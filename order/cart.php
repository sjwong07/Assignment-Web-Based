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
                            <td>
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