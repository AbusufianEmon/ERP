<?php 
include('ini/header.php');
include('dbcon.php');
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">All Corporate Customers</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Corporate Customer List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="corporateTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Corporate Name</th>
                            <th>Number</th>
                            <th>Code</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query = "SELECT * FROM corporate_customer WHERE accounts_approvel_status = 1";
                        $run = mysqli_query($con, $query);
                        
                        while($row = mysqli_fetch_assoc($run)) {
                            
                            
                        ?>
                            <tr>
                                <td><?php echo $row['corporate_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['corporate_email']); ?></td>
                                <td>
                                    <a href="view_corporate.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_corporate.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_corporate.php?id=<?php echo $row['corporate_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this?')">
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
    $('#corporateTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
});
</script>

<?php include('ini/footer.php'); ?>