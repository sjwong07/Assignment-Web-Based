<?php
require '../lib/_base.php';


//cart
if(is_post() && isset($_POST['id'])){
    $id = post('id');
    $unit = post('unit');

    if($unit > 0){
        update_cart($id, $unit);

        temp('info', 'Add Successfully !');
        redirect();
    }
}
$_title = 'Product Listing';
include '../lib/_head.php';
?>

<?php if($msg = temp('info')): ?>
    <div id="tempMsg" >
        <?= $msg ?>
    </div>
    <script>
        setTimeout(() => {
            let el = document.getElementById('tempMsg');
            if(el) el.style.display = 'none';
        }, 2000);
    </script>
<?php endif; ?>
<p>Here is Our Product List</p>
<?php

// 1. Get filters from GET
$category  = get('category', null);
$min_price = get('min_price', null);
$max_price = get('max_price', null);

// 2. Query products with filters
$product = "SELECT Product_id, Product_model, Product_price, Category_id
        FROM Product
        WHERE 1=1
          AND (:category IS NULL OR Category_id = :category)
          AND (:min_price IS NULL OR Product_price >= :min_price)
          AND (:max_price IS NULL OR Product_price <= :max_price)
        ORDER BY Product_model";

$stm = $_db->prepare($product);
$stm->execute([
    ':category'  => $category ?: null,
    ':min_price' => $min_price ?: null,
    ':max_price' => $max_price ?: null
]);

$products = $stm->fetchAll();
?>

<form method="POST" action="">
    <label>Category:</label>
    <select name="category">
        <option value="">All</option>
        <?php foreach($_categories as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($category == $id ? 'selected' : '') ?>><?= $name ?></option>
        <?php endforeach; ?>

    </select>
    <label>Min Price:</label>
    <input type="number" name="min_price" value="<?= encode($min_price) ?>">
    <label>Max Price:</label>
    <input type="number" name="max_price" value="<?= encode($max_price) ?>">
    <button type="submit">Filter</button>
</form>

<!-- 4. Product Table -->
 <div class="ProductMember">
<table class="table" border="1" cellpadding="5">
    <tr>
        <th>Product_ID</th>
        <th>Product_Name</th>
        <th>Product_Price</th>
        <th>Product_Category</th>
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
        <td><?= encode($_categories[$p->Category_id] ?? $p->Category_id) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="id" value="<?= $p->Product_id ?>">
                <?= html_select('unit', $_units, 'Select Unit') ?>
                <button type="submit">Add To Cart</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
    </div>


<?php
include '../lib/_foot.php';