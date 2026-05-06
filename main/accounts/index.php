<?php
include('ini/header.php');
include('dbcon.php');

// Note: Ensure $id is defined via session (usually in header.php or auth check)
$user_sql = "SELECT u.*, b.branch_name 
            FROM user u 
            LEFT JOIN branches b ON u.branch_id = b.branch_id 
            WHERE u.id = '$id'";
$exe = mysqli_query($con, $user_sql);
$user_data = mysqli_fetch_assoc($exe);

// --- REAL STATS CALCULATIONS ---

// 1. Total Direct Sales Count
$sales_query = mysqli_query($con, "SELECT COUNT(DISTINCT invoice_no) as total FROM direct_sales");
$sales_data = mysqli_fetch_assoc($sales_query);
$total_sales_count = $sales_data['total'];

// 2. Pending Corporate Bill Count
$pending_query = mysqli_query($con, "SELECT COUNT(DISTINCT corporate_sales_invoice_id) as total FROM corporate_sales WHERE bill_collection_status = 0");
$pending_data = mysqli_fetch_assoc($pending_query);
$pending_bill_count = $pending_data['total'];

// 3. Total Cash Deposits (Global)
$deposit_query = mysqli_query($con, "SELECT SUM(amount) as total FROM cash_deposits");
$deposit_data = mysqli_fetch_assoc($deposit_query);
$total_deposits_val = $deposit_data['total'] ?? 0;

// 4. Total Expenses (Global)
$expense_query = mysqli_query($con, "SELECT SUM(amount) as total FROM expenses");
$expense_data = mysqli_fetch_assoc($expense_query);
$total_expenses_val = $expense_data['total'] ?? 0;

// 5. Direct Sales Total Due
$direct_due_query = mysqli_query($con, "SELECT SUM(unique_due) as total FROM (
                                        SELECT MAX(due_amount) as unique_due 
                                        FROM direct_sales 
                                        GROUP BY invoice_no) as t");
$direct_due_data = mysqli_fetch_assoc($direct_due_query);
$total_direct_due = $direct_due_data['total'] ?? 0;

// 6. Corporate Sales Total Pending
$corp_pending_query = mysqli_query($con, "SELECT SUM(selling_price * qty) as total FROM corporate_sales WHERE bill_collection_status = 0");
$corp_pending_data = mysqli_fetch_assoc($corp_pending_query);
$total_corp_pending = $corp_pending_data['total'] ?? 0;

/* ==========================================
   GLOBAL CASH IN HAND CALCULATION
   (Collections - (Deposits + Expenses))
   ========================================== */
$all_sales_sql = mysqli_query($con, "SELECT SUM(paid_per_invoice) as total FROM (
                                    SELECT MAX(paid_amount) as paid_per_invoice 
                                    FROM direct_sales WHERE status = 1 
                                    GROUP BY invoice_no) as t");
$all_sales_data = mysqli_fetch_assoc($all_sales_sql);
$total_all_direct = floatval($all_sales_data['total'] ?? 0);

$all_corp_sql = mysqli_query($con, "SELECT SUM(qty * selling_price) as total FROM corporate_sales WHERE bill_collection_status = 1");
$all_corp_data = mysqli_fetch_assoc($all_corp_sql);
$total_all_corp = floatval($all_corp_data['total'] ?? 0);

$global_cash_in_hand = ($total_all_direct + $total_all_corp) - (floatval($total_deposits_val) + floatval($total_expenses_val));
?>

<style>
    .dash-card { border: none; border-radius: 15px; transition: all 0.3s ease; background: #fff; }
    .dash-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
    .icon-shape { width: 48px; height: 48px; background: rgba(25, 135, 84, 0.1); color: #198754; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .welcome-banner { background: linear-gradient(45deg, #0f5132, #198754); border-radius: 20px; padding: 30px; color: white; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3); }
    .stat-value { font-size: 1.5rem; font-weight: 800; color: #333; }
</style>

<div class="container-fluid">

    <div class="welcome-banner d-md-flex align-items-center justify-content-between">
        <div>
            <h2 class="font-weight-bold">Welcome, <?php echo explode(' ', $user_data['s_name'])[0]; ?>!</h2>
            <p class="mb-0 opacity-75">Accounts & CRM Master Dashboard</p>
        </div>
        <div class="text-md-right mt-3 mt-md-0">
            <small class="text-uppercase opacity-75 d-block">System-Wide Net Cash In Hand</small>
            <h2 class="mb-0 font-weight-bold">BDT <?php echo number_format($global_cash_in_hand, 2); ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dash-card shadow-sm h-100 py-2 border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Customer Dues</div>
                            <div class="stat-value text-danger"><?php echo number_format($total_direct_due, 2); ?></div>
                            <a href="customer_due_report_all.php" class="small text-muted mt-2 d-block">View Report &rarr;</a>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape" style="background: rgba(231, 74, 59, 0.1); color: #e74a3b;"><i class="fas fa-user-clock"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dash-card shadow-sm h-100 py-2 border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Corporate Pending</div>
                            <div class="stat-value text-warning"><?php echo number_format($total_corp_pending, 2); ?></div>
                            <a href="corporate_due_report_all.php" class="small text-muted mt-2 d-block"><?php echo $pending_bill_count; ?> Pending Invoices &rarr;</a>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape" style="background: rgba(246, 194, 62, 0.1); color: #f6c23e;"><i class="fas fa-building"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dash-card shadow-sm h-100 py-2 border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Bank Deposits</div>
                            <div class="stat-value text-info"><?php echo number_format($total_deposits_val, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape" style="background: rgba(54, 185, 204, 0.1); color: #36b9cc;"><i class="fas fa-university"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dash-card shadow-sm h-100 py-2 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Expenses</div>
                            <div class="stat-value text-primary"><?php echo number_format($total_expenses_val, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape" style="background: rgba(78, 115, 223, 0.1); color: #4e73df;"><i class="fas fa-wallet"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm dash-card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Branch-wise Cash Statement (Collections - [Deposits + Expenses])</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover border text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-left">Branch Name</th>
                                    <th>Total Collections</th>
                                    <th class="text-primary">Expenses</th>
                                    <th class="text-info">Deposits</th>
                                    <th class="bg-dark text-white">Net Cash in Hand</th>
                                    <th class="text-danger">Customer Dues</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $branches = mysqli_query($con, "SELECT * FROM branches");
                                while($b = mysqli_fetch_assoc($branches)){
                                    $bid = $b['branch_id'];
                                    
                                    // 1. Direct Sales Collection (MAX paid per invoice)
                                    $ds = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(paid_per_invoice) as total FROM (SELECT MAX(paid_amount) as paid_per_invoice FROM direct_sales WHERE branch_id = $bid AND status = 1 GROUP BY invoice_no) as t"));
                                    $total_ds = floatval($ds['total'] ?? 0);

                                    // 2. Corporate Collection
                                    $cs = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(qty * selling_price) as total FROM corporate_sales WHERE branch_id = $bid AND bill_collection_status = 1"));
                                    $total_cs = floatval($cs['total'] ?? 0);

                                    // 3. Total Deposits
                                    $dp = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(amount) as total FROM cash_deposits WHERE branch_id = $bid"));
                                    $total_dp = floatval($dp['total'] ?? 0);

                                    // 4. Total Expenses (Added to match Executive logic)
                                    $ex = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(amount) as total FROM expenses WHERE branch_id = $bid"));
                                    $total_ex = floatval($ex['total'] ?? 0);

                                    // Executive Calculation: Collection - (Deposited + Expenses)
                                    $b_gross_collection = $total_ds + $total_cs;
                                    $b_net_cash = $b_gross_collection - ($total_dp + $total_ex);

                                    // 5. Branch Direct Dues
                                    $b_due_res = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(unique_due) as total FROM (SELECT MAX(due_amount) as unique_due FROM direct_sales WHERE branch_id = $bid GROUP BY invoice_no) as t"));
                                    $b_direct_due = $b_due_res['total'] ?? 0;
                                ?>
                                <tr>
                                    <td class="font-weight-bold text-left"><?php echo $b['branch_name']; ?></td>
                                    <td><?php echo number_format($b_gross_collection, 2); ?></td>
                                    <td class="text-primary"><?php echo number_format($total_ex, 2); ?></td>
                                    <td class="text-info"><?php echo number_format($total_dp, 2); ?></td>
                                    <td class="font-weight-bold <?php echo ($b_net_cash < 0) ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($b_net_cash, 2); ?>
                                    </td>
                                    <td class="text-danger"><?php echo number_format($b_direct_due, 2); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>