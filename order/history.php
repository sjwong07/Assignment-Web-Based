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

        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .history-header h2 {
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

        .history-header h2 i {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.8rem;
        }

        /* Stats Card */
        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .stats-card i {
            font-size: 1.8rem;
            color: #2a5298;
        }

        .stats-card .stats-info {
            display: flex;
            flex-direction: column;
        }

        .stats-card .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .stats-card .stats-label {
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .btn-shop {
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

        .btn-shop:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42,82,152,0.3);
        }

        /* Orders Table */
        .orders-table-wrapper {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
            margin-top: 1.5rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .orders-table th {
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

        .orders-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr {
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .orders-table tr:hover {
            background: #f8fafc;
        }

        /* Order ID Badge */
        .order-id {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .order-id i {
            color: #2a5298;
            font-size: 1rem;
        }

        /* Date Styling */
        .order-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #475569;
            font-size: 0.9rem;
        }

        .order-date i {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* Total Amount */
        .order-total {
            font-weight: 700;
            color: #2a5298;
            font-size: 1.1rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-completed {
            background: #d1fae5;
            color: #059669;
        }

        .status-shipped {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        /* View Button */
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

        /* Responsive */
        @media (max-width: 768px) {
            .history-container {
                padding: 1rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .order-id, .order-date, .order-total {
                font-size: 0.85rem;
            }
            
            .btn-view {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }
            
            .stats-card {
                padding: 0.75rem 1rem;
            }
            
            .stats-card .stats-number {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 640px) {
            .orders-table-wrapper {
                overflow-x: auto;
            }
            
            .orders-table {
                min-width: 500px;
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

        .stats-card, .orders-table-wrapper {
            animation: fadeInUp 0.4s ease forwards;
        }

        .orders-table-wrapper {
            animation-delay: 0.1s;
            opacity: 0;
        }
    </style>
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
                        <tr onclick="location='detail.php?id=<?= $o->id ?>&from=history'">
                            <td>
                                <div class="order-id">
                                    <i class="fas fa-hashtag"></i>
                                    #<?= $o->id ?>
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