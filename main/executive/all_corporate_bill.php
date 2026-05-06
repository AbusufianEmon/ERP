<?php
include('dbcon.php');
include('ini/header.php');

/* ===============================
    Get and Sanitize Branch ID
================================ */
if (!isset($_GET['branch_id'])) {
    die("<div class='alert alert-danger'>Error: Branch ID is missing.</div>");
}
$branch_id = intval($_GET['branch_id']);
$branch_sql = "SELECT branch_name FROM branches WHERE branch_id = $branch_id LIMIT 1";
$branch_res = mysqli_query($con, $branch_sql);
$branch_row = mysqli_fetch_assoc($branch_res);
/* ===============================
    Fetch Corporate Bills
================================ */
// We use cs.* to get all columns including corporate_sales_invoice_id
$sql = "SELECT 
            cs.*, 
            cc.corporate_name AS customer_name, 
            cc.corporate_code AS customer_code,
            b.branch_name
        FROM corporate_sales cs
        LEFT JOIN corporate_customer cc ON cs.corporate_id = cc.corporate_id
        LEFT JOIN branches b ON cs.branch_id = b.branch_id
        WHERE cs.branch_id = $branch_id
        ORDER BY cs.corporate_sales_id DESC";

$run = mysqli_query($con, $sql);

if (!$run) {
    die("Query Error: " . mysqli_error($con));
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Corporate Billing Records</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">All Corporate Bills</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-file-invoice-dollar mr-2"></i> 
                Direct Sales List (Branch: <?php echo $branch_row['branch_name']; ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center" id="billTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Date</th>
                            <th>Corporate Name</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total Amount</th>
                            <th>Collection Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($row = mysqli_fetch_assoc($run)) { 
                            $total_bill = $row['qty'] * $row['selling_price'];
                        ?>
                        <tr>
                            <td class="font-weight-bold text-primary">
                                <?php echo $row['corporate_sales_invoice_id']; ?>
                            </td>
                            
                            <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                            
                            <td>
                                <?php echo htmlspecialchars($row['corporate_name']); ?><br>
                                <small class="badge badge-light"><?php echo $row['corporate_code']; ?></small>
                            </td>
                            
                            <td>
                                <?php echo htmlspecialchars($row['product_name']); ?><br>
                                <small class="text-muted"><?php echo $row['product_code']; ?></small>
                            </td>
                            
                            <td><?php echo $row['qty']; ?></td>
                            
                            <td><?php echo number_format($row['selling_price'], 2); ?></td>
                            
                            <td class="font-weight-bold text-dark">
                                <?php echo number_format($total_bill, 2); ?>
                            </td>
                            
                            <td>
                                <?php if($row['bill_collection_status'] == 1): ?>
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Paid</span>
                                    <br><small><?php echo date('d-M-y', strtotime($row['bill_collection_date'])); ?></small>
                                <?php else: ?>
                                    <span class="badge badge-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div class="dropdown no-arrow">
                                    <a class="dropdown-toggle btn btn-sm btn-outline-secondary" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                        <a class="dropdown-item" href="view_corporate_invoice.php?invoice_id=<?php echo $row['corporate_sales_invoice_id']; ?>&branch_id=<?php echo $branch_id; ?>">
                                            <i class="fas fa-eye fa-sm fa-fw mr-2 text-info"></i> View Invoice
                                        </a>
                                      
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('ini/footer.php'); ?>

<script>
$(document).ready(function() {
    if ( ! $.fn.DataTable.isDataTable( '#billTable' ) ) {
        $('#billTable').DataTable({
            "pageLength": 25,
            "order": [[ 1, "desc" ]], 
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-info',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
                },
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-success',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-primary',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
                },
                'colvis'
            ]
        });
    }
});
</script>