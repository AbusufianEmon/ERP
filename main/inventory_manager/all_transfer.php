<?php
include('dbcon.php');
include('ini/header.php');


// Fetch all transfers where the current branch is either the sender or receiver
$query = "SELECT pt.*, 
          b1.branch_name as from_branch_name, 
          b2.branch_name as to_branch_name,
          p.name
          FROM product_transfer pt
          LEFT JOIN branches b1 ON pt.from_branch = b1.branch_id
          LEFT JOIN branches b2 ON pt.to_branch = b2.branch_id
          LEFT JOIN product p ON pt.product_id = p.id
          ORDER BY pt.created_at DESC";

$result = mysqli_query($con, $query);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transfer Reports</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="transferTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Transfer ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            // LOGIC: If branch_to_branch_status = 1, flip the branches
                            if ($row['branch_to_branch_status'] == 1) {
                                $display_from = $row['to_branch_name'];
                                $display_to   = $row['from_branch_name'];
                            } else {
                                $display_from = $row['from_branch_name'];
                                $display_to   = $row['to_branch_name'];
                            }

                            // Status Logic
                            $status_badge = '';
                            if ($row['transfer_status'] == 0) {
                                $status_badge = '<span class="badge badge-warning">Requested</span>';
                            } elseif ($row['transfer_status'] == 1) {
                                $status_badge = '<span class="badge badge-info">Shipped</span>';
                            } elseif ($row['transfer_status'] == 2) {
                                $status_badge = '<span class="badge badge-success">Received</span>';
                            }
                        ?>
                        <tr>
                            <td><?php echo $row['transfer_id']; ?></td>
                            <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <b><?php echo $row['name']; ?></b><br>
                                <small>Lot: <?php echo $row['lot_no']; ?></small>
                            </td>
                            <td><?php echo $display_from; ?></td>
                            <td><?php echo $display_to; ?></td>
                            <td><?php echo $row['qty']; ?></td>
                            <td><?php echo $status_badge; ?></td>
                            <td class="text-center">
                                <a href="view_transfer_details.php?transfer_id=<?php echo $row['transfer_id']; ?>" class="btn btn-sm btn-primary">
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
    $('#transferTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script>

<?php include('ini/footer.php'); ?>