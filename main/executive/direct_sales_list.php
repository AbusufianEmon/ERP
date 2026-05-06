<?php
include('ini/header.php');
include('dbcon.php');


if (!isset($_GET['branch_id'])) {
    echo "<div class='alert alert-danger'>Branch not selected</div>";
    exit;
}

$branch_id = intval($_GET['branch_id']);

// Fetch branch name
$branch_sql = "SELECT branch_name FROM branches WHERE branch_id = $branch_id LIMIT 1";
$branch_res = mysqli_query($con, $branch_sql);
$branch_row = mysqli_fetch_assoc($branch_res);
$branch_name = $branch_row['branch_name'] ?? "Unknown Branch";
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Direct Sales List (Branch: <?php echo htmlspecialchars($branch_name); ?>)
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Qty</th>
                            <th>Customer</th>
                            <th>Customer Code</th>
                            <th>Total Price</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // FIX: Added ds.invoice_no to the SELECT statement
                        $sql = "
                            SELECT 
                                ds.sale_id,
                                ds.invoice_no,
                                ds.product_name,
                                ds.product_code,
                                ds.qty,
                                ds.total_price,
                                ds.created_at,
                                ds.customer_code,
                                c.name AS customer_name
                            FROM direct_sales ds
                            LEFT JOIN customer c ON ds.cus_id = c.cus_id
                            WHERE ds.branch_id = $branch_id
                            ORDER BY ds.sale_id DESC
                        ";

                        $result = mysqli_query($con, $sql);
                        $i = 1;

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                    <td><?php echo $row['qty']; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_code']); ?></td>
                                    <td><?php echo number_format($row['total_price'], 2); ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="sale_invoice.php?invoice_id=<?php echo urlencode($row['invoice_no']); ?>&branch_id=<?php echo $branch_id; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="9" class="text-center text-danger">
                                    No sales found for this branch
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#example').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'print']
    });
});
</script>

<?php include('ini/footer.php'); ?>