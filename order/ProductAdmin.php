<?php

require_once 'Admin_Access_Required.php';
require_once '../lib/_base.php';

// Check if NOT Admin - show message and STOP
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    include '../lib/_head.php';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .message-box {
                background: white;
                padding: 3rem;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 450px;
            }
            .message-box i {
                font-size: 4rem;
                color: #f59e0b;
                margin-bottom: 1.5rem;
            }
            .message-box h2 {
                color: #1e293b;
                margin-bottom: 1rem;
                font-size: 1.5rem;
            }
            .message-box p {
                color: #64748b;
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .message-box .role-badge {
                display: inline-block;
                background: #e2e8f0;
                padding: 0.25rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                color: #475569;
                margin-top: 0.5rem;
            }
        </style>
    </head>
    <body>
        <div class="message-box">
            <i class="fas fa-shield-alt"></i>
            <h2>Admin Access Required</h2>
            <p>This page is restricted to administrators only.</p>
            <p>Your current role: 
                <span class="role-badge">
                    <i class="fas fa-user"></i> 
                    <?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Guest' ?>
                </span>
            </p>
            <p style="margin-top: 1.5rem; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> 
                <a href="javascript:history.back()" style="color: #2a5298; text-decoration: none; font-weight: 600;">Go Back</a>
            </p>
        </div>
    </body>
    </html>
    <?php
    include '../lib/_foot.php';
    exit(); // STOP execution - no admin content shown
}

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

        $stm = $_db->prepare("INSERT INTO Product (Product_model, Product_price, Category_id, Product_photo) VALUES (?, ?, ?, NULL)");
        $stm->execute([$model, $price, $cat_id]);
        temp('info', '✅ Product Created');
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
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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

        .stats-badge {
            background: white;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e293b;
        }

        .stats-badge i {
            color: #2a5298;
            margin-right: 0.5rem;
        }

        /* Toast Message */
        .toast-message {
            position: fixed;
            top: 20px;
            right: 20px;
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

        /* Card Styles */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header i {
            color: #2a5298;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Filter Box */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.6rem 1rem;
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
            padding: 0.6rem 1.5rem;
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

        /* Table Styles */
        .product-table-wrapper {
            overflow-x: auto;
        }

        .product-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .product-table th {
            background: #f1f5f9;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .product-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .product-table tr:hover {
            background: #f8fafc;
        }

        /* Inputs inside table */
        .product-table input[type="text"],
        .product-table input[type="number"],
        .product-table select {
            padding: 0.5rem 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .product-table input:focus,
        .product-table select:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 2px rgba(42,82,152,0.1);
        }

        /* Buttons */
        .btn-add {
            background: linear-gradient(135deg, #10b981, #059669);
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

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }

        .btn-update {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
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
            width: 100%;
        }

        .btn-update:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
            width: 100%;
            margin-top: 0.5rem;
        }

        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }

        .btn-upload {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.75rem;
        }

        .btn-upload:hover {
            background: #e2e8f0;
        }

        .new-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: #10b981;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .photo-preview {
            max-width: 50px;
            max-height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 0.5rem;
        }

        .photo-info {
            font-size: 0.7rem;
            color: #64748b;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
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
            
            .product-table th,
            .product-table td {
                padding: 0.75rem;
            }
        }
    </style>
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
    ORDER BY p.Product_model";

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