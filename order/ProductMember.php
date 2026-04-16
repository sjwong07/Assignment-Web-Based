<?php
require '../lib/_base.php';

// ================= CART =================
if (is_post() && isset($_POST['id'])) {
    $id = post('id');
    $unit = post('unit');

    if ($unit > 0) {
        update_cart($id, $unit);
        temp('info', 'Added to cart successfully!');
        redirect();
    }
}


include '../lib/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing | Shop</title>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h2 i {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.8rem;
        }

        .cart-icon {
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .cart-icon i {
            font-size: 1.4rem;
            color: #2a5298;
        }

        .cart-icon span {
            font-weight: 600;
            color: #1e293b;
        }

        /* Toast Message */
        .toast-message {
            position: fixed;
            top: 120px;
            right: 40%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 1000;
            animation: slideInRight 0.3s ease;
            font-weight: 500;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Filter Box */
        .filter-box {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1.25rem;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: #f8fafc;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42,82,152,0.1);
            background: white;
        }

        .btn-filter {
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42,82,152,0.3);
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
        }

        .product-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.15);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }

        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            background: #f1f5f9;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.9rem;
            flex-direction: column;
            gap: 0.5rem;
        }

        .product-info {
            padding: 1.25rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .product-category {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2a5298;
            margin-bottom: 1rem;
        }

        .product-price small {
            font-size: 0.8rem;
            font-weight: 400;
            color: #64748b;
        }

        .cart-form {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .unit-select {
            flex: 1;
            padding: 0.6rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            background: #f8fafc;
            cursor: pointer;
        }

        .btn-add {
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: white;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .btn-add:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(42,82,152,0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 24px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .filter-form {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .btn-filter {
                width: 100%;
                justify-content: center;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                height: 350px;
            }
            
            .image-placeholder {
                height: 350px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <i class="fas fa-store"></i>
            Our Products
        </h2>
        
    </div>

    <?php if ($msg = temp('info')): ?>
        <div class="toast-message" id="tempMsg">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($msg) ?>
        </div>
        <script>
            setTimeout(() => {
                let el = document.getElementById('tempMsg');
                if (el) el.style.opacity = '0';
                setTimeout(() => {
                    if (el) el.remove();
                }, 300);
            }, 2000);
        </script>
    <?php endif; ?>

    <?php
    // FILTER 
    $category       = get('category');
    $min_price      = get('min_price');
    $max_price      = get('max_price');
    $category_desc  = get('category_desc');

    // LOAD CATEGORY LIST
    $category_sql = "SELECT Category_id, Category_name FROM Category";
    $category_stmt = $_db->query($category_sql);
    $category_list = $category_stmt->fetchAll();

    $_categories = [];
    foreach ($category_list as $c) {
        $_categories[$c->Category_id] = $c->Category_name;
    }

    // PRODUCT QUERY
    $sql = "SELECT p.Product_id, p.Product_model, p.Product_price, p.Stock,  
                   p.Category_id, p.product_photo, c.Category_name
            FROM Product p
            JOIN Category c ON p.Category_id = c.Category_id
            WHERE 1=1
            AND (:category IS NULL OR p.Category_id = :category)
            AND (:min_price IS NULL OR p.Product_price >= :min_price)
            AND (:max_price IS NULL OR p.Product_price <= :max_price)
            AND (:category_desc IS NULL OR c.Category_name = :category_desc)
            ORDER BY p.Product_model";

    $stm = $_db->prepare($sql);
    $stm->execute([
        ':category'       => $category ?: null,
        ':min_price'      => $min_price ?: null,
        ':max_price'      => $max_price ?: null,
        ':category_desc'  => $category_desc ?: null
    ]);

    $products = $stm->fetchAll();
    ?>

    <!-- FILTER FORM -->
    <div class="filter-box">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label><i class="fas fa-tags"></i> Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($_categories as $id => $name): ?>
                        <option value="<?= htmlspecialchars($id) ?>" <?= ($category == $id ? 'selected' : '') ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-dollar-sign"></i> Min Price</label>
                <input type="number" name="min_price" placeholder="Any" value="<?= htmlspecialchars($min_price ?? '') ?>">
            </div>

            <div class="filter-group">
                <label><i class="fas fa-dollar-sign"></i> Max Price</label>
                <input type="number" name="max_price" placeholder="Any" value="<?= htmlspecialchars($max_price ?? '') ?>">
            </div>

            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
        </form>
    </div>

    <!-- PRODUCT GRID -->
    <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach($products as $p): 
                $cart = get_cart();    
                $current_unit = $cart[$p->Product_id] ?? 0;
            ?>
                <div class="product-card">
                    <?php if ($current_unit > 0): ?>
                        <div class="product-badge">
                            <i class="fas fa-check"></i> In Cart: <?= $current_unit ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    $image_path = "../images/" . $p->product_photo;
                    if (!empty($p->product_photo) && file_exists($image_path)): 
                    ?>
                        <img class="product-image" src="../images/<?= htmlspecialchars($p->product_photo) ?>" 
                             alt="<?= htmlspecialchars($p->Product_model) ?>">
                    <?php elseif (!empty($p->product_photo)): ?>
                        <div class="image-placeholder">
                            <i class="fas fa-image" style="font-size: 3rem; opacity: 0.5;"></i>
                            <span><?= htmlspecialchars($p->product_photo) ?></span>
                            <small>(Missing file)</small>
                        </div>
                    <?php else: ?>
                        <div class="image-placeholder">
                            <i class="fas fa-camera" style="font-size: 3rem; opacity: 0.5;"></i>
                            <span>No Image Available</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <div class="product-title">
                            <span><?= htmlspecialchars($p->Product_model) ?></span>
                            <small style="font-size: 0.7rem; color: #94a3b8;">#<?= $p->Product_id ?></small>
                        </div>
                        <div class="product-category">
                            <i class="fas fa-folder"></i> <?= htmlspecialchars($p->Category_name) ?>
                        </div>
                        <div class="product-price">
                            RM <?= number_format($p->Product_price, 2) ?>
                            <small></small>
                        </div>
                        
                        <form method="post" class="cart-form">
                            <input type="hidden" name="id" value="<?= $p->Product_id ?>">
                            <select name="unit" class="unit-select">
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($current_unit == $i ? 'selected' : '') ?>>
                                        Qty: <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <button type="submit" class="btn-add" <?= ($p->Stock <= 0 ? 'disable' : '') ?>>
                                <i class="fas fa-cart-plus"></i> <?= ($p->Stock <= 0 ? 'Out of Stock' : 'Add') ?>
                            </button>
                        </form>
                        <div style="font-size: 0.8rem; margin-top: 10px; color: <?= ($p->Stock < 10 ? '#ef4444' : '#64748b') ?>;">
                            <i class="fas fa-cubes"></i> Stock: <?= $p->Stock ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No products found</h3>
            <p>Try adjusting your filters or check back later for new items!</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../lib/_foot.php'; ?>