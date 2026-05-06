<?php
include('dbcon.php');
include('ini/header.php'); 

// Get the transfer_id (string) from the URL
$transfer_id = $_GET['transfer_id'];

// Fetch all products under this transfer_id
$query = "SELECT pt.*, 
          b1.branch_name as from_branch_name, 
          b2.branch_name as to_branch_name,
          p.name 
          FROM product_transfer pt
          LEFT JOIN branches b1 ON pt.from_branch = b1.branch_id
          LEFT JOIN branches b2 ON pt.to_branch = b2.branch_id
          LEFT JOIN product p ON pt.product_id = p.id
          WHERE pt.transfer_id = '$transfer_id'";

$result = mysqli_query($con, $query);

// Check if any records exist
if (mysqli_num_rows($result) == 0) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>No products found for this Transfer ID.</div></div>";
    include('ini/footer.php');
    exit();
}

// Fetch the first row to get general info (Branches, Date, Status)
$data = mysqli_fetch_assoc($result);
mysqli_data_seek($result, 0); // Reset pointer to loop through products later

// --- LOGIC: Flip branches if branch_to_branch_status is 1 ---
if ($data['branch_to_branch_status'] == 1) {
    $display_from = $data['to_branch_name'];
    $display_to   = $data['from_branch_name'];
} else {
    $display_from = $data['from_branch_name'];
    $display_to   = $data['to_branch_name'];
}

// Status Labeling
$status_text = "";
$status_class = "";
switch ($data['transfer_status']) {
    case 0: $status_text = "Requested"; $status_class = "warning"; break;
    case 1: $status_text = "Shipped"; $status_class = "info"; break;
    case 2: $status_text = "Received"; $status_class = "success"; break;
    default: $status_text = "Unknown"; $status_class = "secondary";
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body">
            
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h5 class="mb-3">From:</h5>
                    <h3 class="text-danger"><?php echo $display_from; ?></h3>
                    <div>Branch ID: #<?php echo ($data['branch_to_branch_status'] == 1) ? $data['to_branch'] : $data['from_branch']; ?></div>
                </div>

                <div class="col-sm-6 text-sm-right">
                    <h5 class="mb-3">To:</h5>
                    <h3 class="text-success"><?php echo $display_to; ?></h3>
                    <div>Branch ID: #<?php echo ($data['branch_to_branch_status'] == 1) ? $data['from_branch'] : $data['to_branch']; ?></div>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">Transfer Details:</h6>
                    <div><strong>Transfer ID:</strong> <?php echo $transfer_id; ?></div>
                    <div><strong>Date:</strong> <?php echo date('d-M-Y h:i A', strtotime($data['created_at'])); ?></div>
                </div>
                <div class="col-sm-6 text-sm-right">
                    <h6 class="mb-3">Status:</h6>
                    <span class="badge badge-<?php echo $status_class; ?> p-2" style="font-size: 1rem;">
                        <?php echo $status_text; ?>
                    </span>
                </div>
            </div>

            <div class="table-responsive-sm">
                <table class="table table-striped table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th class="center">#</th>
                            <th>Product Name</th>
                            <th>Lot No</th>
                            <th class="center">Qty</th>

                          </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $grand_total = 0;
                        while($row = mysqli_fetch_assoc($result)): 
                            $subtotal = $row['qty'] * $row['buy_price'];
                            $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td class="center"><?php echo $i++; ?></td>
                            <td class="left strong"><?php echo $row['name']; ?></td>
                            <td class="left"><?php echo $row['lot_no']; ?></td>
                            <td class="center"><?php echo $row['qty']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-lg-4 col-sm-5 ml-auto">
                    <table class="table table-clear">
                        <tbody>
                            <tr>
                                <td class="left"><strong>Grand Total</strong></td>
                                <td class="right"><strong><?php echo number_format($grand_total, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row no-print">
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-secondary" onclick="window.print();">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <a href="transfer_report.php?branch_id=<?php echo $data['from_branch']; ?>" class="btn btn-primary">
                        Back to List
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .sidebar, .navbar, .breadcrumb {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php 
include('ini/footer.php'); 
?>