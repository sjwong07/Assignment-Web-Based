<?php
include '../lib/_base.php';

//check login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    temp('info', 'Please login to checkout');
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id) {
    temp('info', 'User data error. Please login again.');
    header("Location: /login.php");
    exit;
}

if (is_post()) {
    //Get shopping cart
    $cart = get_cart();
    if(!$cart) redirect('cart.php');

    try{
        $_db->beginTransaction();

        // insert order
        $stm = $_db->prepare('
            INSERT INTO `order` (user_id, order_date, total, status)
            VALUES (?, NOW(), 0, "Pending")
        ');
        $stm->execute([$user_id]);
        $order_id = $_db->lastInsertId();

        $stm = $_db->prepare('
            INSERT INTO item (order_id, product_id, unit, price)
            VALUES(?, ?, ?, (SELECT Product_price FROM Product WHERE Product_id = ?))
        ');
        foreach($cart as $product_id => $unit){
            $stm->execute([$order_id, $product_id, $unit, $product_id]);
        }

        $stm = $_db->prepare('
            UPDATE `order`
            SET total = (SELECT SUM(price * unit) FROM item WHERE order_id = ?)
            WHERE id = ?
        ');
        $stm->execute([$order_id, $order_id]);

        $_db->commit();

        // clear cart
        set_cart([]);

        temp('info', 'Order placed successfully!');
        redirect("detail.php?id=$order_id");
    }catch (Exception $e){
        $_db->rollBack();
        $_err['order'] = 'Failed to create order: ' . $e->getMessage();
    }
}

redirect('cart.php');