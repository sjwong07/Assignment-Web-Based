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
            
            $stm->execute([$filename, $Product_id]);
            
            temp('info', '✅ Photo uploaded successfully!');
            
        } catch (PDOException $e) {
            unlink($path); // Delete the file
            temp('info', '❌ Database error: ' . $e->getMessage());
        }
        
    } else {
        temp('info', '❌ Failed to save file');
    }
    
    redirect();
}
?>
<?php


if (is_post()) {
    // 1. CREATE
    if (isset($_POST['create'])) {
        $model  = post('Product_model');
        $price  = post('Product_price');
        $cat_id = post('Category_id');

        $stm = $_db->prepare("INSERT INTO Product (Product_model, Product_price, Category_id) VALUES (?, ?, ?)");
        $stm->execute([$model, $price, $cat_id]);
        temp('info', '✅ Product Created');
        redirect();
    }

    // 2. UPDATE
    if (isset($_POST['update'])) {
        $id    = post('product_id');
        $model = post('Product_model');
        $price = post('Product_price');

        $stm = $_db->prepare("UPDATE Product SET Product_model = ?, Product_price = ? WHERE Product_id = ?");
        $stm->execute([$model, $price, $id]);
        temp('info', '✅ Product Updated');
        redirect();
    }

    // 3. DELETE
    if (isset($_POST['delete'])) {
        $id = post('product_id');

        $stm = $_db->prepare("DELETE FROM Product WHERE Product_id = ?");
        $stm->execute([$id]);
        temp('info', '🗑️ Product Deleted');
        redirect();
    }
}
?>
<?php
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


$category = "SELECT Category_name FROM Category";
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

?>

<!-- ================= FILTER ================= -->
<div class="card filter-box">

<form method="GET" action="">
    <label>Category:</label>
    <select name="Category">
        <option value="">All</option>
         <?php foreach ($categories as $c): ?>

        <option value="<?= $c->Category_name ?>" 
        <?= ($userCategory == $c->Category_name) ? 'selected' : '' ?>>
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

<!-- 4. Product Table -->
  <div class="ProductAdmin">
<table border="1" cellpadding="5">
    <tr>
        <th>Product_ID</th>
        <th>Product_Name</th>
        <th>Product_Price</th>
        <th>Category</th>
        <th>Actions</th>
    </tr>

    <tr>
        <form method="post">
            <td>NEW</td>
            <td><input type="text" name="Product_model" placeholder="New Model Name" required></td>
            <td><input type="number" name="Product_price" step="0.01" placeholder="0.00" required></td>
            <td>
                <select name="Category_id">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c->Category_id ?>"><?= $c->Category_name ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <button type="submit" name="create" class="btn-create">➕ Create</button>
            </td>
        </form>
    </tr>

    <?php foreach($products as $p): ?>
    <tr>
        <form method="post">
            <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
            
            <td><?= $p->Product_id ?></td>
            <td><input type="text" name="Product_model" value="<?= encode($p->Product_model) ?>"></td>
            <td><input type="number" name="Product_price" step="0.01" value="<?= $p->Product_price ?>"></td>
            <td><?= encode($p->Category_name) ?></td>
            
            <td>
                <button type="submit" name="update" class="btn-update">✏️Update</button>
                <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Delete this product?')">🗑️ Delete</button>
            </td>
        </form>
    </tr>
    <?php endforeach; ?>
</table>
<?php
$f = get_file('photo');
if (isset($_POST['upload'])) {
    // Get the uploaded file
    $f = get_file('photo');

    // Validate
    if ($f === null) {
        echo "Photo needed";
    } else {
        echo "File ready to upload!";
    }
}
?>

<?php include '../lib/_foot.php'; ?>
