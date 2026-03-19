<?php
require_once __DIR__ . '/security.php';
secure_session_start();
ini_set('display_errors', 1);

/**
 * Action Class - Updated for PHP 8.x compatibility
 * Uses prepared statements and removes extract() for security
 */
class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		$lockout = is_login_locked_out($username);
		if ($lockout['locked']) {
			http_response_code(429);
			return 'Too many login attempts. Try again in ' . ceil($lockout['remaining'] / 60) . ' minute(s).';
		}

		$stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			$user = $result->fetch_assoc();
			if (!password_matches($password, $user['password'])) {
				$stmt->close();
				$lockout = record_login_failure($username);
				if ($lockout['locked']) {
					http_response_code(429);
					return 'Too many login attempts. Try again in ' . ceil($lockout['remaining'] / 60) . ' minute(s).';
				}
				return 3;
			}

			clear_login_failures($username);
			session_regenerate_id(true);
			$_SESSION['fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
			$_SESSION['last_activity'] = time();
			$_SESSION['last_regenerated'] = time();
			renew_csrf_token();
			foreach ($user as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}

			if (password_needs_upgrade($user['password'])) {
				$newHash = password_hash($password, PASSWORD_DEFAULT);
				$updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
				$updateStmt->bind_param('si', $newHash, $user['id']);
				$updateStmt->execute();
				$updateStmt->close();
			}
			$stmt->close();
			return 1;
		}else{
			$stmt->close();
			record_login_failure($username);
			return 3;
		}
	}
	function login2(){
		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';
		
		$stmt = $this->db->prepare("SELECT * FROM user_info WHERE email = ? LIMIT 1");
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			$user = $result->fetch_assoc();
			if (!password_matches($password, $user['password'])) {
				$stmt->close();
				return 3;
			}
			session_regenerate_id(true);
			$_SESSION['fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
			$_SESSION['last_activity'] = time();
			$_SESSION['last_regenerated'] = time();
			renew_csrf_token();
			foreach ($user as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			$stmt->close();

			if (password_needs_upgrade($user['password'])) {
				$newHash = password_hash($password, PASSWORD_DEFAULT);
				$updateStmt = $this->db->prepare("UPDATE user_info SET password = ? WHERE id = ?");
				$updateStmt->bind_param('si', $newHash, $user['id']);
				$updateStmt->execute();
				$updateStmt->close();
			}
			
			$ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
			$stmt2 = $this->db->prepare("UPDATE cart SET user_id = ? WHERE client_ip = ?");
			$stmt2->bind_param('is', $_SESSION['login_user_id'], $ip);
			$stmt2->execute();
			$stmt2->close();
			
			return 1;
		}else{
			$stmt->close();
			return 3;
		}
	}
	function logout(){
		force_logout('login.php');
	}
	
	function logout2(){
		force_logout('../index.php');
	}

	function save_user(){
		require_admin(true);
		$name = $_POST['name'] ?? '';
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';
		$type = $_POST['type'] ?? '';
		$id = $_POST['id'] ?? '';
		
		if(empty($id)){
			if ($password === '') {
				http_response_code(422);
				return 'Password is required.';
			}
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			$stmt = $this->db->prepare("INSERT INTO users (name, username, password, type) VALUES (?, ?, ?, ?)");
			$stmt->bind_param('ssss', $name, $username, $passwordHash, $type);
		}else{
			if ($password === '') {
				$stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, type = ? WHERE id = ?");
				$stmt->bind_param('sssi', $name, $username, $type, $id);
			} else {
				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
				$stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, password = ?, type = ? WHERE id = ?");
				$stmt->bind_param('ssssi', $name, $username, $passwordHash, $type, $id);
			}
		}
		
		$save = $stmt->execute();
		$stmt->close();
		
		if($save){
			return 1;
		}
	}
	
	function delete_user(){
		require_admin(true);
		$id = $_POST['id'] ?? 0;
		
		$stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
		$stmt->bind_param('i', $id);
		$delete = $stmt->execute();
		$stmt->close();
		
		if($delete)
			return 1;
	}
	function signup(){
		$first_name = $_POST['first_name'] ?? '';
		$last_name = $_POST['last_name'] ?? '';
		$mobile = $_POST['mobile'] ?? '';
		$address = $_POST['address'] ?? '';
		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		
		// Check if email exists
		$stmt = $this->db->prepare("SELECT id FROM user_info WHERE email = ?");
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			$stmt->close();
			return 2;
		}
		$stmt->close();
		
		// Insert new user
		$stmt = $this->db->prepare("INSERT INTO user_info (first_name, last_name, mobile, address, email, password) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param('ssssss', $first_name, $last_name, $mobile, $address, $email, $hashed_password);
		$save = $stmt->execute();
		$stmt->close();
		
		if($save){
			$login = $this->login2();
			return 1;
		}
	}

	function save_settings(){
		$name = $_POST['name'] ?? '';
		$email = $_POST['email'] ?? '';
		$contact = $_POST['contact'] ?? '';
		$about = $_POST['about'] ?? '';
		$about_content = htmlentities(str_replace("'","&#x2019;", $about));
		
		$fname = null;
		if(!empty($_FILES['img']['tmp_name'])){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'../assets/img/'. $fname);
		}
		
		$chk = $this->db->query("SELECT id FROM system_settings LIMIT 1");
		
		if($chk->num_rows > 0){
			$row = $chk->fetch_assoc();
			if($fname){
				$stmt = $this->db->prepare("UPDATE system_settings SET name = ?, email = ?, contact = ?, about_content = ?, cover_img = ? WHERE id = ?");
				$stmt->bind_param('sssssi', $name, $email, $contact, $about_content, $fname, $row['id']);
			}else{
				$stmt = $this->db->prepare("UPDATE system_settings SET name = ?, email = ?, contact = ?, about_content = ? WHERE id = ?");
				$stmt->bind_param('ssssi', $name, $email, $contact, $about_content, $row['id']);
			}
		}else{
			if($fname){
				$stmt = $this->db->prepare("INSERT INTO system_settings (name, email, contact, about_content, cover_img) VALUES (?, ?, ?, ?, ?)");
				$stmt->bind_param('sssss', $name, $email, $contact, $about_content, $fname);
			}else{
				$stmt = $this->db->prepare("INSERT INTO system_settings (name, email, contact, about_content) VALUES (?, ?, ?, ?)");
				$stmt->bind_param('ssss', $name, $email, $contact, $about_content);
			}
		}
		
		$save = $stmt->execute();
		$stmt->close();
		
		if($save){
			$query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_assoc();
			foreach ($query as $key => $value) {
				if(!is_numeric($key))
					$_SESSION['setting_'.$key] = $value;
			}
			return 1;
		}
	}

	
	function save_category(){
		$name = $_POST['name'] ?? '';
		$price = $_POST['price'] ?? 0;
		$id = $_POST['id'] ?? '';
		
		if(empty($id)){
			$stmt = $this->db->prepare("INSERT INTO laundry_categories (name, price) VALUES (?, ?)");
			$stmt->bind_param('sd', $name, $price);
		}else{
			$stmt = $this->db->prepare("UPDATE laundry_categories SET name = ?, price = ? WHERE id = ?");
			$stmt->bind_param('sdi', $name, $price, $id);
		}
		
		$save = $stmt->execute();
		$stmt->close();
		
		if($save)
			return 1;
	}
	function delete_category(){
		$id = $_POST['id'] ?? 0;
		
		$stmt = $this->db->prepare("DELETE FROM laundry_categories WHERE id = ?");
		$stmt->bind_param('i', $id);
		$delete = $stmt->execute();
		$stmt->close();
		
		if($delete)
			return 1;
	}
	function save_supply(){
		$name = $_POST['name'] ?? '';
		$id = $_POST['id'] ?? '';
		
		if(empty($id)){
			$stmt = $this->db->prepare("INSERT INTO supply_list (name) VALUES (?)");
			$stmt->bind_param('s', $name);
		}else{
			$stmt = $this->db->prepare("UPDATE supply_list SET name = ? WHERE id = ?");
			$stmt->bind_param('si', $name, $id);
		}
		
		$save = $stmt->execute();
		$stmt->close();
		
		if($save)
			return 1;
	}
	function delete_supply(){
		$id = $_POST['id'] ?? 0;
		
		$stmt = $this->db->prepare("DELETE FROM supply_list WHERE id = ?");
		$stmt->bind_param('i', $id);
		$delete = $stmt->execute();
		$stmt->close();
		
		if($delete)
			return 1;
	}

	function save_laundry(){
		$customer_name = $_POST['customer_name'] ?? '';
		$customer_number = $_POST['customer_number'] ?? '';
		$remarks = $_POST['remarks'] ?? '';
		$tamount = $_POST['tamount'] ?? 0;
		$tendered = $_POST['tendered'] ?? 0;
		$change = $_POST['change'] ?? 0;
		$pay = $_POST['pay'] ?? null;
		$status = $_POST['status'] ?? null;
		$id = $_POST['id'] ?? '';
		$weight = $_POST['weight'] ?? [];
		$laundry_category_id = $_POST['laundry_category_id'] ?? [];
		$unit_price = $_POST['unit_price'] ?? [];
		$amount = $_POST['amount'] ?? [];
		$item_id = $_POST['item_id'] ?? [];
		$data = " customer_name = '$customer_name' ";
		$data .= ", customer_number = '$customer_number' ";
		$data .= ", remarks = '$remarks' ";
		$data .= ", total_amount = '$tamount' ";
		$data .= ", amount_tendered = '$tendered' ";
		$data .= ", amount_change = '$change' ";
		if(isset($pay)){
			$data .= ", pay_status = '1' ";
		}
		if(isset($status))
			$data .= ", status = '$status' ";
		if(empty($id)){
			$queue = $this->db->query("SELECT `queue` FROM laundry_list where status != 3 order by id desc limit 1");
			$queue =$queue->num_rows > 0 ? $queue->fetch_array()['queue']+1 : 1;
			$data .= ", queue = '$queue' ";
			$save = $this->db->query("INSERT INTO laundry_list set ".$data);
			if($save){
				$id = $this->db->insert_id;
				foreach ($weight as $key => $value) {
					$items = " laundry_id = '$id' ";
					$items .= ", laundry_category_id = '$laundry_category_id[$key]' ";
					$items .= ", weight = '$weight[$key]' ";
					$items .= ", unit_price = '$unit_price[$key]' ";
					$items .= ", amount = '$amount[$key]' ";
					$save2 = $this->db->query("INSERT INTO laundry_items set ".$items);
				}
				return 1;
			}		
		}else{
			$save = $this->db->query("UPDATE laundry_list set ".$data." where id=".$id);
			if($save){
				$this->db->query("DELETE FROM laundry_items where id not in (".implode(',',$item_id).") ");
				foreach ($weight as $key => $value) {
					$items = " laundry_id = '$id' ";
					$items .= ", laundry_category_id = '$laundry_category_id[$key]' ";
					$items .= ", weight = '$weight[$key]' ";
					$items .= ", unit_price = '$unit_price[$key]' ";
					$items .= ", amount = '$amount[$key]' ";
					if(empty($item_id[$key]))
						$save2 = $this->db->query("INSERT INTO laundry_items set ".$items);
					else
						$save2 = $this->db->query("UPDATE laundry_items set ".$items." where id=".$item_id[$key]);
				}
				return 1;
			}	

		}
	}

	function delete_laundry(){
		$id = $_POST['id'] ?? 0;
		
		$stmt = $this->db->prepare("DELETE FROM laundry_list WHERE id = ?");
		$stmt->bind_param('i', $id);
		$delete = $stmt->execute();
		$stmt->close();
		
		$stmt2 = $this->db->prepare("DELETE FROM laundry_items WHERE laundry_id = ?");
		$stmt2->bind_param('i', $id);
		$delete2 = $stmt2->execute();
		$stmt2->close();
		
		if($delete && $delete2)
			return 1;
	}
	function save_inv(){
		$supply_id = $_POST['supply_id'] ?? 0;
		$qty = $_POST['qty'] ?? 0;
		$stock_type = $_POST['stock_type'] ?? 0;
		$id = $_POST['id'] ?? '';
		
		if(empty($id)){
			$stmt = $this->db->prepare("INSERT INTO inventory (supply_id, qty, stock_type) VALUES (?, ?, ?)");
			$stmt->bind_param('iii', $supply_id, $qty, $stock_type);
			$save = $stmt->execute();
			$stmt->close();
			
			if($save){
				// Update supply_list qty accordingly
				if($stock_type == 1){
					$stmt2 = $this->db->prepare("UPDATE supply_list SET qty = qty + ? WHERE id = ?");
					$stmt2->bind_param('ii', $qty, $supply_id);
					$stmt2->execute();
					$stmt2->close();
				} else if($stock_type == 2){
					$stmt2 = $this->db->prepare("UPDATE supply_list SET qty = qty - ? WHERE id = ?");
					$stmt2->bind_param('ii', $qty, $supply_id);
					$stmt2->execute();
					$stmt2->close();
				}
				return 1;
			}
		}else{
			$stmt = $this->db->prepare("UPDATE inventory SET supply_id = ?, qty = ?, stock_type = ? WHERE id = ?");
			$stmt->bind_param('iiii', $supply_id, $qty, $stock_type, $id);
			$save = $stmt->execute();
			$stmt->close();
			return $save ? 1 : 0;
		}
	}

	function delete_inv(){
		$id = $_POST['id'] ?? 0;
		
		$stmt = $this->db->prepare("DELETE FROM inventory WHERE id = ?");
		$stmt->bind_param('i', $id);
		$delete = $stmt->execute();
		$stmt->close();
		
		if($delete)
			return 1;
	}
	

}
