<?php

// PHP Setups
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// General Page Functions

function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

function encode($value) {
    return htmlentities($value);
}

function html_hidden($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

function html_select($key, $items, $default = '- Select One -', $selected = null, $attr = '') {
    $value = $selected !== null ? $selected : encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ============================================================================
// Shopping Cart
// ============================================================================

// Get shopping cart
function get_cart() {
    global $_user, $_db;

    if($_user){
        $stm = $_db->prepare('
            SELECT product_id, unit FROM cart_item
            WHERE customer_id = ?
        ');
        $stm->execute([$_user->Customer_id]);
        $rows = $stm->fetchAll();

        $cart = [];
        foreach ($rows as $row){
            $cart[$row->product_id] = $row->unit;
        }
        return $cart;
    } else{
        return $_SESSION['cart'] ?? [];
    }
}

// Set shopping cart
function set_cart($cart = []) {
    global $_user, $_db;

    if($_user){
        $stm = $_db->prepare('
            DELETE FROM cart_item WHERE customer_id = ?
        ');
        $stm->execute([$_user->Customer_id]);
        
        $stm = $_db->prepare('
            INSERT INTO cart_item (customer_id, product_id, unit)
            VALUES (?, ?, ?)
        ');

        foreach ($cart as $product_id => $unit){
            $stm->execute([$_user->Customer_id, $product_id, $unit]);
        }
    } else {
        $_SESSION['cart'] = $cart;
    }
}

// Update shopping cart
function update_cart($id, $unit) {
    global $_db;

    $stm = $_db->prepare('SELECT COUNT(*) FROM Product WHERE Product_id = ?');
    $stm->execute([$id]);
    $exists = $stm->fetchColumn() > 0;

    if(!$exists){
        return false;
    }
    
    $cart = get_cart();

    if($unit >= 1 && $unit <= 10){
        $cart[$id] = $unit;
    }
    else{
        unset($cart[$id]);
    }

    ksort($cart);
    set_cart($cart);
    return true;
}

function get_file($field_name) {
    // Check if a file was uploaded via form
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file uploaded
    }

    // Return the uploaded file info
    return $_FILES[$field_name];
}


// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
$_db = new PDO('mysql:dbname=dbA', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// ============================================================================
// Global Constants and Variables
// ============================================================================

$_units = array_combine(range(1,10), range(1,10));