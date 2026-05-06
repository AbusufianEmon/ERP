<?php
include('dbcon.php');
include('ini/header.php');

// Get Branch ID from URL
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

// Set Date Filters (Default to current month if not set)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

$summary_sql = "SELECT 
                    SUM(total_price) as total_sales, 
                    SUM(paid_amount) as total_received, 
                    SUM(due_amount) as total_due,
                    SUM((sell_price - buy_price) * qty) as total_profit
                FROM direct_sales 
                WHERE branch_id = $branch_id 
                AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'";

$summary_res = mysqli_query($con, $summary_sql);
$summary = mysqli_fetch_assoc($summary_res);

/* =============================================
   2. Fetch Detailed Sales Records
   ============================================= */
$sales_sql = "SELECT * FROM direct_sales 
              WHERE branch_id = $branch_id 
              AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'
              ORDER BY created_at DESC";
$sales_run = mysqli_query($con, $sales_sql);
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Direct Sales Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Sales Report</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow mb-4 no-print">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filter by Date Range</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="form-inline">
                <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                <div class="form-group mr-3">
                    <label class="mr-2">From:</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">To:</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
                </div>
                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-sync fa-sm text-white-50"></i> Generate Report
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales (Gross)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_sales'], 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Estimated Profit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_profit'], 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cash Collected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_received'], 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Outstanding Due</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_due'], 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Sales List (<?= date('d M Y', strtotime($from_date)) ?> to <?= date('d M Y', strtotime($to_date)) ?>)</h6>
            <button onclick="window.print()" class="btn btn-sm btn-secondary shadow-sm no-print"><i class="fas fa-print fa-sm"></i> Print Table</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="reportTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Sell Price</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($sales_run)): 
                            $profit = ($row['sell_price'] - $row['buy_price']) * $row['qty'];
                        ?>
                        <tr>
                            <td><?= date('d-M-y', strtotime($row['created_at'])) ?></td>
                            <td><span class="badge badge-dark"><?= $row['invoice_no'] ?></span></td>
                            <td>
                                <strong><?= $row['product_name'] ?></strong><br>
                                <small class="text-muted"><?= $row['product_code'] ?></small>
                            </td>
                            <td><?= $row['qty'] ?></td>
                            <td><?= number_format($row['sell_price'], 2) ?></td>
                            <td class="font-weight-bold"><?= number_format($row['total_price'], 2) ?></td>
                            <td class="text-success"><?= number_format($row['paid_amount'], 2) ?></td>
                            <td class="text-danger"><?= number_format($row['due_amount'], 2) ?></td>
                            <td><?= number_format($profit, 2) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "order": [[ 0, "desc" ]],
        "pageLength": 25
    });
});
</script>

<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; }
        .shadow { box-shadow: none !important; }
    }
</style>

<?php include('ini/footer.php'); ?>