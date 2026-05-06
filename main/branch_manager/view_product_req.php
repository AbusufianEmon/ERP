<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['transfer_id'])) {
    echo "<div class='alert alert-danger'>Invalid Transfer</div>";
    exit;
}

$transfer_id = mysqli_real_escape_string($con, $_GET['transfer_id']);

if (!isset($_GET['branch_id'])) {
    echo "<div class='alert alert-danger'>Branch not provided</div>";
    exit;
}

$branch_id = intval($_GET['branch_id']);

/* ===============================
   Handle Transfer Submit
================================ */
if (isset($_POST['transfer_now'])) {

    $qtys      = $_POST['qty'];
    $available = $_POST['available'];
    $pt_ids    = $_POST['pt_id'];

    /* ===============================
       VALIDATE FIRST
    ================================ */
    foreach ($qtys as $key => $qty) {

        $qty = (int)$qty;
        $av  = (int)$available[$key];

        if ($qty > $av) {
            echo "<script>
                alert('Stock short! One or more products exceed available quantity.');
                window.history.back();
            </script>";
            include('ini/footer.php');
            exit;
        }
    }

    /* ===============================
       UPDATE AFTER VALIDATION
    ================================ */
    foreach ($qtys as $key => $qty) {

        $qty   = (int)$qty;
        $pt_id = (int)$pt_ids[$key];

        mysqli_query($con, "
            UPDATE product_transfer 
            SET qty = '$qty'
            WHERE id = '$pt_id'
            LIMIT 1
        ");
    }

    mysqli_query($con, "
        UPDATE product_transfer 
        SET transfer_status = 1 
        WHERE transfer_id = '$transfer_id'
    ");

    echo "<script>
        alert('Transferred Successfully');
        window.location='from_branch_product_request.php?branch_id=$branch_id';
    </script>";
}

/* ===============================
   Fetch Header Info
================================ */
$info_sql = "
    SELECT 
        pt.transfer_id,
        pt.created_at,
        fb.branch_name AS from_branch,
        tb.branch_name AS to_branch_name
    FROM product_transfer pt
    LEFT JOIN branches fb ON pt.from_branch = fb.branch_id
    LEFT JOIN branches tb ON pt.to_branch = tb.branch_id
    WHERE pt.transfer_id = '$transfer_id'
    LIMIT 1
";
$info_res = mysqli_query($con, $info_sql);
$info = mysqli_fetch_assoc($info_res);

/* ===============================
   Fetch Items + Available Stock
================================ */
$item_sql = "
    SELECT 
        pt.*,
        p.name AS product_name,
        p.code,
        IFNULL(ps.qty,0) AS available_qty
    FROM product_transfer pt
    LEFT JOIN product p ON pt.product_id = p.id
    LEFT JOIN product_stock ps 
        ON ps.product_id = pt.product_id
       AND ps.lot_no = pt.lot_no
       AND ps.branch_id = '$branch_id'
    WHERE pt.transfer_id = '$transfer_id'
";
$item_res = mysqli_query($con, $item_sql);
?>

<div class="container-fluid">

<div class="card shadow mb-4">
<div class="card-header bg-primary text-white">
    <h5 class="m-0">Branch Product Request Invoice</h5>
</div>

<div class="card-body">

<div class="row mb-4">
    <div class="col-md-6">
        <strong>Transfer ID:</strong> <?= $info['transfer_id']; ?><br>
        <strong>Date:</strong> <?= $info['created_at']; ?>
    </div>
    <div class="col-md-6 text-right">
        <strong>From Branch:</strong> <?= htmlspecialchars($info['from_branch']); ?><br>
        <strong>To Branch:</strong> <?= htmlspecialchars($info['to_branch_name']); ?>
    </div>
</div>

<hr>

<form method="post">

<div class="table-responsive">
<table class="table table-bordered text-center">
<thead class="thead-dark">
<tr>
    <th>#</th>
    <th>Product</th>
    <th>Code</th>
    <th>Lot No</th>
    <th>Qty</th>
    <th>Available Qty</th>
    <th>Buy Price</th>
    <th>Sell Price</th>
</tr>
</thead>

<tbody>
<?php $i = 1; while ($row = mysqli_fetch_assoc($item_res)) { ?>
<tr>
    <td><?= $i++; ?></td>
    <td><?= htmlspecialchars($row['product_name']); ?></td>
    <td><?= $row['code']; ?></td>
    <td><?= $row['lot_no']; ?></td>

    <td>
        <input type="hidden" name="pt_id[]" value="<?= $row['id']; ?>">

        <input type="number" 
               name="qty[]" 
               value="<?= $row['qty']; ?>" 
               min="1"
               class="form-control text-center" 
               required>
    </td>

    <td>
        <span class="badge badge-success"><?= $row['available_qty']; ?></span>
        <input type="hidden" name="available[]" value="<?= $row['available_qty']; ?>">
    </td>

    <td><?= number_format($row['buy_price'], 2); ?></td>
    <td><?= number_format($row['sell_price'], 2); ?></td>
</tr>
<?php } ?>
</tbody>

</table>
</div>

<div class="text-right mt-3">
    <button type="submit" name="transfer_now" class="btn btn-primary">
        <i class="fa fa-check"></i> Transfer
    </button>

    <a href="javascript:history.back()" class="btn btn-secondary">
        Back
    </a>
</div>

</form>

</div>
</div>

</div>

<?php include('ini/footer.php'); ?>
