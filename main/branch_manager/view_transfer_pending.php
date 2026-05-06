<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['transfer_id'])) {
    echo "<div class='alert alert-danger'>Invalid Transfer ID</div>";
    include('ini/footer.php');
    exit;
}

$transfer_id = mysqli_real_escape_string($con, $_GET['transfer_id']);
$branch_id = isset($_GET['branch_id']) ? mysqli_real_escape_string($con, $_GET['branch_id']) : '';

/* ===============================
   Handle Transfer Submit (Receive)
================================ */
if (isset($_POST['transfer_now'])) {
    $from_branch_id = (int)$_POST['from_branch']; // Destination
    $to_branch_id   = (int)$_POST['to_branch'];   // Source

    $pt_ids = $_POST['pt_id'];
    $qtys   = $_POST['qty'];

    mysqli_begin_transaction($con);

    try {
        foreach ($pt_ids as $key => $pt_id) {
            $pt_id = (int)$pt_id;
            $qty   = (int)$qtys[$key];

            $pt_res = mysqli_query($con, "SELECT * FROM product_transfer WHERE id='$pt_id' LIMIT 1");
            $pt_row = mysqli_fetch_assoc($pt_res);

            $product_id = $pt_row['product_id'];
            $lot_no     = $pt_row['lot_no'];

            // 1. Check Source Stock (The branch sending the item)
            $src_res = mysqli_query($con, "
                SELECT * FROM product_stock 
                WHERE product_id = $product_id AND lot_no = '$lot_no' AND branch_id = $to_branch_id 
                LIMIT 1
            ");

            if (mysqli_num_rows($src_res) === 0) {
                throw new Exception("Product stock not found in source branch.");
            }

            $src_row = mysqli_fetch_assoc($src_res);
            $src_stock_id   = $src_row['stock_id'];
            $supplier_id    = $src_row['supplier_id'];
            $cat_id         = $src_row['cat_id'];
            $buy_price      = $src_row['buy_price'];
            $sell_price     = $src_row['sell_price'];

            if ($src_row['qty'] < $qty) {
                throw new Exception("Insufficient stock in source branch for ". $pt_row['product_id']);
            }

            // 2. Deduct from Source
            mysqli_query($con, "UPDATE product_stock SET qty = qty - $qty WHERE product_id = $product_id AND lot_no = '$lot_no' AND branch_id = $to_branch_id");

            // 3. Add to Destination (The branch receiving)
            $dest_res = mysqli_query($con, "SELECT * FROM product_stock WHERE product_id = $product_id AND lot_no = '$lot_no' AND branch_id = $from_branch_id LIMIT 1");

            if (mysqli_num_rows($dest_res) > 0) {
                mysqli_query($con, "UPDATE product_stock SET qty = qty + $qty WHERE product_id = $product_id AND lot_no = '$lot_no' AND branch_id = $from_branch_id");
            } else {
                mysqli_query($con, "INSERT INTO product_stock (stock_id, product_id, supplier_id, cat_id, branch_id, qty, lot_no, buy_price, sell_price, created_at) 
                                    VALUES ($src_stock_id, $product_id, $supplier_id, $cat_id, $from_branch_id, $qty, '$lot_no', $buy_price, $sell_price, NOW())");
            }

            // Update transfer item record
            mysqli_query($con, "UPDATE product_transfer SET qty='$qty' WHERE id='$pt_id'");
        }

        // Finalize Transfer Status to 'Received'
        mysqli_query($con, "UPDATE product_transfer SET transfer_status = 2 WHERE transfer_id = '$transfer_id'");

        mysqli_commit($con);

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({ icon: 'success', title: 'Received!', text: 'Stock updated in destination branch', timer: 2000, showConfirmButton: false })
            .then(() => { window.location='view_stock.php?branch_id=$branch_id'; });
        </script>";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({ icon: 'error', title: 'Error', text: '".addslashes($e->getMessage())."' });
        </script>";
    }
}

/* ===============================
   Fetch Info Header
================================ */
$info_sql = "
    SELECT pt.transfer_id, pt.created_at, fb.branch_id AS from_branch_id, tb.branch_id AS to_branch_id,
           fb.branch_name AS from_branch_name, tb.branch_name AS to_branch_name
    FROM product_transfer pt
    LEFT JOIN branches fb ON pt.from_branch = fb.branch_id
    LEFT JOIN branches tb ON pt.to_branch = tb.branch_id
    WHERE pt.transfer_id = '$transfer_id' LIMIT 1
";
$info_res = mysqli_query($con, $info_sql);
$info = mysqli_fetch_assoc($info_res);

/* ===============================
   Fetch Items + Destination Qty
================================ */
// Notice the join on ps.branch_id = pt.from_branch (The receiving branch)
$item_sql = "
    SELECT pt.*, p.name AS product_name, p.code, IFNULL(ps.qty, 0) AS destination_qty
    FROM product_transfer pt
    LEFT JOIN product p ON pt.product_id = p.id
    LEFT JOIN product_stock ps ON ps.product_id = pt.product_id 
        AND ps.lot_no = pt.lot_no 
        AND ps.branch_id = pt.from_branch
    WHERE pt.transfer_id = '$transfer_id'
";
$item_res = mysqli_query($con, $item_sql);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0">Process Stock Receiving</h5>
            <span class="badge badge-light">Transfer ID: <?= $info['transfer_id']; ?></span>
        </div>

        <div class="card-body">
            <div class="row mb-3 p-2 bg-light border rounded">
                <div class="col-md-6 border-right">
                    <p class="mb-1 text-muted">Shipping From:</p>
                    <h6 class="text-danger font-weight-bold"><?= htmlspecialchars($info['to_branch_name']); ?></h6>
                </div>
                <div class="col-md-6 text-right">
                    <p class="mb-1 text-muted">Receiving To:</p>
                    <h6 class="text-success font-weight-bold"><?= htmlspecialchars($info['from_branch_name']); ?></h6>
                </div>
            </div>

            <form method="post">
                <input type="hidden" name="from_branch" value="<?= $info['from_branch_id']; ?>">
                <input type="hidden" name="to_branch" value="<?= $info['to_branch_id']; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th>Lot No</th>
                                <th width="15%">Transfer Qty</th>
                                <th>Current Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1; 
                            while ($row = mysqli_fetch_assoc($item_res)) { 
                                $transfer_qty = (int)$row['qty'];
                                $dest_qty = (int)$row['destination_qty'];
                            ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td class="text-left">
                                    <strong><?= htmlspecialchars($row['product_name']); ?></strong><br>
                                    <small class="badge badge-light border"><?= $row['code']; ?></small>
                                </td>
                                <td><span class="text-primary font-weight-bold"><?= $row['lot_no']; ?></span></td>
                                
                                <td>
                                    <input type="hidden" name="pt_id[]" value="<?= $row['id']; ?>">
                                    <input type="number" 
                                           name="qty[]" 
                                           value="<?= $transfer_qty; ?>" 
                                           class="form-control form-control-sm text-center font-weight-bold" 
                                           style="background-color: #e3f2fd; border: 1px solid #2196f3;"
                                           readonly>
                                </td>

                                <td>
                                    <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 0.9rem;">
                                        <?= $dest_qty; ?>
                                    </span>
                                </td>

                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-4 border-top pt-3">
                    <a href="javascript:history.back()" class="btn btn-secondary px-4">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="transfer_now" class="btn btn-success px-5" onclick="return confirm('Do you want to add these items to <?= addslashes($info['from_branch_name']); ?> stock?')">
                        <i class="fa fa-download"></i> Receive Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>