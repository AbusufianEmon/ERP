<?php
include('ini/header.php');
include('dbcon.php');

// Fetch transfer details
$sql = "
SELECT 
    pt.transfer_id,
    p.code AS product_code,
    p.name AS product_name,
    b_from.branch_name AS from_branch,
    b_to.branch_name AS to_branch,
    pt.qty,
    pt.transfer_date
FROM product_transfer pt
JOIN product p ON pt.product_id = p.id
JOIN branches b_from ON pt.from_branch_id = b_from.branch_id
JOIN branches b_to ON pt.to_branch_id = b_to.branch_id
ORDER BY pt.transfer_date DESC
";

$result = mysqli_query($con, $sql);
?>

<div class="row">
    <div class="col-md-6 text-left">
        <a href="transfer_stock.php" class="btn btn-warning btn-sm font-weight-bold text-primary">
            New Stock Transfer
        </a>
    </div>
    <div class="col-md-6 text-right">
        <a href="view_stock.php" class="btn btn-warning btn-sm font-weight-bold text-primary">
            View Stock
        </a>
    </div>
</div>
<br><br>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Transfer History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Date</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_assoc($result)){ 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['from_branch']); ?></td>
                                    <td><?php echo htmlspecialchars($row['to_branch']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['qty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['transfer_date']); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" class="text-center text-danger">No transfer records found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.dataTables.min.css">

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'print'
            ]
        });
    });
</script>

<?php include('ini/footer.php'); ?>
