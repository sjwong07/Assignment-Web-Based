<?php
include '../lib/_base.php';

requireMember();

$user_id = $_SESSION['user_id'];

$stm = $_db->prepare('
    SELECT * FROM `order`
    WHERE user_id = ?
    ORDER BY id DESC
');
$stm->execute([$user_id]);
$arr = $stm->fetchAll(PDO::FETCH_OBJ);


include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="history-container">
    <!-- Header -->
    <div class="history-header">
        <h2>
            <i class="fas fa-history"></i>
            Order History
        </h2>
        <div class="stats-card">
            <i class="fas fa-receipt"></i>
            <div class="stats-info">
                <span class="stats-number"><?= count($arr) ?></span>
                <span class="stats-label">Total Orders</span>
            </div>
        </div>
    </div>

    <?php if (count($arr) > 0): ?>
        <!-- Orders Table -->
        <div class="orders-table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date & Time</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arr as $o): ?>
                        <?php
                            // Determine status badge class
                            $statusClass = 'status-pending';
                            $statusText = 'Pending';
                            if (isset($o->status)) {
                                switch(strtolower($o->status)) {
                                    case 'completed':
                                    case 'delivered':
                                        $statusClass = 'status-completed';
                                        $statusText = 'Completed';
                                        break;
                                    case 'shipped':
                                        $statusClass = 'status-shipped';
                                        $statusText = 'Shipped';
                                        break;
                                    case 'cancelled':
                                    case 'canceled':
                                        $statusClass = 'status-cancelled';
                                        $statusText = 'Cancelled';
                                        break;
                                    default:
                                        $statusClass = 'status-pending';
                                        $statusText = 'Pending';
                                }
                            }
                        ?>
                        <tr class="order-row">
                            <td>
                                <div class="order-id">
                                    <i class="fas fa-hashtag"></i>
                                    <?= $o->id ?>
                                </div>
                            </td>
                            <td>
                                <div class="order-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d M Y, h:i A', strtotime($o->order_date)) ?>
                                </div>
                            </td>
                            <td>
                                <div class="order-total">
                                    RM <?= number_format($o->total, 2) ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <i class="fas <?= $statusClass == 'status-completed' ? 'fa-check-circle' : ($statusClass == 'status-shipped' ? 'fa-truck' : ($statusClass == 'status-cancelled' ? 'fa-times-circle' : 'fa-clock')) ?>"></i>
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" onclick="event.stopPropagation(); location='detail.php?id=<?= $o->id ?>&from=history'">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h3>No orders yet</h3>
            <p>You haven't placed any orders. Start shopping to see your order history here!</p>
            <a href="/product/" class="btn-shop">
                <i class="fas fa-store"></i>
                Start Shopping
            </a>
        </div>
    <?php endif ?>
</div>

<script>
    // Add row click handler with proper event propagation
    document.querySelectorAll('.orders-table tbody tr').forEach(row => {
        const viewBtn = row.querySelector('.btn-view');
        if (viewBtn) {
            viewBtn.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    });
</script>

<?php
include '../lib/_foot.php';
?>