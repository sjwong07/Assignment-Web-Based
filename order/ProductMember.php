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

$_title = 'Product Listing';
include '../lib/_head.php';
?>

<style>
body { font-family: Arial; }
.container { width: 90%; margin: auto; }
.filter-box { background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.table { width: 100%; border-collapse: collapse; }
th, td{border: 2px solid #333;}
.table th { background: #aac4fb; color: black; }
.table th, .table td { padding: 10px; text-align: center; }
.btn { padding: 6px 10px; background: #aac4fb; color: white; border: none; cursor: pointer; }
.btn:hover { background: #3772f3; }
#tempMsg { background: #28a745; color: white; padding: 10px; margin-bottom: 10px; }
</style>

<div class="container">

<?php if ($msg = temp('info')): ?>
    <div id="tempMsg"><?= $msg ?></div>
    <script>
        setTimeout(() => {
            let el = document.getElementById('tempMsg');
            if (el) el.style.display = 'none';
        }, 2000);
    </script>
<?php endif; ?>

<h2>Product List</h2>

<?php
//FILTER 
$category       = get('category');
$min_price      = get('min_price');
$max_price      = get('max_price');
$category_desc  = get('category_desc');

// ================= LOAD CATEGORY LIST =================
$category_sql = "SELECT Category_id, Category_name FROM Category";
$category_stmt = $_db->query($category_sql);
$category_list = $category_stmt->fetchAll();

// Build simple category array
$_categories = [];
foreach ($category_list as $c) {
    $_categories[$c->Category_id] = $c->Category_name;
}

//PRODUCT QUERY
$sql = "SELECT p.Product_id, p.Product_model, p.Product_price, 
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

<!-- ================= FILTER FORM ================= -->
<div class="filter-box">
<form method="GET">

    <label>Category:</label>
    <select name="category">
        <option value="">All</option>
        <?php foreach ($_categories as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($category == $id ? 'selected' : '') ?>>
                <?= $name ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Min Price:</label>
    <input type="number" name="min_price" value="<?= encode($min_price) ?>">

    <label>Max Price:</label>
    <input type="number" name="max_price" value="<?= encode($max_price) ?>">

    

    <button class="btn">Filter</button>
</form>
</div>

<!-- ================= PRODUCT TABLE ================= -->
<table class="table" border="1">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price</th>
        <th>Category</th>
        <th>Photo</th>
        <th>Add To Cart</th>
    </tr>

    <?php foreach($products as $p): 
        $cart = get_cart();    
        $current_unit = $cart[$p->Product_id] ?? 0;
        $GLOBALS['unit'] = $current_unit;
    ?>
    <tr>
        <td><?= encode($p->Product_id) ?></td>
        <td><?= encode($p->Product_model) ?></td>
        <td><?= number_format($p->Product_price, 2) ?></td>
        <td><?= encode($p->Category_name) ?></td>
        <td>
    <?php if (!empty($p->product_photo)): ?>
        <img src="../images/<?= encode($p->product_photo) ?>" 
             alt="<?= encode($p->Product_model) ?>" 
             style="max-width: 100px; max-height: 100px;">
    <?php else: ?>
        <img src="../images/no-image.png" 
             alt="No Image" 
             style="max-width: 100px; max-height: 100px;">
    <?php endif; ?>
</td>
        <td>
            <form method="post">
                <input type="hidden" name="id" value="<?= $p->Product_id ?>">
                <?= html_select('unit', $_units, 'Select Unit') ?>
                <button class="btn" style= "background: #aac4fb; color: white;">Add</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<?php include '../lib/_foot.php'; ?>
