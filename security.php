<?php

if (!defined('APP_SESSION_TIMEOUT')) {
    define('APP_SESSION_TIMEOUT', 1800);
}

if (!defined('APP_LOGIN_MAX_ATTEMPTS')) {
    define('APP_LOGIN_MAX_ATTEMPTS', 5);
}

if (!defined('APP_LOGIN_LOCKOUT_SECONDS')) {
    define('APP_LOGIN_LOCKOUT_SECONDS', 900);
}

function apply_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; connect-src 'self'; form-action 'self'; frame-ancestors 'self'; base-uri 'self'");
}

function secure_session_start(): void
{
    apply_security_headers();

    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('lab_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function renew_csrf_token(): string
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        secure_session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        renew_csrf_token();
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_request(): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (!$token || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        echo 'Invalid security token.';
        exit;
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['login_id']);
}

function is_admin(): bool
{
    return isset($_SESSION['login_type']) && (int) $_SESSION['login_type'] === 1;
}

function enforce_session_security(bool $allowGuest = false): void
{
    if (!is_logged_in()) {
        if (!$allowGuest) {
            require_login();
        }
        return;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $currentFingerprint = hash('sha256', $userAgent);

    if (empty($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = $currentFingerprint;
        $_SESSION['last_regenerated'] = time();
    } elseif (!hash_equals($_SESSION['fingerprint'], $currentFingerprint)) {
        force_logout('login.php');
    }

    $lastActivity = $_SESSION['last_activity'] ?? time();
    if ((time() - $lastActivity) > APP_SESSION_TIMEOUT) {
        force_logout('login.php');
    }

    $_SESSION['last_activity'] = time();

    if (empty($_SESSION['last_regenerated']) || (time() - $_SESSION['last_regenerated']) > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
    }
}

function require_login(bool $json = false): void
{
    if (is_logged_in()) {
        return;
    }

    if ($json) {
        http_response_code(401);
        echo 'Unauthorized';
    } else {
        header('Location: login.php');
    }
    exit;
}

function require_admin(bool $json = false): void
{
    require_login($json);

    if (is_admin()) {
        return;
    }

    if ($json) {
        http_response_code(403);
        echo 'Forbidden';
    } else {
        header('Location: index.php?page=home');
    }
    exit;
}

function force_logout(string $redirect = 'login.php'): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    header("Location: {$redirect}");
    exit;
}

function get_client_ip(): string
{
    $candidate = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return preg_replace('/[^a-fA-F0-9\.\:\,]/', '', $candidate);
}

function login_attempt_storage_dir(): string
{
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'security';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $dir;
}

function login_attempt_file(string $username): string
{
    $key = hash('sha256', strtolower(trim($username)) . '|' . get_client_ip());
    return login_attempt_storage_dir() . DIRECTORY_SEPARATOR . $key . '.json';
}

function get_login_attempt_state(string $username): array
{
    $file = login_attempt_file($username);
    if (!is_file($file)) {
        return ['count' => 0, 'locked_until' => 0];
    }

    $json = file_get_contents($file);
    $data = json_decode($json ?: '', true);
    if (!is_array($data)) {
        return ['count' => 0, 'locked_until' => 0];
    }

    return [
        'count' => (int) ($data['count'] ?? 0),
        'locked_until' => (int) ($data['locked_until'] ?? 0),
    ];
}

function save_login_attempt_state(string $username, array $state): void
{
    file_put_contents(login_attempt_file($username), json_encode($state, JSON_PRETTY_PRINT));
}

function is_login_locked_out(string $username): array
{
    $state = get_login_attempt_state($username);
    $remaining = max(0, $state['locked_until'] - time());

    return [
        'locked' => $remaining > 0,
        'remaining' => $remaining,
    ];
}

function record_login_failure(string $username): array
{
    $state = get_login_attempt_state($username);

    if ($state['locked_until'] > 0 && $state['locked_until'] <= time()) {
        $state = ['count' => 0, 'locked_until' => 0];
    }

    $state['count']++;
    if ($state['count'] >= APP_LOGIN_MAX_ATTEMPTS) {
        $state['locked_until'] = time() + APP_LOGIN_LOCKOUT_SECONDS;
        $state['count'] = 0;
    }

    save_login_attempt_state($username, $state);

    return is_login_locked_out($username);
}

function clear_login_failures(string $username): void
{
    $file = login_attempt_file($username);
    if (is_file($file)) {
        unlink($file);
    }
}

function password_matches(string $plainPassword, string $storedPassword): bool
{
    $info = password_get_info($storedPassword);
    if (!empty($info['algo'])) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, $plainPassword) || hash_equals($storedPassword, md5($plainPassword));
}

function password_needs_upgrade(string $storedPassword): bool
{
    $info = password_get_info($storedPassword);
    if (empty($info['algo'])) {
        return true;
    }

    return password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
}

function escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
