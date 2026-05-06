<?php 
include('ini/header.php'); // This includes your session_start and db connection
include('dbcon.php');

// 1. Get and Sanitize Branch ID from URL
if (!isset($_GET['branch_id'])) {
    echo "<div class='alert alert-danger'>Branch ID missing.</div>";
    exit;
}

$branch_id = intval($_GET['branch_id']);

/* ==========================================
   CALCULATION LOGIC
   ========================================== */

// A. Get Branch Name
$b_sql = mysqli_query($con, "SELECT branch_name FROM branches WHERE branch_id = $branch_id");
$b_row = mysqli_fetch_assoc($b_sql);
$branch_name = $b_row['branch_name'] ?? "Unknown";

// B. Sum of Unique Paid Amounts per Invoice
$sales_sql = mysqli_query($con, "SELECT SUM(paid_per_invoice) as total_direct FROM (
                                    SELECT MAX(paid_amount) as paid_per_invoice 
                                    FROM direct_sales 
                                    WHERE branch_id = $branch_id 
                                    AND status = 1 
                                    GROUP BY invoice_no
                                 ) as unique_sales");
$sales_data = mysqli_fetch_assoc($sales_sql);
$total_direct = floatval($sales_data['total_direct'] ?? 0);

// C. Sum of Corporate Collections
$corp_sql = mysqli_query($con, "SELECT SUM(qty * selling_price) as total_corporate 
                               FROM corporate_sales 
                               WHERE branch_id = $branch_id 
                               AND bill_collection_status = 1");
$corp_data = mysqli_fetch_assoc($corp_sql);
$total_corporate = floatval($corp_data['total_corporate'] ?? 0);

// D. Sum of all Deposits
$dep_sql = mysqli_query($con, "SELECT SUM(amount) as total_deposited 
                              FROM cash_deposits 
                              WHERE branch_id = $branch_id");
$dep_data = mysqli_fetch_assoc($dep_sql);
$total_deposited = floatval($dep_data['total_deposited'] ?? 0);

// E. Sum of all Expenses
$exp_sql = mysqli_query($con, "SELECT SUM(amount) as total_expenses 
                              FROM expenses 
                              WHERE branch_id = $branch_id");
$exp_data = mysqli_fetch_assoc($exp_sql);
$total_expenses = floatval($exp_data['total_expenses'] ?? 0);

// F. Final Calculations
$total_collected = $total_direct + $total_corporate;
$cash_in_hand    = $total_collected - ($total_deposited + $total_expenses);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cash Report: <span class="text-primary"><?php echo $branch_name; ?></span></h1>
        <div>
            <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#depositModal">
                <i class="fas fa-upload fa-sm text-white-50"></i> Record New Deposit
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Collections</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_collected, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Expenses</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_expenses, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Deposited</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_deposited, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Net Cash In Hand</div>
                    <div class="h5 mb-0 font-weight-bold text-danger"><?php echo number_format($cash_in_hand, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Deposit History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Slip</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $list = mysqli_query($con, "SELECT * FROM cash_deposits WHERE branch_id = $branch_id ORDER BY deposit_date DESC");
                                while($row = mysqli_fetch_assoc($list)){
                                ?>
                                <tr>
                                    <td><?php echo date('d-M-Y', strtotime($row['deposit_date'])); ?></td>
                                    <td><?php echo number_format($row['amount'], 2); ?></td>
                                    <td class="text-center">
                                        <?php if(!empty($row['slip_photo'])){ ?>
                                            <a href="uploads/<?php echo $row['slip_photo']; ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Calculation Breakdown</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">Gross Collected <span><?php echo number_format($total_collected, 2); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between text-warning">Total Expenses <span>- <?php echo number_format($total_expenses, 2); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between text-danger">Bank Deposits <span>- <?php echo number_format($total_deposited, 2); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between bg-dark text-white font-weight-bold">Available Cash <span><?php echo number_format($cash_in_hand, 2); ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="depositModal" tabindex="-1" role="dialog" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="save_deposit.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="depositModalLabel">Record New Bank Deposit</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
                    
                    <div class="p-3 mb-3 bg-light border-left-danger">
                        <small class="text-uppercase text-muted d-block">Maximum Transferrable Cash:</small>
                        <strong class="h4 text-danger"><?php echo number_format($cash_in_hand, 2); ?></strong>
                    </div>

                    <div class="form-group">
                        <label>Deposit Amount</label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control" required max="<?php echo $cash_in_hand; ?>" placeholder="Minimum 1.00">
                    </div>

                    <div class="form-group">
                        <label>Date of Deposit</label>
                        <input type="date" name="deposit_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Upload Deposit Slip</label>
                        <input type="file" name="slip_photo" class="form-control-file border p-1 rounded w-100">
                    </div>

                    <div class="form-group">
                        <label>Reference / Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Bank Asia, Branch Transfer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" name="btn_deposit">Confirm Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>