<?php 
include('ini/header.php');
include('dbcon.php');

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM corporate_customer WHERE corporate_id = $id";
    $run = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($run);

    if(!$row) {
        echo "<script>alert('Customer not found'); window.location.href='all_corporate.php';</script>";
        exit();
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Corporate Customer Details</h1>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo htmlspecialchars($row['corporate_name']); ?></h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Corporate ID</th>
                    <td><?php echo $row['corporate_id']; ?></td>
                </tr>
                <tr>
                    <th>Corporate Name</th>
                    <td><?php echo htmlspecialchars($row['corporate_name']); ?></td>
                </tr>
                <tr>
                    <th>Contact Number</th>
                    <td><?php echo htmlspecialchars($row['corporate_number']); ?></td>
                </tr>
                <tr>
                    <th>Corporate Code</th>
                    <td><?php echo htmlspecialchars($row['corporate_code']); ?></td>
                </tr>
                <tr>
                    <th>Email Address</th>
                    <td><?php echo htmlspecialchars($row['corporate_email']); ?></td>
                </tr>
                <tr>
                    <th>Office Address</th>
                    <td><?php echo htmlspecialchars($row['corporate_address']); ?></td>
                </tr>
                <tr>
                    <th>Approval Status</th>
                    <td>
                        <?php echo ($row['accounts_approvel_status'] == 1) ? 
                        '<span class="badge badge-success">Approved</span>' : 
                        '<span class="badge badge-warning">Pending</span>'; ?>
                    </td>
                </tr>
            </table>
            <a href="all_corporate.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>