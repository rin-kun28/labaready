<?php
require_once __DIR__ . '/security.php';
secure_session_start();
enforce_session_security(true);
ob_start();
$action = $_GET['action'] ?? '';
if ($action === '') {
	http_response_code(400);
	exit('Missing action.');
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	http_response_code(405);
	exit('Method not allowed.');
}

$publicActions = ['login', 'login2'];
if (!in_array($action, $publicActions, true)) {
	require_login(true);
}

verify_csrf_request();
include 'admin_class.php';
$crud = new Action();

// --- User and Auth Actions ---
if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}

include 'db_connect.php';

// --- Laundry Category Actions ---
if ($_GET['action'] == 'save_category') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $id = $_POST['id'] ?? '';

    $name = $conn->real_escape_string($name);
    $price = floatval($price);

    if (empty($id)) {
        $save = $conn->query("INSERT INTO laundry_categories 
            (name, price) 
            VALUES ('$name', '$price')");
        if ($save) {
            echo 1;
        } else {
            echo $conn->error;
        }
    } else {
        $update = $conn->query("UPDATE laundry_categories SET 
            name = '$name', 
            price = '$price'
            WHERE id = $id");
        if ($update) {
            echo 2;
        } else {
            echo $conn->error;
        }
    }
    exit;
}

if($action == "delete_category"){
	$save = $crud->delete_category();
	if($save)
		echo $save;
}

// --- Supply CRUD ---
if($action == "save_supply"){
	$save = $crud->save_supply();
	if($save)
		echo $save;
}
if($action == "delete_supply"){
	$save = $crud->delete_supply();
	if($save)
		echo $save;
}

// --- EDIT SUPPLY (price and stock) ---
if($action == "edit_supply"){
    $id = intval($_POST['id']);
    $price = floatval($_POST['price']);
    $new_stock = intval($_POST['stock']);
    $stock_diff = intval($_POST['stock_diff']); // new_stock - old_stock

    $update = $conn->query("UPDATE supply_list SET price = $price WHERE id = $id");
    if(!$update){
        echo "Failed to update price: " . $conn->error;
        exit;
    }

    if($stock_diff != 0){
        $update_qty = $conn->query("UPDATE supply_list SET qty = qty + ($stock_diff) WHERE id = $id");
        if(!$update_qty){
            echo "Failed to update stock: " . $conn->error;
            exit;
        }
        if($stock_diff > 0){
            $conn->query("INSERT INTO inventory (supply_id, qty, stock_type) VALUES ($id, $stock_diff, 1)");
        }else{
            $conn->query("INSERT INTO inventory (supply_id, qty, stock_type) VALUES ($id, ".abs($stock_diff).", 2)");
        }
    }
    echo 1;
    exit;
}
// --- END EDIT SUPPLY ---

// --- LAUNDRY SAVE WITH MULTIPLE SUPPLIES_USED SUPPORT ---
if($action == "save_laundry"){
    $response = ['status' => 'error', 'message' => '', 'id' => null];
    try {
        $conn->begin_transaction();

        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_number = $conn->real_escape_string($_POST['customer_number']);
        $remarks = $conn->real_escape_string($_POST['remarks']);
        $total_amount = floatval($_POST['tamount']);
        $pay_status = isset($_POST['pay']) ? 1 : 0;
        $amount_tendered = $pay_status ? floatval($_POST['tendered']) : 0;
        $amount_change = $pay_status ? ($amount_tendered - $total_amount) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
        
        // Payment method details
        $payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : 'Cash';
        $payment_ref = isset($_POST['payment_ref']) ? $conn->real_escape_string($_POST['payment_ref']) : null;

        if(empty($_POST['id'])){
            // Insert new customer in customers table
            $customer_id = null;
            if(!empty($customer_name) && !empty($customer_number)){
                $insert_customer = $conn->query("INSERT INTO customers (customer_name, customer_number) VALUES ('$customer_name', '$customer_number')");
                if($insert_customer){
                    $customer_id = $conn->insert_id;
                }
            }

            // Check if customer_id column exists in laundry_list
            $columns_check = $conn->query("SHOW COLUMNS FROM laundry_list LIKE 'customer_id'");
            $has_customer_id_column = ($columns_check->num_rows > 0);

            if($has_customer_id_column && $customer_id){
                $sql = "INSERT INTO laundry_list (
                    customer_id,
                    customer_name, 
                    customer_number, 
                    remarks, 
                    total_amount, 
                    pay_status, 
                    amount_tendered, 
                    amount_change, 
                    status
                ) VALUES (
                    '$customer_id',
                    '$customer_name', 
                    '$customer_number', 
                    '$remarks', 
                    '$total_amount', 
                    '$pay_status', 
                    '$amount_tendered', 
                    '$amount_change', 
                    '$status'
                )";
            } else {
                $sql = "INSERT INTO laundry_list (
                    customer_name, 
                    customer_number, 
                    remarks, 
                    total_amount, 
                    pay_status, 
                    amount_tendered, 
                    amount_change, 
                    status
                ) VALUES (
                    '$customer_name', 
                    '$customer_number', 
                    '$remarks', 
                    '$total_amount', 
                    '$pay_status', 
                    '$amount_tendered', 
                    '$amount_change', 
                    '$status'
                )";
            }
            $save = $conn->query($sql);
            if(!$save){
                throw new Exception("Error saving laundry: " . $conn->error);
            }
            $laundry_id = $conn->insert_id;
        } else {
            $id = intval($_POST['id']);
            
            // Insert new customer in customers table for edit as well
            $customer_id = null;
            if(!empty($customer_name) && !empty($customer_number)){
                $insert_customer = $conn->query("INSERT INTO customers (customer_name, customer_number) VALUES ('$customer_name', '$customer_number')");
                if($insert_customer){
                    $customer_id = $conn->insert_id;
                }
            }
            
            // Check if customer_id column exists in laundry_list
            $columns_check = $conn->query("SHOW COLUMNS FROM laundry_list LIKE 'customer_id'");
            $has_customer_id_column = ($columns_check->num_rows > 0);
            
            if($has_customer_id_column && $customer_id){
                $sql = "UPDATE laundry_list SET 
                    customer_id = '$customer_id',
                    customer_name = '$customer_name', 
                    customer_number = '$customer_number', 
                    remarks = '$remarks', 
                    total_amount = '$total_amount', 
                    pay_status = '$pay_status', 
                    amount_tendered = '$amount_tendered', 
                    amount_change = '$amount_change', 
                    status = '$status'
                    WHERE id = $id";
            } else {
                $sql = "UPDATE laundry_list SET 
                    customer_name = '$customer_name', 
                    customer_number = '$customer_number', 
                    remarks = '$remarks', 
                    total_amount = '$total_amount', 
                    pay_status = '$pay_status', 
                    amount_tendered = '$amount_tendered', 
                    amount_change = '$amount_change', 
                    status = '$status'
                    WHERE id = $id";
            }
            $save = $conn->query($sql);
            if(!$save){
                throw new Exception("Error updating laundry: " . $conn->error);
            }
            $laundry_id = $id;

            $conn->query("DELETE FROM laundry_items WHERE laundry_id = $laundry_id");
            $conn->query("DELETE FROM supplies_used WHERE laundry_id = $laundry_id");
        }

        if(isset($_POST['laundry_category_id'])){
            for($i = 0; $i < count($_POST['laundry_category_id']); $i++){
                $laundry_category_id = intval($_POST['laundry_category_id'][$i]);
                $weight = floatval($_POST['weight'][$i]);
                $unit_price = floatval($_POST['unit_price'][$i]);
                $amount = floatval($_POST['amount'][$i]);

                $stmt = $conn->prepare("INSERT INTO laundry_items (
                    laundry_id, 
                    laundry_category_id, 
                    weight, 
                    unit_price, 
                    amount
                ) VALUES (?, ?, ?, ?, ?)");
                if(!$stmt){
                    throw new Exception("Error preparing laundry item: " . $conn->error);
                }
                $stmt->bind_param("iiddd", 
                    $laundry_id,
                    $laundry_category_id,
                    $weight,
                    $unit_price,
                    $amount
                );
                if(!$stmt->execute()){
                    throw new Exception("Error saving laundry item: " . $stmt->error);
                }
                $stmt->close();
            }
        }

        if (isset($_POST['supply_used']) && is_array($_POST['supply_used'])) {
            $already_added = [];
            foreach ($_POST['supply_used'] as $k => $supply_id) {
                $qty = isset($_POST['supply_qty'][$k]) ? floatval($_POST['supply_qty'][$k]) : 0;
                $supply_id = intval($supply_id);
                if ($supply_id && $qty > 0 && !isset($already_added[$supply_id])) {
                    $conn->query("INSERT INTO supplies_used (laundry_id, supply_id, qty) VALUES ($laundry_id, $supply_id, $qty)");
                    $conn->query("UPDATE supply_list SET qty = qty - $qty WHERE id = $supply_id");
                    $conn->query("INSERT INTO inventory (supply_id, qty, stock_type) VALUES ($supply_id, $qty, 2)");
                    $already_added[$supply_id] = true;
                }
            }
        }

        // Create payment record if payment was made
        if ($pay_status == 1 && $amount_tendered > 0) {
            // Get current user ID from session
            $user_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : 1; // Default to 1 (Admin) if no session
            
            // First, delete any existing payment records for this laundry (in case of update)
            $conn->query("DELETE FROM payments WHERE laundry_id = $laundry_id");
            
            // Insert new payment record with user_id
            $payment_sql = "INSERT INTO payments (laundry_id, user_id, amount_paid, payment_method";
            $payment_values = "VALUES ($laundry_id, $user_id, $amount_tendered, '$payment_method'";
            
            // Add payment reference if provided (for GCash)
            if ($payment_ref) {
                $payment_sql .= ", payment_ref";
                $payment_values .= ", '$payment_ref'";
            }
            
            $payment_sql .= ") " . $payment_values . ")";
            
            $payment_result = $conn->query($payment_sql);
            if (!$payment_result) {
                throw new Exception("Error saving payment record: " . $conn->error);
            }
        }

        $conn->commit();
        echo 1;

    } catch(Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit();
}
// --- END LAUNDRY SAVE ---

if($action == "delete_laundry"){
	$save = $crud->delete_laundry();
	if($save)
		echo $save;
}

// --- Inventory Save / Delete ---
if($action == "save_inv"){
	$save = $crud->save_inv();
	if($save)
		echo $save;
}
if($action == "delete_inv"){
	$save = $crud->delete_inv();
	if($save)
		echo $save;
}

// --- Update Laundry Status ---
if ($_GET['action'] == 'update_status') {
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);
    $update = $conn->query("UPDATE laundry_list SET status = '$status' WHERE id = $id");
    echo $update ? 1 : 0;
    exit;
}

// --- Add Supply (with price & inventory) ---
if ($_GET['action'] == 'add_supply') {
    $name = trim($_POST['name']);
    $qty = (int)$_POST['qty'];
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    if ($name == '' || $qty <= 0) {
        echo 0;
        exit;
    }

    $insert_supply = $conn->query("INSERT INTO supply_list (name, qty, price) VALUES ('$name', '$qty', $price)");

    if ($insert_supply) {
        $supply_id = $conn->insert_id;

        $insert_inventory = $conn->query("INSERT INTO inventory (supply_id, qty, stock_type) VALUES ('$supply_id', '$qty', 1)");

        if ($insert_inventory) {
            echo 1;
        } else {
            $conn->query("DELETE FROM supply_list WHERE id = '$supply_id'");
            echo 0;
        }
    } else {
        echo 0;
    }
    exit;
}

// --- Delete Supply (and related inventory) ---
if ($_GET['action'] == 'delete_supply') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $supply_id = $_POST['id'];

        $conn->begin_transaction();

        try {
            $delete_inventory = $conn->prepare("DELETE FROM inventory WHERE supply_id = ?");
            $delete_inventory->bind_param("i", $supply_id);
            $delete_inventory->execute();

            $delete_supply = $conn->prepare("DELETE FROM supply_list WHERE id = ?");
            $delete_supply->bind_param("i", $supply_id);
            $delete_supply->execute();

            $conn->commit();

            echo 1;
        } catch (Exception $e) {
            $conn->rollback();
            echo 0;
        }
    } else {
        echo 0;
    }
    exit;
}



// --- Update Supply Stock Only (NO INVENTORY RECORD) ---
if ($_GET['action'] == 'update_stock_only') {
    $supply_id = intval($_POST['supply_id']);
    $qty = intval($_POST['qty']);
    $stock_type = intval($_POST['stock_type']);
    if ($supply_id > 0 && $qty > 0 && in_array($stock_type, [1,2])) {
        if ($stock_type == 1) {
            $sql = "UPDATE supply_list SET qty = qty + $qty WHERE id = $supply_id";
        } else {
            $sql = "UPDATE supply_list SET qty = qty - $qty WHERE id = $supply_id";
        }
        $update = $conn->query($sql);
        echo $update ? 1 : "Failed to update stock";
    } else {
        echo "Invalid data";
    }
    exit();
}
// ... your other code ...
if ($_GET['action'] == "save_expenditure") {
    // Check if user is logged in
    if (!isset($_SESSION['login_id'])) {
        echo 0; // User not logged in
        exit;
    }
    
    $user_id = $_SESSION['login_id'];
    
    // Field validation
    $details = isset($_POST['details']) ? trim($_POST['details']) : '';
    $total = isset($_POST['total']) ? trim($_POST['total']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';

    if ($details === "" || $total === "" || $date === "") {
        echo 0; // Required fields missing
        exit;
    }

    // Proceed with your image upload and DB insert/update logic here
    // Example assuming image upload and distinguishing between add/update
    $id = isset($_POST['id']) && $_POST['id'] ? intval($_POST['id']) : 0;
    $image_path = '';
    if(isset($_FILES['image']) && $_FILES['image']['tmp_name']){
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir,0777,true);
        $file_name = time().'_'.basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $image_path = $target_file;
        }
    }

    $conn = new mysqli("localhost", "root", "", "laundry_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if($id > 0){
        // Update
        $sql = "UPDATE expenditures SET details=?, total=?, date=?, user_id=?" . ($image_path ? ", image_path=?" : "") . " WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($image_path){
            $stmt->bind_param("sdsisi", $details, $total, $date, $user_id, $image_path, $id);
        } else {
            $stmt->bind_param("sdsii", $details, $total, $date, $user_id, $id);
        }
        $stmt->execute();
        echo 2;
    } else {
        // Insert
        $sql = "INSERT INTO expenditures (user_id, details, total, date, image_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdss", $user_id, $details, $total, $date, $image_path);
        $stmt->execute();
        echo 1;
    }
    exit;
}

if ($_GET['action'] == "delete_expenditure") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id > 0) {
        $conn = new mysqli("localhost", "root", "", "laundry_db");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("DELETE FROM expenditures WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo 1;
        } else {
            echo 0;
        }
        $stmt->close();
        $conn->close();
    } else {
        echo 0;
    }
    exit;
}
?>
