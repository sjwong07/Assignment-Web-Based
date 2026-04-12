<?php
include '../lib/_base.php';

$cart = get_cart();

if (is_post()) {

    $btn = req('btn');
    if ($btn == 'clear'){
        set_cart();
        redirect('?');
    }

    $id   = req('id');
    $unit = req('unit');
    update_cart($id, $unit);
    redirect();
}

// ----------------------------------------------------------------------------

$_title = 'Shopping Cart 🛒';
include '../lib/_head.php';
?>

<style>
    .popup {
        width: 100px;
        height: 100px;
    }
</style>

<table class="table">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Price (RM)</th>
        <th>Unit</th>
        <th>Subtotal (RM)</th>
    </tr>

    <?php
        $count =  0;
        $total = 0;
        $item = [];

        $stm = $_db->prepare('SELECT * FROM Product WHERE Product_id = ?');
        $cart = get_cart();

        foreach($cart as $id => $unit):
            $stm->execute([$id]);
            $p = $stm->fetch();
            if (!$p) continue;

            $GLOBALS['unit'] = $unit;

            $subtotal = $p->Product_price * $unit;
            $count += $unit;
            $total += $subtotal;
    ?>
        <tr>
            <td><?= $p->Product_id ?></td>
            <td><?= $p->Product_model ?></td>
            <td class="right"><?= $p->Product_price ?></td>
            <td>
                <form method="post">
                    <input type = "hidden" name="id" value = "<?= $p->Product_id ?>">
                    <?= html_select('unit', $_units, '') ?>
                </form>            
            </td>
            <td class="right">
                <?= sprintf('%.2f', $subtotal) ?>
            </td>
        </tr>
    <?php endforeach ?>

    <tr>
        <th colspan="3"></th>
        <th class="right"><?= $count ?></th>
        <th class="right"><?= sprintf('%.2f', $total) ?></th>
    </tr>
</table>

<p>
    <?php if ($cart): ?>
        <div style="display: flex; gap: 15px; margin-top: 20px; align-items: center;">
            <form method="post" style="margin: 0;">
                <input type="hidden" name="btn" value="clear">
                <button type="submit">Clear All</button>
            </form>

            <?php if ($_user?->role == 'member'): ?>
                <button type="button" onclick="window.location.href='/order/checkout.php'">Checkout</button>
            <?php else: ?>
                <span>Please <a href="/login.php">login</a> as member to checkout</span>
            <?php endif ?>
        </div>
    <?php endif ?>
</p>

<script>
    $('select').on('change', e => e.target.form.submit());
</script>

<?php
include '../lib/_foot.php';