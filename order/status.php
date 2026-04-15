<?php
include '../lib/_base.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the current user
$stm = $_db->prepare('SELECT * FROM `order` WHERE user_id = ? ORDER BY id DESC');
$stm->execute([$user_id]);
$arr = $stm->fetchAll(PDO::FETCH_OBJ);

$_title = 'Order Status 🚚';
include '../lib/_head.php';
?>

<style>
    .table th, .table td { border: 2px solid #333; padding: 12px; }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.85em;
    }
    .status-pending { background-color: #ffeeba; color: #856404; } 
    .status-shipping { background-color: #b8daff; color: #004085; }  
    .status-arrived { background-color: #c3e6cb; color: #155724; }   
    .status-out-of-stock { background-color: #f8d7da; color: #721c24; } 

    .alert-text { color: red; font-weight: bold; font-size: 0.8em; display: block; margin-top: 5px; }
</style>

<p><?= count($arr) ?> record(s) found.</p>

<table class="table">
    <tr>
        <th>Id</th>
        <th>Datetime</th>
        <th>Detail</th> <th>Status</th>
    </tr>

    <?php foreach ($arr as $o): ?>
    <?php 
        $status_class = 'status-pending';
        $current_status = $o->status ?: 'Pending';

        if ($current_status == 'Shipping') $status_class = 'status-shipping';
        if ($current_status == 'Arrived')  $status_class = 'status-arrived';
        if ($current_status == 'Out of Stock') $status_class = 'status-out-of-stock';
    ?>
    <tr>
        <td style="text-align: center;"><?= $o->id ?></td>
        <td><?= $o->order_date ?></td>
        <td style="text-align: center;">
            <button type="button" onclick="location='detail.php?id=<?= $o->id ?>&from=status'">View Items</button>
        </td>
        <td style="text-align: center;">
            <span class="status-badge <?= $status_class ?>">
                <?= htmlspecialchars($current_status) ?>
            </span>
            
            <?php if ($current_status == 'Out of Stock'): ?>
                <span class="alert-text">Please contact us for refund</span> //orderListing for admin not done yet
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php include '../lib/_foot.php'; ?>