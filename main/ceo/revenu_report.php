<?php 
include('ini/header.php'); 
include('dbcon.php');

$id = intval($_SESSION['id']); 

// 1. CAPTURE FILTERS
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?? '';
$branch_id  = $_POST['branch_id'] ?? '';

$date_filter = "";
$exp_date_filter = "";
if(!empty($start_date) && !empty($end_date)){
    $date_filter = " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    $exp_date_filter = " AND DATE(expense_date) BETWEEN '$start_date' AND '$end_date'";
}
$branch_filter = !empty($branch_id) ? " AND branch_id = '$branch_id'" : "";

// --- TOTAL SALES AMOUNT ---
$direct_sales_sql = "SELECT SUM(sell_price * (1 - (discount_percent/100)) * qty) as total_direct_sales FROM direct_sales WHERE 1=1 $branch_filter $date_filter";
$direct_sales_res = mysqli_fetch_assoc(mysqli_query($con, $direct_sales_sql));
$total_direct_sales = $direct_sales_res['total_direct_sales'] ?? 0;

$corp_sales_sql = "SELECT SUM(selling_price * qty) as total_corp_sales FROM corporate_sales WHERE 1=1 $branch_filter $date_filter";
$corp_sales_res = mysqli_fetch_assoc(mysqli_query($con, $corp_sales_sql));
$total_corp_sales = $corp_sales_res['total_corp_sales'] ?? 0;

$total_sales_amount = $total_direct_sales + $total_corp_sales;

// --- 2. DIRECT SALES: SEPARATE PROFIT AND LOSS ---
$direct_sql = "SELECT 
    SUM(CASE WHEN (sell_price * (1 - (discount_percent/100))) > buy_price 
        THEN ((sell_price * (1 - (discount_percent/100))) - buy_price) * qty ELSE 0 END) as profit,
    SUM(CASE WHEN buy_price > (sell_price * (1 - (discount_percent/100))) 
        THEN (buy_price - (sell_price * (1 - (discount_percent/100)))) * qty ELSE 0 END) as loss
    FROM direct_sales WHERE 1=1 $branch_filter $date_filter";
$direct_res = mysqli_fetch_assoc(mysqli_query($con, $direct_sql));
$direct_profit = $direct_res['profit'] ?? 0;
$direct_loss = $direct_res['loss'] ?? 0;

// --- 3. CORPORATE SALES: SEPARATE PROFIT AND LOSS ---
$corp_sql = "SELECT 
    SUM(CASE WHEN selling_price > buy_price THEN (selling_price - buy_price) * qty ELSE 0 END) as profit,
    SUM(CASE WHEN buy_price > selling_price THEN (buy_price - selling_price) * qty ELSE 0 END) as loss
    FROM corporate_sales WHERE 1=1 $branch_filter $date_filter";
$corp_res = mysqli_fetch_assoc(mysqli_query($con, $corp_sql));
$corp_p = $corp_res['profit'] ?? 0;
$corp_l = $corp_res['loss'] ?? 0;

// --- 4. EXPENSES ---
$exp_sql = "SELECT SUM(amount) as total_exp FROM expenses WHERE 1=1 $branch_filter $exp_date_filter";
$exp_res = mysqli_fetch_assoc(mysqli_query($con, $exp_sql));
$total_expenses = $exp_res['total_exp'] ?? 0;

// --- 5. FAULTY LOSS ---
$faulty_sql = "SELECT SUM(loss_amount) as total_loss FROM stock_faulty_items WHERE 1=1 $date_filter";
$faulty_res = mysqli_fetch_assoc(mysqli_query($con, $faulty_sql));
$total_faulty_loss = $faulty_res['total_loss'] ?? 0;

// --- 6. AGGREGATED CALCULATIONS ---
$grand_total_profit = $direct_profit + $corp_p;
$grand_total_sales_loss = $direct_loss + $corp_l;
$final_net_summary = $grand_total_profit - ($grand_total_sales_loss + $total_expenses + $total_faulty_loss);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Financial Performance Report</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" class="form-inline">
                <input type="date" name="start_date" class="form-control mr-2" value="<?php echo $start_date; ?>">
                <input type="date" name="end_date" class="form-control mr-2" value="<?php echo $end_date; ?>">
                <select name="branch_id" class="form-control mr-2">
                    <option value="">All Branches</option>
                    <?php 
                    $b_sql = mysqli_query($con, "SELECT * FROM branches");
                    while($b = mysqli_fetch_assoc($b_sql)){
                        $sel = ($branch_id == $b['branch_id']) ? 'selected' : '';
                        echo "<option value='".$b['branch_id']."' $sel>".$b['branch_name']."</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Sales Amount</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_sales_amount, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sales Profit</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($grand_total_profit, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Sales Deficit Loss</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($grand_total_sales_loss, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Operational Expenses</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_expenses, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Stock Faulty Loss</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_faulty_loss, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">Consolidated Profit & Loss Summary</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable-revenue" width="100%" cellspacing="0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>Financial Category</th>
                            <th class="text-right">Credit (+)</th>
                            <th class="text-right">Debit (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gross Sales Profit (Direct + Corporate)</td>
                            <td class="text-right text-success"><?php echo number_format($grand_total_profit, 2); ?></td>
                            <td class="text-right">-</td>
                        </tr>
                        <tr>
                            <td>Sales Pricing Loss (Discount/Under-cost)</td>
                            <td class="text-right">-</td>
                            <td class="text-right text-danger"><?php echo number_format($grand_total_sales_loss, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Total General Expenses</td>
                            <td class="text-right">-</td>
                            <td class="text-right text-danger"><?php echo number_format($total_expenses, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Stock Faulty Item Loss</td>
                            <td class="text-right">-</td>
                            <td class="text-right text-danger"><?php echo number_format($total_faulty_loss, 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th class="text-center h5">Final Net Business Outcome</th>
                            <th colspan="2" class="text-right h5 <?php echo ($final_net_summary >= 0) ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($final_net_summary >= 0) ? 'PROFIT: ' : 'LOSS: '; ?>
                                <?php echo number_format(abs($final_net_summary), 2); ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('ini/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        $('.datatable-revenue').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Financial_Summary_Report'
                }
            ],
            "paging": false,
            "info": false,
            "searching": false
        });
    });
</script>