<?php 
include('dbcon.php');
include('ini/header.php');

$current_branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

$sql = "
    SELECT 
        pt.*,
        p.name AS p_name,
        b1.branch_name AS original_from_name,
        b2.branch_name AS original_to_name
    FROM product_transfer pt
    LEFT JOIN product p ON pt.product_id = p.id
    LEFT JOIN branches b1 ON pt.from_branch = b1.branch_id
    LEFT JOIN branches b2 ON pt.to_branch = b2.branch_id
    WHERE pt.transfer_status = 1 
    ORDER BY pt.created_at DESC
";

$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-truck-loading"></i> Transfer Receive Pending
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center datatable-transfer" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Transfer ID</th>
                            <th>Product</th>
                            <th>Lot No</th>
                            <th>Qty</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        while ($data = mysqli_fetch_assoc($run)) { 
                            if ($data['branch_to_branch_status'] == 1) {
                                $display_from = $data['original_to_name'];
                                $display_to   = $data['original_from_name'];
                            } else {
                                $display_from = $data['original_from_name'];
                                $display_to   = $data['original_to_name'];
                            }
                        ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $data['transfer_id']; ?></td>
                            <td><?= htmlspecialchars($data['p_name']); ?></td>
                            <td><?= $data['lot_no']; ?></td>
                            <td><?= $data['qty']; ?></td>
                            <td><?= htmlspecialchars($display_from); ?></td>
                            <td><?= htmlspecialchars($display_to); ?></td>
                            <td>
                                <a href="view_transfer_details.php?transfer_id=<?= $data['transfer_id'];?>" class="btn btn-info btn-sm">
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

<?php include('ini/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
    // Using setTimeout exactly like your reference page to ensure initialization
    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('.datatable-transfer')) {
            $('.datatable-transfer').DataTable().destroy();
        }

        $('.datatable-transfer').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: 'Ecxel',
                    className: 'btn btn-success mt-2',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5, 6 ] // Exclude action column
                    }
                }
            ],
            "pageLength": 10,
            "responsive": true
        });
    }, 500);
</script>