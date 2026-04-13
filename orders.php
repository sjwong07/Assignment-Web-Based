<?php
session_start();
require_once 'settings.php';

// Check if order ID exists
if (!isset($_GET['id']) && !isset($_SESSION['last_order_id'])) {
    header("Location: /order/cart.php");
    exit();
}

$order_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['last_order_id'];
$total = isset($_SESSION['last_order_total']) ? $_SESSION['last_order_total'] : 0;

// Clear session variables
unset($_SESSION['last_order_id']);
unset($_SESSION['last_order_total']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        h1 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .order-id {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 1.2rem;
        }

        .order-id strong {
            color: #667eea;
            font-size: 1.3rem;
        }

        .total-amount {
            font-size: 1.5rem;
            color: #667eea;
            font-weight: bold;
            margin: 20px 0;
        }

        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #666;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 30px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">🎉</div>
        <h1>Order Placed Successfully!</h1>
        
        <div class="order-id">
            Your Order ID: <strong>#<?php echo $order_id; ?></strong>
        </div>
        
        <div class="total-amount">
            Total Amount: <?php echo CURRENCY; ?> <?php echo number_format($total, 2); ?>
        </div>
        
        <div class="message">
            Thank you for your purchase! <br>
            We have received your order and will process it soon. <br>
            A confirmation email has been sent to your registered email address.
        </div>
        
        <a href="/order/cart.php" class="btn btn-primary">🏠 Continue Shopping</a>
        <a href="orders.php" class="btn btn-secondary">📦 View My Orders</a>
    </div>
</body>
</html>