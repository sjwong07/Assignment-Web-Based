<?php
include '../lib/_base.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stm = $_db->prepare('
    SELECT * FROM `order`
    WHERE user_id = ?
    ORDER BY id DESC
');
$stm->execute([$user_id]);
$arr = $stm->fetchAll(PDO::FETCH_OBJ);

$_title = 'Order History 🕓';
include '../lib/_head.php';
?>

<style>
    .table th, .table td{
        border: 2px solid #333;
    }
    th, td{
        padding: 12px;
    }
</style>

<p><?= count($arr) ?> record(s)</p>

<table class="table">
    <tr>
        <th>Id</th>
        <th>Datetime</th>
        <th>Total (RM)</th>
        <th>Detail</th>
    </tr>

    <?php foreach ($arr as $o): ?>
    <tr>
        <td style= "text-align: center;"><?= $o->id ?></td>
        <td><?= $o->order_date ?></td>
        <td class="right" style= "text-align: center;"><?= number_format($o->total, 2) ?></td>
        <td style= "text-align: center;">
            <button type="button" onclick="location='detail.php?id=<?= $o->id ?>&from=history'">Detail</button>
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php
include '../lib/_foot.php';