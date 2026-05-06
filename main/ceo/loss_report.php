<?php 
include('ini/header.php'); 
include('dbcon.php');

$id = intval($_SESSION['id']); 

// Capture Filters
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?? '';
$f_supplier = $_POST['f_supplier'] ?? '';
$branch_id  = $_POST['branch_id'] ?? '';

// Helper for Date Filtering
$date_filter = "";
if(!empty($start_date) && !empty($end_date)){
    $date_filter = " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
}

// --- 1. FAULTY ITEMS LOSS ---
$faulty_where = "WHERE 1=1" . str_replace('created_at', 'f.created_at', $date_filter);
if(!empty($f_supplier)) { $faulty_where .= " AND f.supplier_id = '$f_supplier'"; }

$faulty_sql = "SELECT f.product_id, p.name as product_name, s.sup_name, SUM(f.qty) as total_qty, SUM(f.loss_amount) as total_faulty_loss
               FROM stock_faulty_items f
               LEFT JOIN product p ON f.product_id = p.id
               LEFT JOIN supplier s ON f.supplier_id = s.id
               $faulty_where
               GROUP BY f.product_id, f.supplier_id
               ORDER BY total_faulty_loss DESC";
$faulty_res = mysqli_query($con, $faulty_sql);

// --- 2. DIRECT SALES LOSS ---
$direct_where = "WHERE (ds.sell_price * (1 - (ds.discount_percent/100))) < ds.buy_price" . str_replace('created_at', 'ds.created_at', $date_filter);
if(!empty($branch_id)) { $direct_where .= " AND ds.branch_id = '$branch_id'"; }

$direct_loss_sql = "SELECT ds.invoice_no, ds.product_name, ds.qty, ds.buy_price, ds.sell_price, ds.discount_percent, ds.remarks, b.branch_name,
                        ((ds.buy_price - (ds.sell_price * (1 - (ds.discount_percent/100)))) * ds.qty) as calc_loss
                    FROM direct_sales ds
                    LEFT JOIN branches b ON ds.branch_id = b.branch_id
                    $direct_where ORDER BY ds.created_at DESC";
$direct_res = mysqli_query($con, $direct_loss_sql);

// --- 3. CORPORATE SALES LOSS ---
$corp_where = "WHERE selling_price < buy_price" . str_replace('created_at', 'created_at', $date_filter);
if(!empty($branch_id)) { $corp_where .= " AND branch_id = '$branch_id'"; }

$corp_loss_sql = "SELECT corporate_sales_invoice_id, SUM((buy_price - selling_price) * qty) as corp_loss
                  FROM corporate_sales $corp_where GROUP BY corporate_sales_invoice_id";
$corp_res = mysqli_query($con, $corp_loss_sql);

$total_direct_loss = 0;
$total_corp_loss = 0;
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Financial Loss Analysis Report</h1>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Stock Damage Loss</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="faulty_sum_display">0.00</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Direct Sales Loss</div>
                    <div id="direct_loss_summary" class="h5 mb-0 font-weight-bold text-gray-800">0.00</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Corporate Loss</div>
                    <div id="corp_loss_summary" class="h5 mb-0 font-weight-bold text-gray-800">0.00</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-danger text-white">Faulty Item Analysis</div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table1" class="table table-bordered datatable-export" width="100%">
                    <thead><tr><th>Product</th><th>Supplier</th><th>Qty</th><th>Loss Amount</th></tr></thead>
                    <tbody>
                        <?php 
                        $f_sum = 0;
                        while($f_row = mysqli_fetch_assoc($faulty_res)) { 
                            $f_sum += $f_row['total_faulty_loss'];
                        ?>
                        <tr>
                            <td><?php echo $f_row['product_name']; ?></td>
                            <td><?php echo $f_row['sup_name']; ?></td>
                            <td><?php echo $f_row['total_qty']; ?></td>
                            <td><?php echo number_format($f_row['total_faulty_loss'], 2); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning text-white">Direct Sales Loss</div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table2" class="table table-sm table-bordered datatable-export" width="100%">
                    <thead><tr><th>Branch</th><th>Invoice</th><th>Product</th><th>Disc%</th><th>Price</th><th>Loss</th><th>Remarks</th></tr></thead>
                    <tbody>
                        <?php while($d_row = mysqli_fetch_assoc($direct_res)) { 
                            $effective_price = $d_row['sell_price'] * (1 - ($d_row['discount_percent']/100));
                            $total_direct_loss += $d_row['calc_loss'];
                        ?>
                        <tr>
                            <td><?php echo $d_row['branch_name']; ?></td>
                            <td>#<?php echo $d_row['invoice_no']; ?></td>
                            <td><?php echo $d_row['product_name']; ?></td>
                            <td><?php echo $d_row['discount_percent']; ?>%</td>
                            <td><?php echo number_format($effective_price, 2); ?></td>
                            <td><?php echo number_format($d_row['calc_loss'], 2); ?></td>
                            <td><?php echo $d_row['remarks']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-dark text-white">Corporate Price Deficit</div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table3" class="table table-sm table-bordered datatable-export" width="100%">
                    <thead><tr><th>Invoice ID</th><th>Loss Amount</th></tr></thead>
                    <tbody>
                        <?php while($c_row = mysqli_fetch_assoc($corp_res)) { 
                            $total_corp_loss += $c_row['corp_loss'];
                        ?>
                        <tr>
                            <td><?php echo $c_row['corporate_sales_invoice_id']; ?></td>
                            <td><?php echo number_format($c_row['corp_loss'], 2); ?></td>
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
    // We use a small delay to ensure the footer scripts finish loading/resetting jQuery
    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('.datatable-export')) {
            $('.datatable-export').DataTable().destroy();
        }

        $('.datatable-export').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-success mt-2'
                }
            ]
        });

        // Update Summary Totals
        $('#faulty_sum_display').text('<?php echo number_format($f_sum, 2); ?>');
        $('#direct_loss_summary').text('<?php echo number_format($total_direct_loss, 2); ?>');
        $('#corp_loss_summary').text('<?php echo number_format($total_corp_loss, 2); ?>');
    }, 500);
</script>