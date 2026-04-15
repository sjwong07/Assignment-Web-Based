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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .checkout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkout-header h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checkout-header h2 i {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.8rem;
        }

        .step-indicator {
            display: flex;
            gap: 1rem;
            background: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .step.active {
            color: #2a5298;
            font-weight: 600;
        }

        .step.completed {
            color: #10b981;
        }

        .step i {
            font-size: 1rem;
        }

        .step-separator {
            color: #cbd5e1;
        }

        /* Review Message */
        .review-message {
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            padding: 1rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #0369a1;
            font-weight: 500;
        }

        .review-message i {
            font-size: 1.25rem;
        }

        /* Cart Table */
        .cart-table-wrapper {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
            margin-bottom: 1.5rem;
        }

        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .cart-table th {
            background: #f8fafc;
            padding: 1.25rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .cart-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-table tr:hover {
            background: #fafcff;
        }

        /* Product Cell */
        .product-cell {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-image {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            background: #f1f5f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .product-name {
            font-weight: 600;
            color: #1e293b;
        }

        /* Price Styling */
        .price {
            font-weight: 600;
            color: #1e293b;
        }

        .subtotal {
            font-weight: 700;
            color: #2a5298;
            font-size: 1.1rem;
        }

        .unit-badge {
            display: inline-block;
            background: #f1f5f9;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
            min-width: 50px;
        }

        /* Summary Section */
        .order-summary {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .summary-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
        }

        .summary-row.total {
            border-top: 2px solid #e2e8f0;
            margin-top: 0.5rem;
            padding-top: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .summary-label {
            color: #64748b;
        }

        .summary-value {
            font-weight: 600;
            color: #1e293b;
        }

        .summary-value.total {
            color: #2a5298;
            font-size: 1.5rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.5rem;
        }

        .btn-back {
            background: white;
            border: 2px solid #cbd5e1;
            color: #475569;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            transform: translateY(-2px);
        }

        .btn-confirm {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16,185,129,0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .checkout-container {
                padding: 1rem;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .product-image {
                width: 50px;
                height: 50px;
            }
            
            .product-name {
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-back, .btn-confirm {
                justify-content: center;
            }
            
            .step-indicator {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .cart-table-wrapper {
                overflow-x: auto;
            }
            
            .cart-table {
                min-width: 550px;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cart-table-wrapper, .order-summary, .action-buttons {
            animation: fadeInUp 0.4s ease forwards;
        }

        .order-summary {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .action-buttons {
            animation-delay: 0.2s;
            opacity: 0;
        }
    </style>
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