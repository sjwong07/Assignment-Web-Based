<?php
include '../lib/_base.php';

$from = req('from'); 
$id = req('id');

$user_id = $_SESSION['user_id'] ?? ($_user->user_id ?? null);

$stm = $_db->prepare('
    SELECT * FROM `order`
    WHERE id = ?
');
$stm->execute([$id]);
$o = $stm->fetch();

if (!$o) redirect('history.php');

$stm = $_db->prepare('
    SELECT i.*, p.Product_model as name, p.Product_id as product_id, p.Product_photo as photo,
    (i.price * i.unit) as subtotal
    FROM item AS i
    JOIN Product AS p ON i.Product_id = p.Product_id
    WHERE i.order_id = ?
');
$stm->execute([$id]);
$arr = $stm->fetchAll();

$total_count = 0;
$total_amount = 0;
foreach ($arr as $i) {
    $total_count += $i->unit;
    $total_amount += ($i->price * $i->unit);
}


include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail | Order #<?= $id ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="detail-container">
    <!-- Header -->
    <div class="detail-header">
        <h2>
            <i class="fas fa-file-invoice"></i>
            Order Details
        </h2>
        <div class="items-count">
            <i class="fas fa-box"></i>
            <?= count($arr) ?> item(s)
        </div>
    </div>

    <?php
        // Determine status badge class
        $statusClass = 'status-pending';
        $statusText = 'Pending';
        $statusIcon = 'fa-clock';
        if (isset($o->status)) {
            switch(strtolower($o->status)) {
                case 'completed':
                case 'delivered':
                    $statusClass = 'status-completed';
                    $statusText = 'Completed';
                    $statusIcon = 'fa-check-circle';
                    break;
                case 'shipped':
                    $statusClass = 'status-shipped';
                    $statusText = 'Shipped';
                    $statusIcon = 'fa-truck';
                    break;
                case 'cancelled':
                case 'canceled':
                    $statusClass = 'status-cancelled';
                    $statusText = 'Cancelled';
                    $statusIcon = 'fa-times-circle';
                    break;
                default:
                    $statusClass = 'status-pending';
                    $statusText = 'Pending';
                    $statusIcon = 'fa-clock';
            }
        }
    ?>

    <!-- Order Info Card -->
    <div class="order-info-card">
        <div class="order-info-group">
            <div class="order-info-item">
                <span class="order-info-label">Order ID</span>
                <span class="order-info-value">
                    <i class="fas fa-hashtag"></i>
                    #<?= $o->id ?>
                </span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Order Date</span>
                <span class="order-info-value">
                    <i class="far fa-calendar-alt"></i>
                    <?= date('d M Y, h:i A', strtotime($o->order_date)) ?>
                </span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Status</span>
                <span class="status-badge <?= $statusClass ?>">
                    <i class="fas <?= $statusIcon ?>"></i>
                    <?= $statusText ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Cart Table -->
    <div class="cart-table-wrapper">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product</th>
                    <th>Price (RM)</th>
                    <th>Quantity</th>
                    <th>Subtotal (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($arr as $i): ?>
                    <tr>
                        <td>
                            <span class="order-id">#<?= $i->product_id ?></span>
                        </td>
                        <td>
                            <div class="product-cell">
                                <?php if (!empty($i->photo) && file_exists('../images/' . $i->photo)): ?>
                                    <img src="../images/<?= htmlspecialchars($i->photo) ?>" alt="<?= htmlspecialchars($i->name) ?>" class="product-image">
                                <?php else: ?>
                                    <img src="../images/no-image.png" alt="No Image" class="product-image">
                                <?php endif; ?>
                                <span class="product-name"><?= htmlspecialchars($i->name) ?></span>
                            </div>
                        </td>
                        <td class="price">RM <?= number_format($i->price, 2) ?></td>
                        <td>
                            <span class="unit-badge">x <?= $i->unit ?></span>
                        </td>
                        <td class="subtotal">RM <?= number_format($i->subtotal, 2) ?></td>
                    </tr>
                <?php endforeach ?>
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
            <span class="summary-label">Subtotal</span>
            <span class="summary-value">RM <?= number_format($total_amount, 2) ?></span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Shipping</span>
            <span class="summary-value" style="color: #10b981;">Free</span>
        </div>
        <div class="summary-row total">
            <span class="summary-label">Total</span>
            <span class="summary-value total">RM <?= number_format($total_amount, 2) ?></span>
        </div>
    </div>

    <!-- Back Button -->
    <div class="back-section">
        <?php
            $back_url = 'history.php';
            if ($from == 'admin') $back_url = '../users/admins/orderlisting.php';
        ?>
        <button type="button" class="btn-back" onclick="location='<?= $back_url ?>'">
            <i class="fas fa-arrow-left"></i>
            Back to Orders
        </button>
    </div>
</div>

<?php
include '../lib/_foot.php';
?>