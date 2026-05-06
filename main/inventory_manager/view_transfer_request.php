<?php
include('ini/header.php');
include('dbcon.php');

// 1. Get Transfer ID
if (!isset($_GET['transfer_id'])) {
    echo "<script>window.location='transfer_approval_pending.php';</script>";
    exit;
}

$transfer_id = mysqli_real_escape_string($con, $_GET['transfer_id']);

/* =============================================
   2. HANDLE APPROVE ACTION
============================================= */
if (isset($_POST['approve_transfer'])) {
    $update_sql = "UPDATE product_transfer SET stock_manager_approval_status = 1 WHERE transfer_id = '$transfer_id'";
    if (mysqli_query($con, $update_sql)) {
        echo "<script>
            swal('Approved!', 'Transfer request has been approved successfully.', 'success').then(() => {
                window.location='transfer_approval_pending.php';
            });
        </script>";
    }
}

/* =============================================
   3. HANDLE DECLINE ACTION (Delete)
============================================= */
if (isset($_POST['decline_transfer'])) {
    $delete_sql = "DELETE FROM product_transfer WHERE transfer_id = '$transfer_id'";
    if (mysqli_query($con, $delete_sql)) {
        echo "<script>
            swal('Declined!', 'Transfer request has been deleted.', 'error').then(() => {
                window.location='transfer_approval_pending.php';
            });
        </script>";
    }
}

/* =============================================
   4. FETCH ITEMS FOR DISPLAY
============================================= */
$query = "
SELECT pt.*, p.name, p.code, b1.branch_name as from_b, b2.branch_name as to_b
FROM product_transfer pt
LEFT JOIN product p ON pt.product_id = p.id
LEFT JOIN branches b1 ON pt.from_branch = b1.branch_id
LEFT JOIN branches b2 ON pt.to_branch = b2.branch_id
WHERE pt.transfer_id = '$transfer_id'";

$res = mysqli_query($con, $query);
$items = [];
while($row = mysqli_fetch_assoc($res)){
    $items[] = $row;
}

if(empty($items)){
    echo "<script>window.location='transfer_approval_pending.php';</script>";
    exit;
}

$first_item = $items[0];
?>

<style>
/* CSS to hide elements during printing */
@media print {
    #accordionSidebar, .navbar, .card-header button, .btn, hr, footer, #sidebarToggleTop {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .container-fluid {
        width: 100%;
        padding: 0;
        margin: 0;
    }
    body {
        background-color: white !important;
    }
}
</style>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4" id="invoice-print">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary">Transfer Invoice: #<?php echo $transfer_id; ?></h6>
                    <button class="btn btn-sm btn-primary shadow-sm" onclick="window.print()">
                        <i class="fas fa-print fa-sm text-white-50"></i> Print Invoice
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-5">
                        <div class="col-sm-6">
                            <h5 class="mb-3 font-weight-bold">Source</h5>
                            <div><strong>From:</strong> <?php echo $first_item['from_b']; ?></div>
                            <div class="text-muted small">Date Requested: <?php echo date('d M Y, h:i A', strtotime($first_item['created_at'])); ?></div>
                        </div>
                        <div class="col-sm-6 text-sm-right">
                            <h5 class="mb-3 font-weight-bold">Destination</h5>
                            <div><strong>To:</strong> <?php echo $first_item['to_b']; ?></div>
                            <div class="mt-2"><span class="badge badge-warning">Status: Pending Approval</span></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Product Name & Code</th>
                                    <th>Lot No</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-center">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grand_total = 0;
                                foreach($items as $index => $item) { 
                                    $subtotal = $item['qty'] * $item['sell_price'];
                                    $grand_total += $subtotal;
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['code']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['lot_no']); ?></td>
                                    <td class="text-right"><?php echo number_format($item['sell_price'], 2); ?></td>
                                    <td class="text-center"><?php echo $item['qty']; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                           
                        </table>
                    </div>

                    <div class="row mt-5 d-none d-print-flex">
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid black;">
                            <p>Prepared By</p>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid black;">
                            <p>Authorized Signature</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 no-print">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to decline and delete this entire transfer request?');">
                            <button type="submit" name="decline_transfer" class="btn btn-danger mr-2">
                                <i class="fas fa-times"></i> Decline & Delete
                            </button>
                        </form>

                        <form method="POST">
                            <button type="submit" name="approve_transfer" class="btn btn-success">
                                <i class="fas fa-check"></i> Approve Transfer
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>