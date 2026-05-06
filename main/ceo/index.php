<?php
include('ini/header.php');
include('dbcon.php');

// 1. DIRECT SALES CALCULATIONS
// We use CASE statements to separate Profits from Losses at the row level
$direct_sql = "SELECT 
    SUM(CASE WHEN (sell_price * (1 - (discount_percent/100))) > buy_price 
        THEN ((sell_price * (1 - (discount_percent/100))) - buy_price) * qty ELSE 0 END) AS profit,
    SUM(CASE WHEN buy_price > (sell_price * (1 - (discount_percent/100))) 
        THEN (buy_price - (sell_price * (1 - (discount_percent/100)))) * qty ELSE 0 END) AS loss
    FROM direct_sales";
$direct_res = mysqli_fetch_assoc(mysqli_query($con, $direct_sql));
$direct_profit = $direct_res['profit'] ?? 0;
$direct_loss = $direct_res['loss'] ?? 0;

// 2. CORPORATE SALES CALCULATIONS
$corp_sql = "SELECT 
    SUM(CASE WHEN selling_price > buy_price THEN (selling_price - buy_price) * qty ELSE 0 END) AS profit,
    SUM(CASE WHEN buy_price > selling_price THEN (buy_price - selling_price) * qty ELSE 0 END) AS loss
    FROM corporate_sales";
$corp_res = mysqli_fetch_assoc(mysqli_query($con, $corp_sql));
$corp_profit = $corp_res['profit'] ?? 0;
$corp_loss = $corp_res['loss'] ?? 0;

// 3. EXPENSES
$exp_sql = "SELECT SUM(amount) as total_exp FROM expenses";
$exp_res = mysqli_fetch_assoc(mysqli_query($con, $exp_sql));
$total_expenses = $exp_res['total_exp'] ?? 0;

// 4. STOCK FAULTY LOSS
// Using loss_amount column as per your table schema
$faulty_sql = "SELECT SUM(loss_amount) as total_faulty FROM stock_faulty_items";
$faulty_res = mysqli_fetch_assoc(mysqli_query($con, $faulty_sql));
$total_faulty_loss = $faulty_res['total_faulty'] ?? 0;

// 5. AGGREGATED TOTALS
$total_sales_profit = $direct_profit + $corp_profit;
$total_sales_loss = $direct_loss + $corp_loss;
$total_all_losses = $total_sales_loss + $total_expenses + $total_faulty_loss;

// Final Summary Result
$net_balance = $total_sales_profit - $total_all_losses;
?>

<div class="container-fluid mt-4">
    <h1 class="h3 mb-4 text-gray-800">CEO Dashboard</h1>

    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sales Profits (Direct + Corp)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_sales_profit, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Sales Losses (Under-price Sales)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_sales_loss, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Operational Expenses</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_expenses, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Stock Faulty Losses</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_faulty_loss, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Profit & Loss Summary Report</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-right">Earnings (+)</th>
                                    <th class="text-right">Deductions (-)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Direct & Corporate Sales Profits</td>
                                    <td class="text-right text-success"><?php echo number_format($total_sales_profit, 2); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Sales-Price Deficit (Losses)</td>
                                    <td></td>
                                    <td class="text-right text-danger"><?php echo number_format($total_sales_loss, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Company Expenses</td>
                                    <td></td>
                                    <td class="text-right text-danger"><?php echo number_format($total_expenses, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Stock Faulty/Damaged Items</td>
                                    <td></td>
                                    <td class="text-right text-danger"><?php echo number_format($total_faulty_loss, 2); ?></td>
                                </tr>
                            </tbody>
                           
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <a href="revenu_report.php" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> View Detailed Sales</a>
                        <a href="expense_report_all.php" class="btn btn-sm btn-secondary shadow-sm">Expense Breakdown</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>