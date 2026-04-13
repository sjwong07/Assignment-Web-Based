<?php
require '../lib/_base.php';

$_title = 'Product Listing';
include '../lib/_head.php';
?>
<p></p>

<?php

// 1. Get filters from GET
$Category  = get('Category', null);
$min_price = get('min_price', null);
$max_price = get('max_price', null);

$category = "SELECT Category_name FROM Category";
$stm = $_db->prepare($category);
$stm->execute();
$categories = $stm->fetchAll();

// 2. Query products with filters
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
ORDER BY p.Product_model
";

$stm = $_db->prepare($product);
$stm->execute([
    ':category'  => !empty($Category) ? $Category : null,
    ':min_price' => is_numeric($min_price) ? $min_price : null,
    ':max_price' => is_numeric($max_price) ? $max_price : null
]);

$products = $stm->fetchAll();
?>


<form method="GET" action="">
    <label>Category:</label>
    <select name="Category">
        <option value="">All</option>
         <?php foreach ($categories as $c): ?>
        <option value="<?= $c->Category_name ?>">
            <?= $c->Category_name ?>
        </option>
    <?php endforeach; ?>

    </select>
    <label>Min Price:</label>
    <input type="number" name="min_price" value="<?= encode($min_price) ?>">
    <label>Max Price:</label>
    <input type="number" name="max_price" value="<?= encode($max_price) ?>">
    <button type="submit">Filter</button>
</form>

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
        <th>Actions</th>
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
            <input type="file" name='photo' accept='image/*'>
            <button type="submit" name="upload">Upload</button>
        </form>
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

<?php
include '../lib/_foot.php';