<?php
session_start(); // Add this at the top

// --- Product Database (from your table) ---
$products = [
    'A001' => [
        'name' => '17E',
        'price' => 2999,
        'specs' => 'Standard 2026 Mobile: 6.5" AMOLED, 8GB RAM, 50MP Main Camera, 5000mAh, 5G Connectivity.'
    ],
    'A002' => [
        'name' => '17 Pro',
        'price' => 5200,
        'specs' => '6.3" Super Retina XDR, A19 Pro Chip, 12GB RAM, 48MP Triple Fusion, Always-On Display.'
    ],
    'A003' => [
        'name' => '17 Pro Max',
        'price' => 5999,
        'specs' => '6.9" Super Retina XDR, A19 Pro Chip, 12GB RAM, 48MP Triple Fusion, Wi-Fi 7, Titanium Body.'
    ],
    'A004' => [
        'name' => '16E',
        'price' => 2999,
        'specs' => 'Standard 2026 Mobile: 6.5" AMOLED, 8GB RAM, 50MP Main Camera, 5000mAh, 5G Connectivity.'
    ],
    'A005' => [
        'name' => 'Air',
        'price' => 5999,
        'specs' => 'Standard 2026 Mobile: 6.5" AMOLED, 8GB RAM, 50MP Main Camera, 5000mAh, 5G Connectivity.'
    ],
    'AT001' => [
        'name' => 'Pro 13 (2025)',
        'price' => 5799,
        'specs' => '13" Tandem OLED, M5 Chip, 16GB RAM, FaceID, 4-Speaker Audio.'
    ]
];

// --- Get product_id from URL ---
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';

// --- Validate and fetch product ---
if ($product_id === '' || !isset($products[$product_id])) {
    $product = null;
} else {
    $product = $products[$product_id];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - <?php echo $product ? htmlspecialchars($product['name']) : 'Not Found'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        /* Toast Notification Style */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #48bb78;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
            display: none;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .container {
            max-width: 800px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .product-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .product-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .product-id {
            font-size: 0.9rem;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .product-body {
            padding: 30px;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .price span {
            font-size: 1rem;
            font-weight: normal;
            color: #718096;
        }

        .specs {
            background: #f7fafc;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
        }

        .specs h3 {
            color: #2d3748;
            margin-bottom: 12px;
            font-size: 1.2rem;
        }

        .specs p {
            color: #4a5568;
            line-height: 1.6;
            font-size: 1rem;
        }

        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 40px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .error {
            text-align: center;
            padding: 50px 30px;
        }

        .error h2 {
            color: #e53e3e;
            margin-bottom: 15px;
        }

        .error p {
            color: #718096;
            margin-bottom: 25px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .product-header h1 {
                font-size: 1.8rem;
            }
            .product-body {
                padding: 20px;
            }
            .price {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast" class="toast-notification"></div>

    <div class="container">
        <?php if ($product === null): ?>
            <!-- Error: Product not found -->
            <div class="card">
                <div class="error">
                    <h2>⚠️ Product Not Found</h2>
                    <p>The product ID "<strong><?php echo htmlspecialchars($product_id); ?></strong>" does not exist in our catalog.</p>
                    <a href="#" class="btn btn-secondary" onclick="history.back(); return false;">← Go Back</a>
                    <br>
                    <a href="#" class="back-link" onclick="history.back(); return false;">Or click here to return to previous page</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Product details -->
            <div class="card">
                <div class="product-header">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-id">Product ID: <?php echo htmlspecialchars($product_id); ?></div>
                </div>
                <div class="product-body">
                    <div class="price">
                        RM <?php echo number_format($product['price'], 0, '.', ','); ?>
                        <span>(MYR)</span>
                    </div>
                    
                    <div class="specs">
                        <h3>📱 Specifications</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['specs'])); ?></p>
                    </div>
                    
                    <div class="actions">
                        <!-- Add to Cart button removed -->
                        <button class="btn btn-secondary" onclick="history.back()">← Back</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.backgroundColor = isError ? '#e53e3e' : '#48bb78';
            toast.style.display = 'block';
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        function updateCartCount(count) {
            // You can add a cart counter element to your page
            // For example: document.getElementById('cart-count').textContent = count;
            console.log('Cart count updated:', count);
        }
    </script>
</body>
</html>