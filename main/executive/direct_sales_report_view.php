<?php
include('dbcon.php');
include('ini/header.php');

// Get Branch ID from URL
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

// Set Date Filters
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

/* =============================================
   1. Fetch Summary Calculations
   ============================================= */

// Calculate Total Sales (Subtotal minus the calculated discount)
$sales_query = "SELECT SUM(total_price - (total_price * (discount_percent / 100))) as net_sales 
                FROM direct_sales 
                WHERE branch_id = $branch_id 
                AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'";
$sales_res = mysqli_query($con, $sales_query);
$sales_row = mysqli_fetch_assoc($sales_res);
$total_sales = $sales_row['net_sales'] ?? 0;

// Calculate Cash Collected (One payment per invoice, only if status is 1)
$cash_query = "SELECT SUM(paid_amount) as total_cash FROM (
                    SELECT DISTINCT invoice_no, paid_amount 
                    FROM direct_sales 
                    WHERE branch_id = $branch_id 
                    AND status = 1 
                    AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'
                ) as unique_payments";
$cash_res = mysqli_query($con, $cash_query);
$cash_row = mysqli_fetch_assoc($cash_res);
$total_cash = $cash_row['total_cash'] ?? 0;

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
        <div class="card-body">
            <form method="GET" action="" class="form-inline">
                <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                <div class="form-group mr-3">
                    <label class="mr-2 font-weight-bold">From:</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2 font-weight-bold">To:</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
                </div>
                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-sync fa-sm text-white-50"></i> Generate Report
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Net Sales (After Discount)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_sales, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-shopping-bag fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Cash Collected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_cash, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-money-bill-wave fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Transaction Details (<?= date('d M Y', strtotime($from_date)) ?> - <?= date('d M Y', strtotime($to_date)) ?>)</h6>
            <button onclick="window.print()" class="btn btn-sm btn-secondary shadow-sm no-print"><i class="fas fa-print fa-sm"></i> Print Report</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="reportTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Disc (%)</th>
                            <th class="no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($sales_run)): ?>
                        <tr>
                            <td><?= date('d-M-y', strtotime($row['created_at'])) ?></td>
                            <td><span class="badge badge-dark"><?= $row['invoice_no'] ?></span></td>
                            <td class="font-weight-bold text-dark"><?= $row['product_code'] ?></td>
                            <td><?= $row['product_name'] ?></td>
                            <td><?= $row['qty'] ?></td>
                            <td><?= number_format($row['sell_price'], 2) ?></td>
                            <td><?= $row['discount_percent'] ?>%</td>
                            <td class="no-print">
                                <a href="sale_invoice.php?invoice_id=<?= $row['invoice_no'] ?>" class="btn btn-sm btn-info btn-circle shadow-sm" title="View Invoice">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
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
            { extend: 'excel', className: 'btn-sm btn-success' },
            { extend: 'pdf', className: 'btn-sm btn-danger' },
            { extend: 'print', className: 'btn-sm btn-info' }
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
        .table-bordered th, .table-bordered td { border: 1px solid #e3e6f0 !important; }
    }
</style>

<?php include('ini/footer.php'); ?>