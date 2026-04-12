<?php
include '../lib/_base.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    header("Location: ../login.php");
    exit;
}

$custoemr_id = $_SESSION['customer_id'];

$stm = $_db->prepare('
    SELECT * FROM `order`
    WHERE customer_id = ?
    ORDER BY id DESC
');
$stm->execute([$customer_id]);
$arr = $stm->fetchAll(PDO::FETCH_OBJ);

$_title = 'Order History 🕓';
include '../lib/_head.php';
?>


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
        <td><?= $o->id ?></td>
        <td><?= $o->order_date ?></td>
        <td class="right"><?= number_format($o->total, 2) ?></td>
        <td>
            <button data-get="detail.php?id=<?= $o->id ?>">View</button>
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php
include '../lib/_foot.php';