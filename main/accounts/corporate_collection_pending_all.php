<?php
include('dbcon.php');
include('ini/header.php');

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01', strtotime("-3 months"));
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Summary Logic
$summary_sql = "SELECT SUM(selling_price * qty) as total_pending_amt, COUNT(DISTINCT corporate_sales_invoice_id) as total_pending_invoices
                FROM corporate_sales WHERE bill_collection_status = 0 
                AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'";
$summary_res = mysqli_query($con, $summary_sql);
$summary_data = mysqli_fetch_assoc($summary_res);

// Main Table Logic
$report_sql = "SELECT cs.corporate_sales_invoice_id, cs.corporate_name, cs.corporate_code, cs.delivery_date, b.branch_name,
                MAX(cs.created_at) as sale_date, SUM(cs.selling_price * cs.qty) as total_invoice_amount
               FROM corporate_sales cs LEFT JOIN branches b ON cs.branch_id = b.branch_id
               WHERE cs.bill_collection_status = 0 AND DATE(cs.created_at) BETWEEN '$from_date' AND '$to_date'
               GROUP BY cs.corporate_sales_invoice_id ORDER BY sale_date DESC";
$report_run = mysqli_query($con, $report_sql);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Master Pending Corporate Collections</h1>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Outstanding</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">PKR <?= number_format($summary_data['total_pending_amt'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Invoices</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary_data['total_pending_invoices'] ?> Invoices</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Consolidated Pending Bills</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="masterPendingTable" width="100%">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Invoice ID</th>
                            <th>Corporate Client</th>
                            <th>Delivery Date</th>
                            <th>Amount</th>
                            <th class="no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($report_run)): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($row['sale_date'])) ?></td>
                            <td><span class="badge badge-info"><?= $row['branch_name'] ?></span></td>
                            <td><?= $row['corporate_sales_invoice_id'] ?></td>
                            <td><strong><?= $row['corporate_name'] ?></strong></td>
                            <td><?= date('d-M-Y', strtotime($row['delivery_date'])) ?></td>
                            <td class="font-weight-bold text-danger"><?= number_format($row['total_invoice_amount'], 2) ?></td>
                            <td class="no-print">
                                <a href="view_corporate_invoice.php?invoice_id=<?= $row['corporate_sales_invoice_id'] ?>" class="btn btn-primary btn-sm">View</a>
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
    // Initializing DataTable
    var table = $('#masterPendingTable').DataTable({
        // B = Buttons, f = filter/search, r = processing, t = table, i = info, p = pagination
        dom: "<'row'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> Download PDF',
                className: 'btn btn-danger btn-sm',
                orientation: 'landscape',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print List',
                className: 'btn btn-info btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            }
        ],
        "order": [[ 0, "desc" ]],
        "pageLength": 25
    });
});
</script>

<?php include('ini/footer.php'); ?>