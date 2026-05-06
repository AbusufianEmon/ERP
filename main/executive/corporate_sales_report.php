<?php
include('dbcon.php');
include('ini/header.php');

// Get Branch ID from URL
$branch_id = $_GET['branch_id'];

// Set Date Filters (Default to current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

/* =============================================
   1. Strategic Summary Calculations
   ============================================= */

// 1. Total Corporate Sales (Sum of all selling prices)
$total_sales_sql = "SELECT SUM(selling_price * qty) as grand_total 
                    FROM corporate_sales 
                    WHERE branch_id = $branch_id 
                    AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'";
$total_sales_res = mysqli_query($con, $total_sales_sql);
$total_sales_data = mysqli_fetch_assoc($total_sales_res);
$grand_total = $total_sales_data['grand_total'] ?? 0;

// 2. Collected vs Pending (Grouped by Invoice Status)
$status_sql = "SELECT 
                SUM(CASE WHEN bill_collection_status = 1 THEN (selling_price * qty) ELSE 0 END) as collected,
                SUM(CASE WHEN bill_collection_status = 0 THEN (selling_price * qty) ELSE 0 END) as pending
               FROM corporate_sales 
               WHERE branch_id = $branch_id 
               AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'";
$status_res = mysqli_query($con, $status_sql);
$status_data = mysqli_fetch_assoc($status_res);
$collected = $status_data['collected'] ?? 0;
$pending = $status_data['pending'] ?? 0;

/* =============================================
   2. Fetch Grouped Invoice Records for Table
   ============================================= */
$report_sql = "SELECT 
                corporate_sales_invoice_id, 
                corporate_name, 
                corporate_code,
                delivery_date,
                bill_collection_status,
                MAX(created_at) as sale_date,
                SUM(selling_price * qty) as total_invoice_amount
               FROM corporate_sales 
               WHERE branch_id = $branch_id 
               AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'
               GROUP BY corporate_sales_invoice_id 
               ORDER BY sale_date DESC";
$report_run = mysqli_query($con, $report_sql);
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Corporate Sales Strategic Report</h1>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm no-print">
            <i class="fas fa-print fa-sm text-white-50"></i> Print Report
        </button>
    </div>

    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                <div class="form-group mr-3">
                    <label class="mr-2">From:</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">To:</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
                </div>
                <button type="submit" class="btn btn-success">Filter Report</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Corporate Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($grand_total, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Collected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($collected, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Pending (Receivables)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($pending, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Invoice-wise Corporate Summary</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="corporateReportTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Invoice ID</th>
                            <th>Corporate Client</th>
                            <th>Delivery Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th class="no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($report_run)): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($row['sale_date'])) ?></td>
                            <td><span class="badge badge-secondary"><?= $row['corporate_sales_invoice_id'] ?></span></td>
                            <td>
                                <strong><?= $row['corporate_name'] ?></strong><br>
                                <small class="text-muted"><?= $row['corporate_code'] ?></small>
                            </td>
                            <td><?= date('d-M-Y', strtotime($row['delivery_date'])) ?></td>
                            <td class="font-weight-bold text-dark"><?= number_format($row['total_invoice_amount'], 2) ?></td>
                            <td>
                                <?php if($row['bill_collection_status'] == 1): ?>
                                    <span class="badge badge-success">Collected</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print">
                                <a href="view_corporate_invoice.php?invoice_id=<?= $row['corporate_sales_invoice_id'] ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View Items
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
    $('#corporateReportTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        "order": [[ 0, "desc" ]],
        "pageLength": 25
    });
});
</script>

<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>

<?php include('ini/footer.php'); ?>