<?php
include '../lib/_base.php';

//check login
requireMember();

$user_id = $_SESSION['user_id'] ?? null;
$cart = get_cart();

if(!$cart) {
    $_SESSION['flash_message'] = 'Your cart is empty.';
    $_SESSION['flash_type'] = 'error';
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

        $stm_stock = $_db->prepare('
        UPDATE Product
        SET Stock = Stock - ?
        WHERE Product_id = ? AND Stock >= ?
        ');

        foreach($cart as $product_id => $unit){
            $stm->execute([$order_id, $product_id, $unit, $product_id]);

            $stm_stock->execute([$unit, $product_id, $unit]);

            if ($stm_stock->rowCount() == 0) {
                throw new Exception("$product_id stock not enough");
            }
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

        $_SESSION['flash_message'] = 'Order placed successfully!';
        $_SESSION['flash_type'] = 'success';
        redirect("/order/history.php");
    }catch (Exception $e){
        $_db->rollBack();
        $_SESSION['flash_message'] = 'Checkout fail';
        $_SESSION['flash_type'] = 'error';
    }
}


include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Confirm Your Order</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="checkout-container">
    <!-- Checkout Header -->
    <div class="checkout-header">
        <h2>
            <i class="fas fa-clipboard-list"></i>
            Checkout
        </h2>
        <div class="step-indicator">
            <div class="step completed">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
            </div>
            <span class="step-separator"><i class="fas fa-chevron-right"></i></span>
            <div class="step active">
                <i class="fas fa-check-circle"></i>
                <span>Review</span>
            </div>
            <span class="step-separator"><i class="fas fa-chevron-right"></i></span>
            <div class="step">
                <i class="fas fa-credit-card"></i>
                <span>Payment</span>
            </div>
        </div>
    </div>

    <!-- Review Message -->
    <div class="review-message">
        <i class="fas fa-eye"></i>
        <span>Please review your items carefully before confirming your order.</span>
    </div>

    <?php
        $count = 0;
        $total = 0;
        $cart_items = [];
        
        $stm = $_db->prepare('SELECT * FROM Product WHERE Product_id = ?');

        foreach($cart as $id => $unit):
            $stm->execute([$id]);
            $p = $stm->fetch();
            
            if (!$p) continue; 

            $subtotal = $p->Product_price * $unit;
            $count += $unit;
            $total += $subtotal;
            $cart_items[] = ['product' => $p, 'unit' => $unit, 'subtotal' => $subtotal];
        endforeach;
    ?>

    <!-- Cart Table -->
    <div class="cart-table-wrapper">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price (RM)</th>
                    <th>Quantity</th>
                    <th>Subtotal (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($cart_items as $item): 
                    $p = $item['product'];
                    $unit = $item['unit'];
                    $subtotal = $item['subtotal'];
                ?>
                    <tr>
                        <td>
                            <div class="product-cell">
                                <?php if (!empty($p->Product_photo) && file_exists('../images/' . $p->Product_photo)): ?>
                                    <img src="../images/<?= htmlspecialchars($p->Product_photo) ?>" alt="<?= htmlspecialchars($p->Product_model) ?>" class="product-image">
                                <?php else: ?>
                                    <img src="../images/no-image.png" alt="No Image" class="product-image">
                                <?php endif; ?>
                                <span class="product-name"><?= htmlspecialchars($p->Product_model) ?></span>
                            </div>
                        </td>
                        <td class="price">RM <?= number_format($p->Product_price, 2) ?></td>
                        <td>
                            <span class="unit-badge"><?= $unit ?></span>
                        </td>
                        <td class="subtotal">RM <?= number_format($subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <div class="summary-title">
            <i class="fas fa-receipt"></i>
            Order Summary
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Items:</span>
            <span class="summary-value"><?= $count ?> item(s)</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Subtotal:</span>
            <span class="summary-value">RM <?= number_format($total, 2) ?></span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Shipping:</span>
            <span class="summary-value" style="color: #10b981;">Free</span>
        </div>
        <div class="summary-row total">
            <span class="summary-label">Grand Total:</span>
            <span class="summary-value total">RM <?= number_format($total, 2) ?></span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button type="button" class="btn-back" onclick="location='/order/cart.php'">
            <i class="fas fa-arrow-left"></i>
            Back to Cart
        </button>

        <form method="post" style="margin: 0;">
            <button type="submit" class="btn-confirm" onclick="return confirm('Please confirm your order. Once confirmed, you cannot modify your order.')">
                <i class="fas fa-check-circle"></i>
                Confirm Order
            </button>
        </form>
    </div>
</div>

<?php include '../lib/_foot.php'; ?>