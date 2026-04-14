<?php
include '../lib/_base.php';

//check login
if (!isset($_SESSION['user_id'])){
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

<style>
    .table th, .table td{
        border: 2px solid #333;
    }
    th, td{
        padding: 12px;
    }
</style>

<table class="table">
        <thead>
            <tr>
                <th>Id</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Price (RM)</th>
                <th>Unit</th>
                <th>Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $count = 0;
                $total = 0;
                
                $stm = $_db->prepare('SELECT * FROM Product WHERE Product_id = ?');

                foreach($cart as $id => $unit):
                    $stm->execute([$id]);
                    $p = $stm->fetch();
                    
                    if (!$p) continue; 

                    $subtotal = $p->Product_price * $unit;
                    $count += $unit;
                    $total += $subtotal;
            ?>
                <tr>
                    <td><?= $p->Product_id ?></td>

                    <td style= "text-align: center;">
                        <?php if (!empty($p->Product_photo)): ?>
                            <img src="../images/<?=  $p->Product_photo ?>" alt="<?= $p->Product_model ?>" style="max-width: 80px; max-height: 80px;">
                        <?php else: ?>
                            <img src="../images/no-image.png" alt="No Image" style="max-width: 80px; max-height: 80px;">
                        <?php endif; ?>
                    </td>

                    <td><?= $p->Product_model ?></td>
                    <td class="right"><?= number_format($p->Product_price, 2) ?></td>
                    <td class="center"><?= $unit ?></td>
                    <td class="right">
                        <?= number_format($subtotal, 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th class="center"><?= $count ?></th>
                <th class="right">RM <?= number_format($total, 2) ?></th>
            </tr>
        </tfoot>
    </table>

<div style="margin-top: 30px; display: flex; gap: 15px; align-items: center;">
    
    <button type="button" 
            onclick="location='/order/cart.php'" 
            style="background: #95c5f8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
        Back to Cart
    </button>

    <form method="post" style="margin: 0;">
        <button type="submit" 
                style="background: #95c5f8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">
            Confirm Order
        </button>
    </form>

</div>

<?php include '../lib/_foot.php'; ?>