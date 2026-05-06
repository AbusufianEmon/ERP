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

    $from_branch_id = (int)$_POST['from_branch']; // destination branch
    $to_branch_id   = (int)$_POST['to_branch'];   // source branch

    $pt_ids = $_POST['pt_id'];
    $qtys   = $_POST['qty'];

    mysqli_begin_transaction($con);

    try {

        foreach ($pt_ids as $key => $pt_id) {

            $pt_id = (int)$pt_ids[$key];
            $qty   = (int)$qtys[$key];

            // Get product_transfer info
            $pt_res = mysqli_query($con, "SELECT * FROM product_transfer WHERE id='$pt_id' LIMIT 1");
            $pt_row = mysqli_fetch_assoc($pt_res);

            $product_id = $pt_row['product_id'];
            $lot_no     = $pt_row['lot_no'];

            // ---------------------------
            // Fetch source branch stock
            // ---------------------------
            $src_res = mysqli_query($con, "
                SELECT * FROM product_stock
                WHERE product_id = $product_id
                  AND lot_no = '$lot_no'
                  AND branch_id = $to_branch_id
                LIMIT 1
            ");

            if (mysqli_num_rows($src_res) === 0) {
                throw new Exception("Product not found in source branch");
            }

            $src_row = mysqli_fetch_assoc($src_res);
            $src_stock_id   = $src_row['stock_id'];     // keep FK intact
            $supplier_id    = $src_row['supplier_id'];
            $cat_id         = $src_row['cat_id'];
            $buy_price      = $src_row['buy_price'];
            $sell_price     = $src_row['sell_price'];

            if ($src_row['qty'] < $qty) {
                throw new Exception("Insufficient stock in source branch for product ID $product_id");
            }

            // Deduct stock from source branch
            mysqli_query($con, "
                UPDATE product_stock
                SET qty = qty - $qty
                WHERE product_id = $product_id
                  AND lot_no = '$lot_no'
                  AND branch_id = $to_branch_id
            ");

            // ---------------------------
            // Add stock to destination branch
            // ---------------------------
            $dest_res = mysqli_query($con, "
                SELECT * FROM product_stock
                WHERE product_id = $product_id
                  AND lot_no = '$lot_no'
                  AND branch_id = $from_branch_id
                LIMIT 1
            ");

            if (mysqli_num_rows($dest_res) > 0) {
                // Update existing stock
                $dest_row = mysqli_fetch_assoc($dest_res);
                $dest_stock_id = $dest_row['product_stock_id'];

                mysqli_query($con, "
                    UPDATE product_stock
                    SET qty = qty + $qty
                    WHERE product_stock_id = $dest_stock_id
                ");
            } else {
                // Insert new stock row for destination branch
                mysqli_query($con, "
                    INSERT INTO product_stock
                    (stock_id, product_id, supplier_id, cat_id, branch_id, qty, lot_no, buy_price, sell_price, created_at)
                    VALUES
                    ($src_stock_id, $product_id, $supplier_id, $cat_id, $from_branch_id, $qty, '$lot_no', $buy_price, $sell_price, NOW())
                ");
            }

            // Update product_transfer qty
            mysqli_query($con, "
                UPDATE product_transfer
                SET qty='$qty'
                WHERE id='$pt_id'
            ");
        }

        // Mark transfer as completed
        mysqli_query($con, "
            UPDATE product_transfer 
            SET transfer_status = 2 
            WHERE transfer_id = '$transfer_id'
        ");

        mysqli_commit($con);

        // Success alert
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Transfer Completed Successfully',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location='view_stock.php?branch_id=$branch_id';
            });
        </script>";

        include('ini/footer.php');
        exit;

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '".addslashes($e->getMessage())."'
            }).then(() => { window.history.back(); });
        </script>";
        include('ini/footer.php');
        exit;
    }
}

/* ===============================
   Fetch Header Info
================================ */
$info_sql = "
    SELECT 
        pt.transfer_id,
        pt.created_at,
        fb.branch_id AS from_branch_id,
        tb.branch_id AS to_branch_id,
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
   Fetch Items + Available Stock (from_branch)
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
       AND ps.branch_id = '{$info['from_branch_id']}'  
    WHERE pt.transfer_id = '$transfer_id'
";
$item_res = mysqli_query($con, $item_sql);
?>

<div class="container-fluid">

<div class="card shadow mb-4">
<div class="card-header bg-primary text-white">
    <h5 class="m-0">Branch Product Transfer</h5>
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

<input type="hidden" name="from_branch" value="<?= $info['from_branch_id']; ?>">
<input type="hidden" name="to_branch" value="<?= $info['to_branch_id']; ?>">

<div class="table-responsive">
<table class="table table-bordered text-center">
<thead class="thead-dark">
<tr>
    <th>#</th>
    <th>Product</th>
    <th>Code</th>
    <th>Lot No</th>
    <th>Qty</th>
    <th>Available Qty (From Branch)</th>
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
        <input type="number" name="qty[]" value="<?= $row['qty']; ?>" class="form-control text-center" readonly>
    </td>

    <td>
        <span class="badge badge-success"><?= $row['available_qty']; ?></span>
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
        <i class="fa fa-check"></i> Receive
    </button>
    <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
</div>

</form>

</div>
</div>

</div>

<?php include('ini/footer.php'); ?>
