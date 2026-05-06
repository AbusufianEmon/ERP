<?php
include('ini/header.php');
include('dbcon.php');

// Step 0: Get the branch_id from the URL
if (isset($_GET['branch_id'])) {
    $branch_id = intval($_GET['branch_id']);
} else {
    // Fallback if branch_id is missing
    echo "<div class='alert alert-danger'>Error: Branch ID is missing.</div>";
    include('ini/footer.php');
    exit();
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Direct Sales</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Direct Sales List for Branch ID: <?php echo $branch_id; ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="salesTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice No</th>
                            <th>Customer Name</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Qty</th>
                            <th>Grand Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

<?php
/* ============================================================
   STEP 1: Calculate invoice totals (Filtered by branch_id)
============================================================ */
$invoiceTotals = [];
// Added WHERE branch_id = $branch_id
$calcSql = "SELECT invoice_no, SUM(sell_price * qty) AS total_actual, MAX(discount_percent) AS discount_percent 
            FROM direct_sales 
            WHERE branch_id = $branch_id 
            GROUP BY invoice_no";
$calcRes = mysqli_query($con, $calcSql);

while ($r = mysqli_fetch_assoc($calcRes)) {
    $discount = ($r['total_actual'] * $r['discount_percent']) / 100;
    $grand_total = $r['total_actual'] - $discount;
    $invoiceTotals[$r['invoice_no']] = $grand_total;
}

/* ============================================================
   STEP 2: Fetch ALL sales (Filtered by branch_id)
============================================================ */
// Added WHERE ds.branch_id = $branch_id
$sql = "SELECT ds.invoice_no, ds.product_name, ds.product_code, ds.qty, ds.status, c.name AS customer_name 
        FROM direct_sales ds 
        LEFT JOIN customer c ON ds.cus_id = c.cus_id 
        WHERE ds.branch_id = $branch_id 
        ORDER BY ds.invoice_no DESC";
$res = mysqli_query($con, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $grandTotal = $invoiceTotals[$row['invoice_no']] ?? 0;

        if ($row['status'] == 1) {
            $statusBadge = "<span class='badge bg-success' style='color:white; padding:5px;'>Approved</span>";
        } else {
            $statusBadge = "<span class='badge bg-warning text-dark' style='padding:5px;'>Pending</span>";
        }

        echo "<tr>";
        echo "<td>".htmlspecialchars($row['invoice_no'])."</td>";
        echo "<td>".htmlspecialchars($row['customer_name'])."</td>";
        echo "<td>".htmlspecialchars($row['product_name'])."</td>";
        echo "<td>".htmlspecialchars($row['product_code'])."</td>";
        echo "<td>".htmlspecialchars($row['qty'])."</td>";
        echo "<td><strong>".number_format($grandTotal, 2)."</strong></td>";
        echo "<td>$statusBadge</td>";
        echo "<td>
                <a href='single_view_direct_sales.php?invoice_id=".$row['invoice_no']."' 
                   class='btn btn-sm btn-primary'>
                   <i class='fas fa-eye'></i> View
                </a>
              </td>";
        echo "</tr>";
    }
}
?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {
    $('#salesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-secondary' },
            { extend: 'csv', className: 'btn btn-sm btn-success' },
            { extend: 'print', className: 'btn btn-sm btn-primary' }
        ],
        pageLength: 10,
        responsive: true,
        order: [[0, "desc"]] 
    });
});
</script>

<?php include('ini/footer.php'); ?>