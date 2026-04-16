<?php
require 'Admin_Access_Required.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    $order_ids = $_POST['order_ids'] ?? [];
    $statuses = $_POST['statuses'] ?? [];
    
    $stm = $_db->prepare('UPDATE `order` SET status = ? WHERE id = ?');
    
    foreach ($order_ids as $index => $id) {
        $stm->execute([$statuses[$index], $id]);
    }
}

// Fetch all orders
$stm = $_db->prepare('
    SELECT o.*, u.username 
    FROM `order` o
    JOIN user u ON o.user_id = u.user_id
    ORDER BY o.id DESC
');
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%); min-height: 100vh; }
        .history-container { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }
        .history-header h2 { font-size: 2rem; font-weight: 700; background: linear-gradient(135deg, #1e3c72, #2a5298); -webkit-background-clip: text; background-clip: text; color: transparent; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; }
        
        .orders-table-wrapper { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        .orders-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .orders-table th { background: #f8fafc; padding: 1.25rem 1rem; text-align: left; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #475569; border-bottom: 2px solid #e2e8f0; }
        .orders-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
            text-align: center;
            min-width: 110px; /* Ensures consistent width */
            border: 1px solid transparent;
        }

        /* Color Variations */
        .status-Pending { background: #fff7ed; color: #9a3412; border-color: #ffedd5; }
        .status-Shipped { background: #eff6ff; color: #1e40af; border-color: #dbeafe; }
        .status-Completed { background: #f0fdf4; color: #166534; border-color: #dcfce7; }
        .status-Cancelled { background: #fef2f2; color: #991b1b; border-color: #fee2e2; }

        .btn-view {
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42,82,152,0.3);
        }
        
        tbody tr:hover {
            background-color: #f8faff;
            transition: background 0.2s ease;
            cursor: default;
        }
        
        .submit-container { display: flex; justify-content: flex-end; margin-top: 1.5rem; }
        .btn-submit { background: linear-gradient(135deg, #059669, #047857); color: white; border: none; padding: 0.8rem 2rem; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(5,150,105,0.3); }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .orders-table-wrapper { animation: fadeInUp 0.4s ease forwards; }
    </style>
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
                                <td style="font-size: 0.9rem; color: #475569;"><?= date('d M Y, h:i A', strtotime($o->order_date)) ?></td>
                                <td style="font-weight: 700; color: #2a5298;">RM <?= number_format($o->total, 2) ?></td>
                                <td>
                                    <button type="button" class="btn-view" onclick="location='detail.php?id=<?= $o->id ?>&from=admin'">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </td>
                                <td>
                                    <input type="hidden" name="statuses[]" class="status-input" value="<?= $o->status ?>">
                                    <span class="status-badge status-<?= $o->status ?>">
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
    <?php endif ?>
</div>

<script>
$(document).ready(function() {
    const statuses = ['Pending', 'Shipped', 'Completed', 'Cancelled'];

    $('.status-badge').on('click', function() {
        const $badge = $(this);
        const $input = $badge.siblings('.status-input');
        
        let currentIndex = statuses.indexOf($input.val());
        let nextIndex = (currentIndex + 1) % statuses.length;
        let nextStatus = statuses[nextIndex];
        
        // Update input value
        $input.val(nextStatus);
        
        // Update Text
        $badge.text(nextStatus);
        
        // Update color 
        $badge.removeClass('status-Pending status-Shipped status-Completed status-Cancelled')
              .addClass('status-' + nextStatus);
    });

    $('#bulk-update-form').on('submit', function() {
        return confirm("Are you sure you want to update the status for these orders?");
    });
});
</script>

<?php include '../lib/_foot.php'; ?>