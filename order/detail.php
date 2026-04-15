<?php
include '../lib/_base.php';

$from = req('from'); 
$id = req('id');

if (!isset($_SESSION['user_id']) && !isset($_user->user_id)) {
    redirect('history.php');
}

$user_id = $_SESSION['user_id'] ?? $_user->user_id;

$stm = $_db->prepare('
    SELECT * FROM `order`
    WHERE id = ? AND user_id = ?
');
$stm->execute([$id, $user_id]);
$o = $stm->fetch();

if (!$o) redirect('history.php');

$stm = $_db->prepare('
    SELECT i.*, p.Product_model as name, p.Product_id as product_id, p.Product_photo as photo,
    (i.price * i.unit) as subtotal
    FROM item AS i
    JOIN Product AS p ON i.Product_id = p.Product_id
    WHERE i.order_id = ?
');
$stm->execute([$id]);
$arr = $stm->fetchAll();

$total_count = 0;
$total_amount = 0;
foreach ($arr as $i) {
    $total_count += $i->unit;
    $total_amount += ($i->price * $i->unit);
}

$_title = 'Order Detail';
include '../lib/_head.php';
?>

<style>
    .popup {
        width: 100px;
        height: 100px;
    }

    .table th, .table td{
        border: 2px solid #333;
    }

    th, td{
        padding: 12px;
    }
</style>

<p><?= count($arr) ?> item(s)</p>

<table class="table">
    <tr>
        <th>Product Id</th>
        <th>Photo</th>
        <th>Product Name</th>
        <th>Price (RM)</th>
        <th>Unit</th>
        <th>Subtotal (RM)</th>
    </tr>

    <?php foreach ($arr as $i): ?>
    <tr>
        <td style= "text-align: center;"><?= $i->product_id ?></td>

        <td style="text-align: center;">
            <?php if (!empty($i->photo)): ?>
                <img src="../images/<?= $i->photo ?>" alt="<?= $i->name ?>" style="max-width: 80px; max-height: 80px;">
            <?php else: ?>
                <img src="../images/no-image.png" alt="No Image" style="max-width: 80px; max-height: 80px;">
            <?php endif; ?>
        </td>

        <td><?= $i->name ?></td>
        <td class="right" style= "text-align: center;"><?= number_format($i->price, 2) ?></td>
        <td class="right" style= "text-align: center;"><?= $i->unit ?></td>
        <td class="right" style= "text-align: center;">
            <?= $i->subtotal ?>
        </td>
    </tr>
    <?php endforeach ?>

    <tr>
        <th colspan="4"></th>
        <th class="right"><?= $total_count ?></th>
        <th class="right"><?= number_format($total_amount, 2) ?></th>
    </tr>
</table>

<div style="margin-top: 20px;">
    <?php
        $back_url = 'history.php';
        if ($from == 'status')  $back_url = 'status.php';
        if ($from == 'listing') $back_url = 'listing.php';
    ?>
    <button type="button" onclick="location='<?= $back_url ?>'">Back</button>
</div>

<?php
include '../lib/_foot.php';