<?php 
include('ini/header.php'); 
include('dbcon.php');

$id = intval($_SESSION['id']); 

// Capture Filters
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?? '';
$branch_id  = $_POST['branch_id'] ?? '';

// Helper for Date Filtering
$date_filter = "";
if(!empty($start_date) && !empty($end_date)){
    $date_filter = " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
}

// --- 1. DIRECT SALES PROFIT ---
$direct_profit_where = "WHERE (ds.sell_price * (1 - (ds.discount_percent/100))) > ds.buy_price" . str_replace('created_at', 'ds.created_at', $date_filter);
if(!empty($branch_id)) { $direct_profit_where .= " AND ds.branch_id = '$branch_id'"; }

$direct_profit_sql = "SELECT ds.invoice_no, ds.product_name, ds.qty, ds.buy_price, ds.sell_price, ds.discount_percent, ds.remarks, b.branch_name,
                        (((ds.sell_price * (1 - (ds.discount_percent/100))) - ds.buy_price) * ds.qty) as calc_profit
                    FROM direct_sales ds
                    LEFT JOIN branches b ON ds.branch_id = b.branch_id
                    $direct_profit_where ORDER BY ds.created_at DESC";
$direct_res = mysqli_query($con, $direct_profit_sql);

// --- 2. CORPORATE SALES PROFIT ---
$corp_profit_where = "WHERE cs.selling_price > cs.buy_price" . str_replace('created_at', 'cs.created_at', $date_filter);
if(!empty($branch_id)) { $corp_profit_where .= " AND cs.branch_id = '$branch_id'"; }

$corp_profit_sql = "SELECT cs.corporate_sales_invoice_id, b.branch_name, 
                  GROUP_CONCAT(DISTINCT cs.remarks SEPARATOR ', ') as remarks,
                  SUM((cs.selling_price - cs.buy_price) * cs.qty) as corp_profit
                  FROM corporate_sales cs
                  LEFT JOIN branches b ON cs.branch_id = b.branch_id
                  $corp_profit_where 
                  GROUP BY cs.corporate_sales_invoice_id, b.branch_name";
$corp_res = mysqli_query($con, $corp_profit_sql);

$total_direct_profit = 0;
$total_corp_profit = 0;
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Financial Profit Analysis Report</h1>

    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Direct Profit</div>
                    <div id="direct_profit_display" class="h5 mb-0 font-weight-bold text-gray-800">0.00</div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Corporate Profit</div>
                    <div id="corp_profit_display" class="h5 mb-0 font-weight-bold text-gray-800">0.00</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">Direct Sales Profit Breakdown</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable-profit" width="100%">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Invoice</th>
                            <th>Product</th>
                            <th>Buy Price</th>
                            <th>Net Sell Price</th>
                            <th>Profit</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($d_row = mysqli_fetch_assoc($direct_res)) { 
                            // Net sell price = sell_price - discount
                            $net_sell = $d_row['sell_price'] * (1 - ($d_row['discount_percent']/100));
                            $total_direct_profit += $d_row['calc_profit'];
                        ?>
                        <tr>
                            <td><?php echo $d_row['branch_name']; ?></td>
                            <td>#<?php echo $d_row['invoice_no']; ?></td>
                            <td><?php echo $d_row['product_name']; ?></td>
                            <td><?php echo number_format($d_row['buy_price'], 2); ?></td>
                            <td><?php echo number_format($net_sell, 2); ?></td>
                            <td class="text-success font-weight-bold"><?php echo number_format($d_row['calc_profit'], 2); ?></td>
                            <td><small><?php echo $d_row['remarks']; ?></small></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">Corporate Profit Breakdown</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable-profit" width="100%">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Invoice ID</th>
                            <th>Profit Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($c_row = mysqli_fetch_assoc($corp_res)) { 
                            $total_corp_profit += $c_row['corp_profit'];
                        ?>
                        <tr>
                            <td><?php echo $c_row['branch_name']; ?></td>
                            <td><?php echo $c_row['corporate_sales_invoice_id']; ?></td>
                            <td class="text-success font-weight-bold"><?php echo number_format($c_row['corp_profit'], 2); ?></td>
                            <td><small><?php echo $c_row['remarks']; ?></small></td>
                        </tr>
                        <?php } ?>
                    </tbody>
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
    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('.datatable-profit')) {
            $('.datatable-profit').DataTable().destroy();
        }

        $('.datatable-profit').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Export Profit Report',
                    className: 'btn btn-success mt-2'
                }
            ]
        });

        $('#direct_profit_display').text('<?php echo number_format($total_direct_profit, 2); ?>');
        $('#corp_profit_display').text('<?php echo number_format($total_corp_profit, 2); ?>');
    }, 500);
</script>