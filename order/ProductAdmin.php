<?php

require_once 'Admin_Access_Required.php';
require_once '../lib/_base.php';
  
if (is_post() && isset($_POST['upload'])) {
    
    $product_id = post('product_id');
    
    // Check if file was uploaded
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        temp('info', '❌ Please select a photo');
        redirect();
    }
    
    $file = $_FILES['photo'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        temp('info', '❌ Only JPG, PNG, GIF, and WEBP files are allowed');
        redirect();
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        temp('info', '❌ File size must be less than 5MB');
        redirect();
    }
    
    // Create upload folder
    $folder = '../images/';
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $path = $folder . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $path)) {
        
        // Update database
        try {
            $stm = $_db->prepare("
                UPDATE Product 
                SET product_photo = ? 
                WHERE Product_id = ?
            ");
            
            $stm->execute([$filename, $product_id]);
            
            temp('info', '✅ Photo uploaded successfully!');
            
        } catch (PDOException $e) {
            unlink($path);
            temp('info', '❌ Database error: ' . $e->getMessage());
        }
        
    } else {
        temp('info', '❌ Failed to save file');
    }
    
    redirect();
}

if (is_post()) {
    // 1. CREATE
    if (isset($_POST['Add'])) {
        $model  = post('Product_model');
        $price  = post('Product_price');
        $cat_id = post('Category_name');
        $photo = post('Product_photo');

        if (empty($model) || empty($price) || empty($cat_id)) {
            temp('info', '❌ All fields are required!');
            redirect();
        }
        
        if ($price <= 0) {
            temp('info', '❌ Price must be greater than 0!');
            redirect();
        }

        $stm = $_db->prepare("SELECT Category_name FROM Category WHERE Category_id = ?");
        $stm->execute([$cat_id]);
        $category = $stm->fetchColumn();
    
        $cat_upper = strtoupper($category);
    
        if (strpos($cat_upper, 'IPHONE') !== false) {
        $prefix = 'A';
        } else {
        $prefix = strtoupper(substr($category, 0, 1));
        }
    
        $stm = $_db->prepare("SELECT COUNT(*) FROM Product WHERE Category_id = ?");
        $stm->execute([$cat_id]);
        $count = $stm->fetchColumn();
    
        $product_id = $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    
        $stm = $_db->prepare("SELECT COUNT(*) FROM Product WHERE Product_id LIKE ?");
        $stm->execute([$prefix . '%']);
        $count = $stm->fetchColumn();
    
        $product_id = $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $stm = $_db->prepare("INSERT INTO Product (Product_id, Product_model, Product_price, Category_id, Product_photo) VALUES (?, ?, ?, ?, NULL)");
        $stm->execute([$product_id, $model, $price, $cat_id]);
        temp('info', "Product Created (ID: $product_id)");
        
        /*temp('info', 'Product Created');*/
        redirect();
    }

    // 2. UPDATE
    if (isset($_POST['Update'])) {
        $id    = post('product_id');
        $model = post('Product_model');
        $price = post('Product_price');
        $cat_id = post('Category_name');
        $photo = post('Product_photo');

        $stm = $_db->prepare("UPDATE Product SET Product_model = ?, Product_price = ?, Category_id = ?, Product_photo = ? WHERE Product_id = ?");
        $stm->execute([$model, $price, $cat_id, $photo, $id]);
        temp('info', '✅ Product Updated');
        redirect();
    }

    // 3. DELETE
    if (isset($_POST['Delete'])) {
        $id = post('product_id');
        
        $stm = $_db->prepare("SELECT product_photo FROM Product WHERE Product_id = ?");
        $stm->execute([$id]);
        $photo = $stm->fetchColumn();
        
        if ($photo && file_exists('../images/' . $photo)) {
            unlink('../images/' . $photo);
        }

        $stm = $_db->prepare("DELETE FROM Product WHERE Product_id = ?");
        $stm->execute([$id]);
        temp('info', '🗑️ Product Deleted');
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
    <title>Product Management | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <i class="fas fa-boxes"></i>
            Product Management
        </h2>
        <div class="stats-badge">
            <i class="fas fa-database"></i> Admin Dashboard
        </div>
    </div>

    <?php if ($msg = temp('info')): ?>
        <div class="toast-message" id="tempMsg">
            <i class="fas <?= strpos($msg, '✅') !== false ? 'fa-check-circle' : (strpos($msg, '🗑️') !== false ? 'fa-trash-alt' : 'fa-info-circle') ?>"></i>
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
    $category = "SELECT Category_id,Category_name FROM Category";
    $stm = $_db->prepare($category);
    $stm->execute();
    $categories = $stm->fetchAll();

    $userCategory  = get('Category', null);
    $min_price = get('min_price', null);
    $max_price = get('max_price', null);

    $product = "SELECT 
        p.*, 
        c.Category_name
    FROM product p
    JOIN Category c 
        ON p.Category_id = c.Category_id
    WHERE 1=1
        AND (:category IS NULL OR c.Category_name = :category)
        AND (:min_price IS NULL OR p.Product_price >= :min_price)
        AND (:max_price IS NULL OR p.Product_price <= :max_price)
    ORDER BY p.Product_id,p.Product_model";

    $stm = $_db->prepare($product);
    $stm->execute([
        ':category'  => !empty($userCategory) ? $userCategory : null,
        ':min_price' => is_numeric($min_price) ? $min_price : null,
        ':max_price' => is_numeric($max_price) ? $max_price : null
    ]);

    $products = $stm->fetchAll();

    $_categories = [];
    $cat_stmt = $_db->query("SELECT Category_id, Category_name FROM Category");
    foreach ($cat_stmt->fetchAll() as $c) {
        $_categories[$c->Category_id] = $c->Category_name;
    }
    ?>

    <!-- Filter Section -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-filter"></i>
            Filter Products
        </div>
        <div class="card-body">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Category</label>
                    <select name="Category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c->Category_name) ?>" 
                                <?= ($userCategory == $c->Category_name) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c->Category_name) ?>
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
                    <i class="fas fa-search"></i> Apply Filters
                </button>
            </form>
        </div>
    </div>

    <!-- Product Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i>
            Product List
            <span style="margin-left: auto; font-size: 0.8rem; font-weight: normal;">
                <i class="fas fa-box"></i> <?= count($products) ?> products
            </span>
        </div>
        <div class="card-body">
            <div class="product-table-wrapper">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Photo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- ADD NEW PRODUCT ROW -->
                        <tr style="background: #f0fdf4;">
                            <form method="post">
                                <td>
                                    <span class="new-badge">
                                        <i class="fas fa-plus"></i> NEW
                                    </span>
                                </td>
                                <td><input type="text" name="Product_model" placeholder="Enter product name" required></td>
                                <td><input type="number" name="Product_price" step="0.01" placeholder="0.00" required></td>
                                <td>
                                    <select name="Category_name" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c->Category_id ?>"><?= htmlspecialchars($c->Category_name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="Stock" value="100" min="0" required>
                                </td>
                                <td style="color:#999; font-size:0.75rem;">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload after add
                                </td>
                                <td>
                                    <button type="submit" name="Add" class="btn-add">
                                        <i class="fas fa-plus"></i> Add Product
                                    </button>
                                </td>
                            </form>    
                        </tr>
                        
                        <?php foreach($products as $p): ?>
                        <tr>
                            <!-- EDIT FORM -->
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
                                <td><?= htmlspecialchars($p->Product_id) ?></td>
                                <td><input type="text" name="Product_model" value="<?= htmlspecialchars($p->Product_model) ?>" required></td>
                                <td><input type="number" name="Product_price" step="0.01" value="<?= $p->Product_price ?>" required></td>
                                <td>
                                    <select name="Category_name" required>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c->Category_id ?>" <?= ($p->Category_id == $c->Category_id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c->Category_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </form>
                            
                            <!-- show stock -->
                            <td style="font-weight: bold; color: <?=  $p->Stock < 10 ? 'red' : 'inherit' ?>;">
                                <input type="number" name="Stock" value="<?= $p->Stock ?>" min="0" style="width: 70px;" required>
                            </td>
                            
                            <!-- PHOTO UPLOAD -->
                            <td>
                                <form method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-start;">
                                    <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
                                    <input type="file" name="photo" accept="image/*" style="font-size: 0.7rem;">
                                    <button class="btn-upload" type="submit" name="upload">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </form>
                                <?php if (!empty($p->product_photo)): ?>
                                    <div class="photo-info">
                                        <?php 
                                        $photo_path = '../images/' . $p->product_photo;
                                        if (file_exists($photo_path)): ?>
                                            <img src="../images/<?= htmlspecialchars($p->product_photo) ?>" class="photo-preview" alt="photo">
                                        <?php endif; ?>
                                        <div><i class="fas fa-image"></i> <?= substr(htmlspecialchars($p->product_photo), 0, 20) ?>...</div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            

                            <!-- ACTION BUTTONS -->
                            <td class="action-buttons">
                                <form method="post" onsubmit="return confirm('Update this product?');">
                                    <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
                                    <input type="hidden" name="Product_model" value="<?= htmlspecialchars($p->Product_model) ?>">
                                    <input type="hidden" name="Product_price" value="<?= $p->Product_price ?>">
                                    <input type="hidden" name="Category_name" value="<?= $p->Category_id ?>">
                                    <input type="hidden" name="Product_photo" value="<?= $p->product_photo ?>">
                                    <button type="submit" name="Update" class="btn-update">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                </form>
                                
                                <form method="post" onsubmit="return confirm('Delete product #<?= $p->Product_id ?>? This will also delete the photo!');">
                                    <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
                                    <button type="submit" name="Delete" class="btn-delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../lib/_foot.php'; ?>