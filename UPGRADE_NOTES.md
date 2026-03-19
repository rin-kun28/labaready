# Upgrade Notes - Latest XAMPP Compatibility

## Overview
This application has been updated to work with the latest XAMPP stack (PHP 8.x, MySQL 8.x, Apache 2.4+).

## Major Changes

### 1. Database Connection (`db_connect.php`)
- ✅ Added `mysqli_report()` for better error handling
- ✅ Implemented try-catch for connection errors
- ✅ Set `utf8mb4` charset for full Unicode support
- ✅ Added timezone configuration
- ✅ Secure error logging (doesn't expose sensitive info)

### 2. Security Improvements

#### Removed `extract()` Function
The `extract()` function has been removed from all files as it's a security risk in PHP 8.x:
- **Before**: `extract($_POST);` - Creates variables from POST data
- **After**: `$name = $_POST['name'] ?? '';` - Explicit variable assignment

#### Added Prepared Statements
All database queries now use prepared statements to prevent SQL injection:
- **Before**: `$this->db->query("SELECT * FROM users WHERE username = '$username'")`
- **After**: 
  ```php
  $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param('s', $username);
  $stmt->execute();
  ```

### 3. PHP 8.x Compatibility

#### Null Coalescing Operator (`??`)
Used throughout for safer variable handling:
```php
$name = $_POST['name'] ?? '';  // Returns '' if not set
```

#### Strict Type Checking
- All variables are properly typed before database operations
- Using `intval()`, `floatval()`, etc. for type casting

### 4. Updated Dependencies (`composer.json`)
- ✅ PHP requirement: `^8.0`
- ✅ Symfony HTTP Client: `^6.0 || ^7.0` (updated from 5.4)
- ✅ Added autoloader configuration
- ✅ Platform configuration for PHP 8.0

### 5. Apache Configuration (`.htaccess`)
- ✅ Security headers (X-Frame-Options, XSS Protection, etc.)
- ✅ PHP settings optimized for PHP 8.x
- ✅ Compression and caching enabled
- ✅ Protection for sensitive files

## Installation Steps

### 1. Update Composer Dependencies
```bash
cd c:\xampp\htdocs\lab
composer update
```

### 2. Configure Database
Ensure your MySQL 8.x database is created:
```sql
CREATE DATABASE IF NOT EXISTS laundry_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Update php.ini (if needed)
In `c:\xampp\php\php.ini`, ensure these extensions are enabled:
```ini
extension=mysqli
extension=mbstring
extension=openssl
extension=fileinfo
```

### 4. Restart XAMPP
- Stop Apache and MySQL
- Start Apache and MySQL
- Check that PHP 8.x is active: `http://localhost/lab/` and create a `phpinfo.php` file

## Breaking Changes from Older PHP Versions

### PHP 7.x → PHP 8.x
1. **Named Arguments**: Functions now support named parameters
2. **Union Types**: Better type declarations
3. **Nullsafe Operator**: `$obj?->method()` instead of checking null
4. **Match Expression**: More powerful than switch statements

### MySQL 5.x → MySQL 8.x
1. **Authentication**: Uses `caching_sha2_password` by default
2. **Reserved Keywords**: Some new reserved words (check queries)
3. **Character Set**: Default is `utf8mb4`

## Testing Checklist

- [ ] Login functionality works
- [ ] User registration works
- [ ] Laundry management CRUD operations
- [ ] Inventory management
- [ ] Supply management
- [ ] File uploads work correctly
- [ ] No PHP errors in error log
- [ ] Database transactions complete successfully

## Performance Improvements

1. **Prepared Statements**: Faster repeated queries
2. **utf8mb4**: Better character handling
3. **Optimized Autoloader**: Faster class loading
4. **Apache Compression**: Smaller file transfers
5. **Browser Caching**: Reduced server load

## Security Enhancements

1. **SQL Injection Prevention**: All queries use prepared statements
2. **XSS Protection**: Headers and input sanitization
3. **CSRF Protection**: Consider adding CSRF tokens (future enhancement)
4. **Secure Sessions**: Better session handling
5. **Error Logging**: Errors logged, not displayed to users

## Known Issues & Warnings

### Minor Lint Warnings
- `logout()` and `logout2()` functions don't return values but are checked in `ajax.php`
- This is harmless and won't affect functionality
- Functions use `header()` redirect instead of return values

### Recommendations for Production

1. **Disable Error Display**:
   ```php
   ini_set('display_errors', 0);
   error_reporting(E_ALL);
   ```

2. **Use Environment Variables**:
   ```php
   define('DB_HOST', getenv('DB_HOST'));
   define('DB_USER', getenv('DB_USER'));
   ```

3. **Enable HTTPS**: Update `.htaccess` to force HTTPS

4. **Add CSRF Protection**: Implement CSRF tokens for forms

5. **Password Hashing**: Replace `md5()` with `password_hash()`:
   ```php
   // Instead of: md5($password)
   // Use: password_hash($password, PASSWORD_BCRYPT)
   ```

## Support

For issues related to:
- **XAMPP**: Check `c:\xampp\apache\logs\error.log`
- **PHP**: Check `c:\xampp\php\logs\php_error_log`
- **MySQL**: Check `c:\xampp\mysql\data\*.err`

## Version Information

- **XAMPP**: 8.2.x or higher
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Apache**: 2.4+

## Next Steps (Future Enhancements)

1. Implement `password_hash()` instead of `md5()`
2. Add CSRF token protection
3. Implement input validation classes
4. Add API rate limiting
5. Create automated tests
6. Add database migrations system
7. Implement proper logging system (Monolog)
