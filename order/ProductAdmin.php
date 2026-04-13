<?php
require '../lib/_base.php';

/* ================= UPLOAD HANDLER (FIXED) ================= */
if (is_post() && isset($_POST['upload'])) {

    $product_id = post('product_id');
    $file = get_file('photo');

    if ($file === null) {
        temp('info', '❌ Please select a photo');
        redirect();
    }

    // create upload folder if not exists
    $folder = '../uploads/';
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // unique file name
    $filename = uniqid() . '_' . $file->name;
    $path = $folder . $filename;

    // move file
    if (move_uploaded_file($file->tmp_name, $path)) {

        // save into database (MAKE SURE column exists)
        $stm = $_db->prepare("
            UPDATE Product
            SET product_photo = :photo
            WHERE Product_id = :id
        ");

        $stm->execute([
            ':photo' => $filename,
            ':id' => $product_id
        ]);

        temp('info', '✅ Upload successful!');
    } else {
        temp('info', '❌ Upload failed!');
    }

    redirect();
}

$_title = 'Product Listing';
include '../lib/_head.php';
?>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f5f7fa;
}

.container {
    width: 95%;
    margin: 20px auto;
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.filter-box input,
.filter-box select {
    padding: 6px;
    margin-right: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-filter {
    background: #007bff;
    color: white;
}

.btn-upload {
    background: #28a745;
    color: white;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #343a40;
    color: white;
}

.table th, .table td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

img {
    border-radius: 6px;
}
</style>

<div class="container">

<h2>📦 Product Management</h2>

<?php if ($msg = temp('info')): ?>
    <div style="background:#28a745;color:white;padding:10px;margin-bottom:10px;">
        <?= $msg ?>
    </div>
<?php endif; ?>

<?php
// ================= FILTER =================
$category       = get('category');
$min_price      = get('min_price');
$max_price      = get('max_price');
$category_desc  = get('category_desc');

// get descriptions for dropdown
$desc_sql = "SELECT DISTINCT Category_name FROM Category";
$descriptions = $_db->query($desc_sql)->fetchAll();

// main query
$sql = "SELECT 
    p.*, 
    c.Category_name
FROM product p
JOIN Category c 
    ON p.Category_id = c.Category_id
WHERE 1=1
    AND (:category IS NULL OR p.Category_id = :category)
    AND (:min_price IS NULL OR p.Product_price >= :min_price)
    AND (:max_price IS NULL OR p.Product_price <= :max_price)
    AND (:category_desc IS NULL OR c.Category_name = :category_desc)
ORDER BY p.Product_model";

$stm = $_db->prepare($sql);
$stm->execute([
    ':category'      => $category ?: null,
    ':min_price'     => $min_price ?: null,
    ':max_price'     => $max_price ?: null,
    ':category_desc' => $category_desc ?: null
]);

$products = $stm->fetchAll();
?>

<!-- ================= FILTER ================= -->
<div class="card filter-box">
<form method="GET">

    <label>Category:</label>
    <select name="category">
        <option value="">All</option>
        <?php foreach($_categories as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($category == $id ? 'selected' : '') ?>>
                <?= $name ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Min Price:</label>
    <input type="number" name="min_price" value="<?= encode($min_price) ?>">

    <label>Max Price:</label>
    <input type="number" name="max_price" value="<?= encode($max_price) ?>">

    <label>Description:</label>
    <select name="category_desc">
        <option value="">All</option>
        <?php foreach ($descriptions as $d): ?>
            <option value="<?= $d->Category_name ?>"
                <?= ($category_desc == $d->Category_name ? 'selected' : '') ?>>
                <?= $d->Category_name ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-filter">🔍 Filter</button>
</form>
</div>

<!-- ================= TABLE ================= -->
<div class="card">
<table class="table">

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Price</th>
    <th>Category</th>
    <th>Description</th>
    <th>Photo</th>
    <th>Upload</th>
</tr>

<?php foreach($products as $p): ?>
<tr>
    <td><?= encode($p->Product_id) ?></td>
    <td><?= encode($p->Product_model) ?></td>
    <td><?= number_format($p->Product_price, 2) ?></td>
    <td><?= encode($_categories[$p->Category_id] ?? $p->Category_id) ?></td>
    <td><?= encode($p->Category_name) ?></td>

    <td>
        <?php if (!empty($p->product_photo)): ?>
            <img src="../uploads/<?= encode($p->product_photo) ?>" width="60">
        <?php else: ?>
            No Image
        <?php endif; ?>
    </td>

    <td>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= $p->Product_id ?>">
            <input type="file" name="photo" accept="image/*">
            <button class="btn btn-upload" type="submit" name="upload">Upload</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>

</table>
</div>

</div>

<?php include '../lib/_foot.php'; ?>
