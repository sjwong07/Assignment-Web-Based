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
                        <img class="product-img" src="../images/<?= htmlspecialchars($p->product_photo) ?>" 
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
                                    <option value="<?= $i ?>" <?= ($i == 1 ? 'selected' : '') ?>>
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