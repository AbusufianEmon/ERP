<?php 
include('ini/header.php');
include('dbcon.php');
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Corporate Head Approval Pending</h1>
    </div>

    <div class="card shadow mb-4 border-left-warning">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">Waiting for Approval</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="pendingTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Corporate Name</th>
                            <th>Number</th>
                            <th>Code</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Only fetch where status is 0
                        $query = "SELECT * FROM corporate_customer WHERE accounts_approvel_status = 0 ORDER BY corporate_id DESC";
                        $run = mysqli_query($con, $query);
                        
                        while($row = mysqli_fetch_assoc($run)) {
                        ?>
                            <tr>
                                <td><?php echo $row['corporate_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_address']); ?></td>
                                <td>
                                    <a href="view_corporate.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="approve_corporate_action.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-success btn-sm" title="Approve Now">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="edit_corporate.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_corporate.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
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

<script>
$(document).ready(function() {
    $('#pendingTable').DataTable();
});
</script>

<?php include('ini/footer.php'); ?>