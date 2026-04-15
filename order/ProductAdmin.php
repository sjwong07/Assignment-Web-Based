<?php
require '../lib/_base.php';

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
    // 1. CREATE (Add)
    if (isset($_POST['Add'])) {
        $product_id = post('Product_id'); // Manual ID input
        $model  = post('Product_model');
        $price  = post('Product_price');
        $cat_id = post('Category_name');

        // Validation
        if (empty($product_id) || empty($model) || empty($price) || empty($cat_id)) {
            temp('info', '❌ All fields are required!');
            redirect();
        }
        
        // Check if ID already exists
        $check = $_db->prepare("SELECT Product_id FROM Product WHERE Product_id = ?");
        $check->execute([$product_id]);
        if ($check->fetch()) {
            temp('info', '❌ Product ID already exists!');
            redirect();
        }
        
        if ($price <= 0) {
            temp('info', '❌ Price must be greater than 0!');
            redirect();
        }

        $stm = $_db->prepare("INSERT INTO Product (Product_id, Product_model, Product_price, Category_id, product_photo) VALUES (?, ?, ?, ?, NULL)");
        $stm->execute([$product_id, $model, $price, $cat_id]);
        temp('info', '✅ Product Created');
        redirect();
    }

    // 2. UPDATE - FIXED: Get values from the edit form
    if (isset($_POST['Update'])) {
        $id    = post('product_id');
        $model = post('Product_model_update'); // Get from input field
        $price = post('Product_price_update'); // Get from input field
        $cat_id = post('Category_name_update'); // Get from select field

        // Validation
        if (empty($model) || empty($price) || empty($cat_id)) {
            temp('info', '❌ All fields are required!');
            redirect();
        }
        
        if ($price <= 0) {
            temp('info', '❌ Price must be greater than 0!');
            redirect();
        }

        $stm = $_db->prepare("UPDATE Product SET Product_model = ?, Product_price = ?, Category_id = ? WHERE Product_id = ?");
        $stm->execute([$model, $price, $cat_id, $id]);
        temp('info', '✅ Product Updated');
        redirect();
    }

    // 3. DELETE
    if (isset($_POST['Delete'])) {
        $id = post('product_id');
        
        // Get photo to delete file
        $stm = $_db->prepare("SELECT product_photo FROM Product WHERE Product_id = ?");
        $stm->execute([$id]);
        $photo = $stm->fetchColumn();
        
        // Delete photo file if exists
        if ($photo && file_exists('../images/' . $photo)) {
            unlink('../images/' . $photo);
        }

        $stm = $_db->prepare("DELETE FROM Product WHERE Product_id = ?");
        $stm->execute([$id]);
        temp('info', '🗑️ Product Deleted');
        redirect();
    }
}

$_title = 'Product Listing';
include '../lib/_head.php';
?>

<div class="ProductAdmin">
</div>

<div class="container">

<h2>📦 Product Management</h2>

<?php if ($msg = temp('info')): ?>
    <div style="background:#28a745;color:white;padding:10px;margin-bottom:10px;">
        <?= $msg ?>
    </div>
<?php endif; ?>

<?php
$category = "SELECT Category_id,Category_name FROM Category";
$stm = $_db->prepare($category);
$stm->execute();
$categories = $stm->fetchAll();

// Get filters from GET
$userCategory  = get('Category', null);
$min_price = get('min_price', null);
$max_price = get('max_price', null);

// main query
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

// Build categories array for display
$_categories = [];
$cat_stmt = $_db->query("SELECT Category_id, Category_name FROM Category");
foreach ($cat_stmt->fetchAll() as $c) {
    $_categories[$c->Category_id] = $c->Category_name;
}
?>

<!-- FILTER -->
<div class="card filter-box">
<form method="GET" action="">
    <label>Category:</label>
    <select name="Category">
        <option value="">All</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c->Category_name ?>" <?= ($userCategory == $c->Category_name) ? 'selected' : '' ?>>
            <?= $c->Category_name ?>
        </option>
        <?php endforeach; ?>
    </select>

    <label>Min Price:</label>
    <input type="number" name="min_price" value="<?= encode($min_price) ?>">

    <label>Max Price:</label>
    <input type="number" name="max_price" value="<?= encode($max_price) ?>">

    <button class="btn btn-filter">🔍 Filter</button>
</form>
</div>

<!-- Product Table -->
<div class="ProductAdmin">
<table border="1" cellpadding="5">
    <tr>
        <th>Product_ID</th>
        <th>Product_Name</th>
        <th>Product_Price</th>
        <th>Category_name</th>
        <th>Product photo upload</th>
        <th>Product Actions</th>
    </tr>
   
    <!-- ADD NEW PRODUCT ROW - With manual ID input -->
    <tr>
        <form method="post">
           <td><input type="text" name="Product_id" placeholder="ID" required style="width:60px;"></td>
            <td><input type="text" name="Product_model" placeholder="New Model Name" required></td>
            <td><input type="number" name="Product_price" step="0.01" placeholder="0.00" required></td>
            <td>
               <select name="Category_name">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c->Category_id ?>"><?= $c->Category_name ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td style="color:#999;">Upload after add</td>
            <td>
                <button type="submit" name="Add" class="btn-Add">Add Product</button>
            </td>
        </form>    
    </tr>
    
    <?php foreach($products as $p):?>
    <tr>
        <!-- EDIT FORM - Values can be edited directly -->
        <form method="post" id="editForm<?= $p->Product_id ?>">
            <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
            <td><?= encode($p->Product_id) ?></td>
            <td><input type="text" name="Product_model_update" value="<?= encode($p->Product_model) ?>" required></td>
            <td><input type="number" name="Product_price_update" step="0.01" value="<?= $p->Product_price ?>" required></td>
            <td>
                <select name="Category_name_update">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c->Category_id ?>" <?= ($p->Category_id == $c->Category_id) ? 'selected' : '' ?>>
                            <?= $c->Category_name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </form>
        
       <td>
        <!-- PHOTO UPLOAD FORM -->
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
            <input type="file" name="photo" accept="image/*">
            <button class="btn btn-upload" type="submit" name="upload">Upload</button>
        </form>
        <?php if (!empty($p->product_photo)): ?>
            <br><small>📷 Photo uploaded</small>
        <?php endif; ?>
        </td>
        
        <td>
            <!-- UPDATE BUTTON - Submits the edit form -->
            <button type="submit" name="Update" form="editForm<?= $p->Product_id ?>" onclick="return confirm('Update this product?');">✏️ Update</button>
            
            <!-- DELETE FORM -->
            <form method="post" style="margin-top:5px;" onsubmit="return confirm('Delete product #<?= $p->Product_id ?>? This will also delete the photo!');">
                <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
                <button type="submit" name="Delete">🗑️ Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>

<?php include '../lib/_foot.php'; ?>