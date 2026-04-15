<?php
include '../lib/_base.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the current user
$stm = $_db->prepare('SELECT * FROM `order` WHERE user_id = ? ORDER BY id DESC');
$stm->execute([$user_id]);
$arr = $stm->fetchAll(PDO::FETCH_OBJ);


include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status | Track Your Orders</title>
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

        .status-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .status-header h2 {
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

        .status-header h2 i {
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
        }

        .orders-table tr:hover {
            background: #f8fafc;
        }

        /* Order ID */
        .order-id {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .order-id i {
            color: #2a5298;
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
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-shipping {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-arrived {
            background: #d1fae5;
            color: #059669;
        }

        .status-out-of-stock {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Alert Text */
        .alert-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.7rem;
            font-weight: 500;
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

        /* Progress Timeline for each row */
        .timeline-preview {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .timeline-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
        }

        .timeline-dot.active {
            background: #2a5298;
        }

        .timeline-dot.completed {
            background: #10b981;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .status-container {
                padding: 1rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .order-id, .order-date {
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
            
            .status-badge {
                padding: 0.3rem 0.75rem;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 640px) {
            .orders-table-wrapper {
                overflow-x: auto;
            }
            
            .orders-table {
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

<div class="status-container">
    <!-- Header -->
    <div class="status-header">
        <h2>
            <i class="fas fa-truck"></i>
            Order Status
        </h2>
        <div class="stats-card">
            <i class="fas fa-clipboard-list"></i>
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
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arr as $o): ?>
                    <?php 
                        $status_class = 'status-pending';
                        $current_status = $o->status ?: 'Pending';
                        $status_icon = 'fa-clock';

                        if ($current_status == 'Shipping') {
                            $status_class = 'status-shipping';
                            $status_icon = 'fa-shipping-fast';
                        }
                        if ($current_status == 'Arrived') {
                            $status_class = 'status-arrived';
                            $status_icon = 'fa-check-circle';
                        }
                        if ($current_status == 'Out of Stock') {
                            $status_class = 'status-out-of-stock';
                            $status_icon = 'fa-exclamation-triangle';
                        }
                    ?>
                    <tr>
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
                            <div>
                                <span class="status-badge <?= $status_class ?>">
                                    <i class="fas <?= $status_icon ?>"></i>
                                    <?= htmlspecialchars($current_status) ?>
                                </span>
                                
                                <?php if ($current_status == 'Out of Stock'): ?>
                                    <span class="alert-text">
                                        <i class="fas fa-exclamation-circle"></i> 
                                        Please contact us for refund
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <button class="btn-view" onclick="location='detail.php?id=<?= $o->id ?>&from=status'">
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
            <i class="fas fa-box-open"></i>
            <h3>No orders found</h3>
            <p>You haven't placed any orders yet. Start shopping to track your orders here!</p>
            <a href="/product/" class="btn-shop">
                <i class="fas fa-store"></i>
                Start Shopping
            </a>
        </div>
    <?php endif ?>
</div>

<?php include '../lib/_foot.php'; ?>