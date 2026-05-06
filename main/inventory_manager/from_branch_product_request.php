<?php 
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['branch_id'])) {
    echo "<div class='alert alert-danger'>Branch not selected</div>";
    exit;
}

$branch_id = intval($_GET['branch_id']);

$sql = "
    SELECT 
        pt.*,
        p.name AS product_name,
        b.branch_name AS from_branch_name
    FROM product_transfer pt
    LEFT JOIN product p ON pt.product_id = p.id
    LEFT JOIN branches b ON pt.from_branch = b.branch_id
    WHERE pt.to_branch = '$branch_id'
      AND pt.stock_manager_approval_status = 1
      AND pt.transfer_status = 0
    ORDER BY pt.created_at DESC
";

$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Branch Product Requests</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>Transfer ID</td>
                            <td>Product</td>
                            <td>Lot No</td>
                            <td>Qty</td>
                            <td>Buy Price</td>
                            <td>Sell Price</td>
                            <td>From Branch</td>
                            <td>Date</td>
                            <td>Action</td>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <td>#</td>
                            <td>Transfer ID</td>
                            <td>Product</td>
                            <td>Lot No</td>
                            <td>Qty</td>
                            <td>Buy Price</td>
                            <td>Sell Price</td>
                            <td>From Branch</td>
                            <td>Date</td>
                            <td>Action</td>
                        </tr>
                    </tfoot>

                    <tbody>
                        <?php 
                        $i = 1;
                        while ($data = mysqli_fetch_assoc($run)) { 
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $data['transfer_id']; ?></td>
                            <td><?php echo htmlspecialchars($data['product_name']); ?></td>
                            <td><?php echo $data['lot_no']; ?></td>
                            <td><?php echo $data['qty']; ?></td>
                            <td><?php echo $data['buy_price']; ?></td>
                            <td><?php echo $data['sell_price']; ?></td>
                            <td><?php echo htmlspecialchars($data['from_branch_name']); ?></td>
                            <td><?php echo $data['created_at']; ?></td>
                            <td>
                                <a href="view_product_req.php?transfer_id=<?php echo $data['transfer_id']; ?>&branch_id=<?php echo $branch_id; ?>" 
   class="btn btn-info btn-sm">
   <i class="fa fa-eye"></i>
</a>

                            </td>
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
        buttons: ['copy', 'csv', 'print']
    });
});
</script>

<?php include('ini/footer.php'); ?>
