<?php
include '../lib/_base.php';

requireMember();

$cart = get_cart();

if (is_post()) {

    $btn = req('btn');
    if ($btn == 'clear'){
        set_cart();
        redirect('?');
    }

    if ($btn == 'clear_selected') {
        $selected_items = req('selected_items'); 
        if (is_array($selected_items)) {
            foreach ($selected_items as $id) {
                update_cart($id, 0); 
            }
        }
        redirect('?');
    }

    $id   = req('id');
    $unit = req('unit');
    update_cart($id, $unit);
    redirect();
}

// ----------------------------------------------------------------------------


include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Your Cart</title>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            min-height: 100vh;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cart-header h2 {
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

        .cart-header h2 i {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.8rem;
        }

        .cart-badge {
            background: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e293b;
        }

        .cart-badge i {
            color: #2a5298;
            margin-right: 0.5rem;
        }

        /* Empty Cart State */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        .empty-cart i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-cart h3 {
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .empty-cart p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .btn-continue {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42,82,152,0.3);
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

        /* Unit Select */
        .unit-select {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .unit-select:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42,82,152,0.1);
        }

        /* Summary Section */
        .cart-summary {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
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
            margin-top: 0.5rem;
        }

        .btn-clear {
            background: white;
            border: 2px solid #ef4444;
            color: #ef4444;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-clear:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }

        .btn-checkout {
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

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16,185,129,0.3);
        }

        .login-prompt {
            background: #fef3c7;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            color: #92400e;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .login-prompt a {
            color: #d97706;
            font-weight: 600;
            text-decoration: none;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .cart-container {
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
            
            .btn-clear, .btn-checkout {
                justify-content: center;
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

        /* Animation for quantity change */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .updated {
            animation: pulse 0.3s ease;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <!-- Cart Header -->
    <div class="cart-header">
        <h2>
            <i class="fas fa-shopping-cart"></i>
            Shopping Cart
        </h2>
        <div class="cart-badge">
            <i class="fas fa-store"></i> Continue Shopping
        </div>
    </div>

    <?php
        $count = 0;
        $total = 0;
        $cart_items = [];

        $stm = $_db->prepare('SELECT * FROM Product WHERE Product_id = ?');
        $cart = get_cart();

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

    <?php if (!empty($cart_items)): ?>
        <!-- Cart Table -->
        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="width: 50px;"></th>
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
                        <tr data-product-id="<?= $p->Product_id ?>">
                            <td style="text-align: center;">
                                <input type="checkbox" name="selected_items[]" value="<?= $p->Product_id ?>" class="item-selector" form="clear-selected-form" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
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
                                <form method="post" class="quantity-form">
                                    <input type="hidden" name="id" value="<?= $p->Product_id ?>">
                                    <select name="unit" class="unit-select">
                                        <?php for($i = 1; $i <= 20; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($unit == $i) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="subtotal">RM <?= number_format($subtotal, 2) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary">
            <div class="summary-row">
                <span class="summary-label">Total Items:</span>
                <span class="summary-value"><?= $count ?> item(s)</span>
            </div>
            <div class="summary-row total">
                <span class="summary-label">Grand Total:</span>
                <span class="summary-value total">RM <?= number_format($total, 2) ?></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <form method="post" id="clear-selected-form" style="margin: 0;">
                <input type="hidden" name="btn" value="clear_selected">
                <button type="submit" id="btn-clear-selected" class="btn-clear" disabled onclick="return confirm('Remove selected items from cart?')">
                    <i class="fas fa-check-square"></i> Clear Selected
                </button>
            </form>
            <form method="post" style="margin: 0;">
                <input type="hidden" name="btn" value="clear">
                <button type="submit" class="btn-clear" onclick="return confirm('Are you sure you want to clear your entire cart?')">
                    <i class="fas fa-trash-alt"></i> Clear Cart
                </button>
            </form>

            <?php if ($_user && $_user->role == 'member'): ?>
                <button type="button" class="btn-checkout" onclick="window.location.href='/order/checkout.php'">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </button>
            <?php else: ?>
                <div class="login-prompt">
                    <i class="fas fa-exclamation-triangle"></i>
                    Please <a href="/login.php">login</a> as a member to checkout
                </div>
            <?php endif ?>
        </div>
    <?php else: ?>
        <!-- Empty Cart State -->
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="/product/" class="btn-continue">
                <i class="fas fa-store"></i> Continue Shopping
            </a>
        </div>
    <?php endif ?>
</div>

<script>
    // Auto-submit form when select changes with animation
    document.querySelectorAll('.unit-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const row = e.target.closest('tr');
            if (row) {
                row.style.animation = 'pulse 0.3s ease';
                setTimeout(() => {
                    if (row) row.style.animation = '';
                }, 300);
            }
            e.target.form.submit();
        });
    });

    // Add continue shopping button click handler
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        cartBadge.style.cursor = 'pointer';
        cartBadge.addEventListener('click', () => {
            window.location.href = '/product/';
        });
    }

    document.querySelectorAll('.unit-select').forEach(select => {
        select.addEventListener('change', (e) => {
            e.target.form.submit();
        });
    });

    //Clear Selected button enable/disable logic
    const checkboxes = document.querySelectorAll('.item-selector');
    const clearSelectedBtn = document.getElementById('btn-clear-selected');

    function toggleClearBtn() {
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        clearSelectedBtn.disabled = checkedCount === 0;
        
        if (clearSelectedBtn.disabled) {
            clearSelectedBtn.style.opacity = "0.5";
            clearSelectedBtn.style.cursor = "not-allowed";
        } else {
            clearSelectedBtn.style.opacity = "1";
            clearSelectedBtn.style.cursor = "pointer";
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleClearBtn);
    });

    toggleClearBtn();
</script>

<?php
include '../lib/_foot.php';
?>