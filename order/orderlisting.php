<?php

require 'Admin_Access_Required.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    $order_ids = $_POST['order_id'] ?? [];
    $statuses = $_POST['statuse'] ?? [];
    
    $stm = $_db->prepare('UPDATE `order` SET status = ? WHERE id = ?');
    
    foreach ($order_ids as $index => $id) {
        $stm->execute([$statuses[$index], $id]);
    }
}

$order = " SELECT o.*, u.username 
    FROM `order` o
    JOIN user u ON o.user_id = u.user_id
    ORDER BY o.id DESC";
// Fetch all orders
$stm = $_db->prepare($order);
$stm->execute();
$arr = $stm->fetchAll(PDO::FETCH_OBJ);

include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Order Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/app.css"> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="history-container">
    <div class="history-header">
        <h2><i class="fas fa-tasks"></i> Order Listing </h2>
    </div>

    <?php if (count($arr) > 0): ?>
        <form method="post" id="bulk-update-form">
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>USER</th>
                            <th>Date & Time</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                            <th>Manage Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arr as $o): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $o->id ?></strong>
                                    <input type="hidden" name="order_ids[]" value="<?= $o->id ?>">
                                </td>
                                <td><?= htmlspecialchars($o->username) ?></td>
                                <td class="order-date"><?= date('d M Y, h:i A', strtotime($o->order_date)) ?></td>
                                <td class="order-total">RM <?= number_format($o->total, 2) ?></td>
                                <td>
                                    <button type="button" class="btn-view" onclick="location='detail.php?id=<?= $o->id ?>&from=admin'">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </td>
                                <td>
                                    <input type="hidden" name="statuses[]" class="status-input" value="<?= $o->status ?>">
                                    <span class="status-badge status-<?= strtolower($o->status) ?>">
                                        <?= $o->status ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <div class="submit-container">
                <input type="hidden" name="bulk_update" value="1">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Save All Changes
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No Records Found</h3>
            <p>There are currently no orders in the system to display.</p>
        </div>
    <?php endif ?>
</div>

<script>
//Status and Update
$(document).ready(function() {
    const statuses = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];

    $('.status-badge').on('click', function() {
        const $badge = $(this);
        const $input = $badge.siblings('.status-input');
        
        let currentIndex = statuses.indexOf($input.val());
        let nextIndex = (currentIndex + 1) % statuses.length;
        let nextStatus = statuses[nextIndex];
        
        $input.val(nextStatus);
        $badge.text(nextStatus);

        let cssClass = nextStatus.toLowerCase();
        
        $badge.removeClass('status-pending status-shipped status-delivered status-cancelled')
              .addClass('status-' + cssClass);
    });

    $('#bulk-update-form').on('submit', function() {
        return confirm("Are you sure you want to update the status for these orders?");
    });
});
</script>

<?php include '../lib/_foot.php'; ?>