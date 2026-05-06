<?php 
include('ini/header.php');
include('dbcon.php');

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM corporate_customer WHERE corporate_id = $id";
    $run = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($run);
}

if(isset($_POST['update'])) {
    $c_name = mysqli_real_escape_string($con, $_POST['corporate_name']);
    $c_num  = mysqli_real_escape_string($con, $_POST['corporate_number']);
    $c_code = mysqli_real_escape_string($con, $_POST['corporate_code']);
    $c_mail = mysqli_real_escape_string($con, $_POST['corporate_email']);
    $c_addr = mysqli_real_escape_string($con, $_POST['corporate_address']);

    $update = "UPDATE corporate_customer SET 
               corporate_name='$c_name', 
               corporate_number='$c_num', 
               corporate_code='$c_code', 
               corporate_email='$c_mail', 
               corporate_address='$c_addr' 
               WHERE corporate_id = $id";

    if(mysqli_query($con, $update)) {
        echo "<script>alert('Updated Successfully'); window.location.href='all_corporate.php';</script>";
    } else {
        echo "<script>alert('Update Failed');</script>";
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Corporate Customer</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Corporate Name</label>
                        <input type="text" name="corporate_name" value="<?php echo htmlspecialchars($row['corporate_name']); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Corporate Number</label>
                        <input type="text" name="corporate_number" value="<?php echo htmlspecialchars($row['corporate_number']); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Corporate Code</label>
                        <input type="text" name="corporate_code" value="<?php echo htmlspecialchars($row['corporate_code']); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Corporate Email</label>
                        <input type="email" name="corporate_email" value="<?php echo htmlspecialchars($row['corporate_email']); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Address</label>
                        <textarea name="corporate_address" class="form-control" rows="3"><?php echo htmlspecialchars($row['corporate_address']); ?></textarea>
                    </div>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Update Customer</button>
                <a href="all_corporate.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>