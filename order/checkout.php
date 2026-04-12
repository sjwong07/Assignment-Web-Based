<?php
include '../lib/_base.php';

//check login
if (!isset($_SESSION['user_id'])){
    temp('info', 'Please login to checkout');
    redirect('/login.php');
}

$user_id = $_SESSION['user_id'] ?? null;
$cart = get_cart();

if(!$cart) {
    temp('info', 'Your cart is empty.');
    redirect('/order/cart.php');
}

if (is_post()) {
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
        set_cart();

        temp('info', 'Order placed successfully!');
        redirect("/order/history.php");
    }catch (Exception $e){
        $_db->rollBack();
        temp('info', 'Checkout failed.');
    }
}

$_title = 'Checkout Confirmation';
include '../lib/_head.php';
?>

<p>Please review your item before checkout.</p>

<form method="post">
    <button type="submit" style="background: #95c5f8; color: white;">Confirm Order</button>
    <a href="/order/cart.php">Back to Cart</a>
</form>

<?php include '../lib/_foot.php'; ?>