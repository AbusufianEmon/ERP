<?php
include('ini/header.php');
include('dbcon.php');

/* ===============================
    Fetch Pending Approvals
    (stock_manager_approval_status = 0)
================================ */
$sql = "
SELECT 
    pt.*, 
    p.name AS product_name, 
    p.code AS product_code,
    b1.branch_name AS original_from_name, 
    b2.branch_name AS original_to_name
FROM product_transfer pt
LEFT JOIN product p ON pt.product_id = p.id
LEFT JOIN branches b1 ON pt.from_branch = b1.branch_id
LEFT JOIN branches b2 ON pt.to_branch = b2.branch_id
WHERE pt.stock_manager_approval_status = 0
ORDER BY pt.created_at DESC
";

$result = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pending Transfer Approvals</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">List of Transfers Awaiting Approval</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center datatable-profit" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Transfer ID</th>
                            <th>Product Details</th>
                            <th>Lot No</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                if ($row['branch_to_branch_status'] == 1) {
                                    $display_from = $row['original_to_name'];
                                    $display_to   = $row['original_from_name'];
                                } else {
                                    $display_from = $row['original_from_name'];
                                    $display_to   = $row['original_to_name'];
                                }
                                ?>
                                <tr>
                                    <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                                    <td><span class="badge badge-secondary"><?php echo $row['transfer_id']; ?></span></td>
                                    <td class="text-left">
                                        <strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br>
                                        <small class="text-muted">Code: <?php echo $row['product_code']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['lot_no']); ?></td>
                                    <td><?php echo htmlspecialchars($display_from); ?></td>
                                    <td><?php echo htmlspecialchars($display_to); ?></td>
                                    <td><b class="text-primary"><?php echo $row['qty']; ?></b></td>
                                    <td><span class="badge badge-warning">Pending Approval</span></td>
                                    <td>
                                        <a href="view_transfer_details.php?transfer_id=<?php echo $row['transfer_id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
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
    setTimeout(function() {
        // Destroy if already initialized
        if ($.fn.DataTable.isDataTable('.datatable-profit')) {
            $('.datatable-profit').DataTable().destroy();
        }

        // Initialize with your exact parameters
        $('.datatable-profit').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: 'Ecxel',
                    className: 'btn btn-success mt-2'
                }
            ],
            "order": [[ 0, "desc" ]] 
        });
    }, 500);
</script>