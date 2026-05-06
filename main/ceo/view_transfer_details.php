<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['transfer_id'])) {
    echo "<div class='alert alert-danger'>Transfer ID missing.</div>";
    exit;
}

$transfer_id = mysqli_real_escape_string($con, $_GET['transfer_id']);

/* We fetch all products under this transfer_id. 
   We also join the branches table twice to get both branch names.
*/
$sql = "
    SELECT 
        pt.*, 
        p.name AS product_name, 
        p.code AS product_code,
        b1.branch_name AS original_from_name, 
        b2.branch_name AS original_to_name
    FROM product_transfer pt
    LEFT JOIN product p ON pt.product_id = p.id
    LEFT JOIN branches b1 ON pt.from_branch = b1.branch_id
    LEFT JOIN branches b2 ON pt.to_branch = b2.branch_id
    WHERE pt.transfer_id = '$transfer_id'
";

$result = mysqli_query($con, $sql);

// Fetch first row to get general transfer info (Status, Branch Names)
$first_row = mysqli_fetch_assoc($result);

if (!$first_row) {
    echo "<div class='alert alert-danger'>Transfer record not found.</div>";
    exit;
}

// --- BRANCH SWAP LOGIC ---
// If status is 1, swap the display names
if ($first_row['branch_to_branch_status'] == 1) {
    $display_from = $first_row['original_to_name'];
    $display_to   = $first_row['original_from_name'];
} else {
    $display_from = $first_row['original_from_name'];
    $display_to   = $first_row['original_to_name'];
}

// Logic for Transfer Status
$t_status = $first_row['transfer_status'];
$transfer_label = match($t_status) {
    '0' => '<span class="badge badge-secondary">Requested</span>',
    '1' => '<span class="badge badge-primary">Shipped</span>',
    '2' => '<span class="badge badge-success">Received</span>',
    default => '<span class="badge badge-dark">Unknown</span>',
};

// Logic for Stock Manager Approval Status
$m_status = $first_row['stock_manager_approval_status'];
$manager_label = match($m_status) {
    '0' => '<span class="badge badge-warning">Pending Approval</span>',
    '1' => '<span class="badge badge-success">Approved</span>',
    default => '<span class="badge badge-danger">Rejected</span>',
};

// Reset result pointer to the beginning so the loop catches the first product too
mysqli_data_seek($result, 0);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Transfer Invoice: <?php echo $transfer_id; ?></h1>
        <a href="javascript:history.back()" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Status Overview</div>
                    <div class="mb-0 font-weight-bold text-gray-800">
                        Transfer: <?php echo $transfer_label; ?><br>
                        Approval: <?php echo $manager_label; ?>
                    </div>
                    <hr>
                    <small>Date: <?php echo date('d-M-Y', strtotime($first_row['created_at'])); ?></small>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-5 text-center">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Dispatching Branch</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $display_from; ?></div>
                        </div>
                        <div class="col-2 text-center">
                            <i class="fas fa-long-arrow-alt-right fa-2x text-gray-300"></i>
                        </div>
                        <div class="col-5 text-center">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Receiving Branch</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $display_to; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-dark d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-white">Transferred Products List</h6>
            <button onclick="window.print()" class="btn btn-sm btn-light"><i class="fas fa-print"></i> Print</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Lot No</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $grand_total = 0;
                        while ($row = mysqli_fetch_assoc($result)) { 
                            $subtotal = $row['buy_price'] * $row['qty'];
                            $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><span class="badge badge-secondary"><?php echo $row['product_code']; ?></span></td>
                            <td class="text-left"><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><span class="text-danger font-weight-bold"><?php echo $row['lot_no']; ?></span></td>
                            <td><?php echo $row['qty']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                   
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>