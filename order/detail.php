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

        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .detail-header h2 {
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

        .detail-header h2 i {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.8rem;
        }

        /* Order Info Card */
        .order-info-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .order-info-group {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .order-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .order-info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
        }

        .order-info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-info-value i {
            color: #2a5298;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
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

        /* Items Count */
        .items-count {
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            color: #475569;
        }

        .items-count i {
            margin-right: 0.5rem;
            color: #2a5298;
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

        /* Back Button */
        .btn-back {
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: white;
            border: none;
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

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42,82,152,0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .detail-container {
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
            
            .order-info-group {
                gap: 1rem;
            }
            
            .order-info-value {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 640px) {
            .cart-table-wrapper {
                overflow-x: auto;
            }
            
            .cart-table {
                min-width: 550px;
            }
            
            .order-info-card {
                flex-direction: column;
                align-items: flex-start;
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

        .order-info-card, .cart-table-wrapper, .order-summary, .back-section {
            animation: fadeInUp 0.4s ease forwards;
        }

        .cart-table-wrapper {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .order-summary {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .back-section {
            animation-delay: 0.3s;
            opacity: 0;
        }
    </style>
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