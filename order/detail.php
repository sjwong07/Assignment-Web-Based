<?php
include '../lib/_base.php';

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
    SELECT i.*, p.Product_model as name, p.Product_id as product_id,
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
</style>

<p><?= count($arr) ?> item(s)</p>

<table class="table">
    <tr>
        <th>Product Id</th>
        <th>Product Name</th>
        <th>Price (RM)</th>
        <th>Unit</th>
        <th>Subtotal (RM)</th>
    </tr>

    <?php foreach ($arr as $i): ?>
    <tr>
        <td><?= $i->product_id ?></td>
        <td><?= $i->name ?></td>
        <td class="right"><?= number_format($i->price, 2) ?></td>
        <td class="right"><?= $i->unit ?></td>
        <td class="right">
            <?= $i->subtotal ?>
        </td>
    </tr>
    <?php endforeach ?>

    <tr>
        <th colspan="3"></th>
        <th class="right"><?= $total_count ?></th>
        <th class="right"><?= number_format($total_amount, 2) ?></th>
    </tr>
</table>

<div style="margin-top: 20px;">
    <button type="button" onclick="location.href='/order/history.php'">Back</button>
</div>

<?php
include '../lib/_foot.php';