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
    $folder = '../uploads/';
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
            unlink($path); // Delete the file
            temp('info', '❌ Database error: ' . $e->getMessage());
        }
        
    } else {
        temp('info', '❌ Failed to save file');
    }
    
    redirect();
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
        <th>Product_Category</th>
        <th>Category_description</th>
        <th>Product photo upload</th>
        <th>Product Actions</th>
        <th>Category Actions</th>
    </tr>
    <?php foreach($products as $p):?>
    <tr>
        <td><?= encode($p->Product_id) ?></td>
        <td><?= encode($p->Product_model) ?></td>
        <td><?= number_format($p->Product_price, 2) ?></td>
        <td><?= encode($_categories[$p->Category_id] ?? $p->Category_id) ?></td>
       <td><?= encode($p->Category_name) ?></td>
       <td>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
            <input type="file" name="photo" accept="image/*">
            <button class="btn btn-upload" type="submit" name="upload">Upload</button>
        </form>
        </td>
         <td>
            <button>Add Product</button>
            <button>Update Product</button>
            <button>Delete Product</button>
        </td>
        <td>
            <button>Add Product</button>
            <button>Update Product</button>
            <button>Delete Product</button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
    </div>
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
