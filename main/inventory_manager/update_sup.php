<?php 
$idd = $_GET['id'];
include('ini/header.php');
include('dbcon.php');

// Fetch existing data
$sql = "SELECT * FROM supplier WHERE id = $idd";
$run = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($run);
?>

<h1 class="text-center">Update Supplier :</h1>
<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="container">
                <label class="form-group">Supplier Name :</label>
                <input type="text" name="sup_name" required="" value="<?php echo htmlspecialchars($data['sup_name']) ?>" class="form-control"><br>
                
                <label class="form-group">Supplier Address :</label>
                <input type="text" name="sup_add" required="" value="<?php echo htmlspecialchars($data['sup_add']) ?>" class="form-control"><br>
                
                <label class="form-group">Supplier Phone :</label>
                <input type="text" name="sup_phone" required="" value="<?php echo htmlspecialchars($data['sup_phone']) ?>" pattern="[0-9]{11}" maxlength="11" title="Please enter exactly 11 digits" class="form-control"><br>
                
                <label class="form-group">Supplier Email :</label>
                <input type="email" name="sup_email" required="" value="<?php echo htmlspecialchars($data['sup_email']) ?>" class="form-control"><br>
                
                <label class="form-group">Supplier Photo (Leave blank to keep current) :</label>
                <input type="file" name="sup_photo" class="form-control"><br>
                
                <input type="submit" name="submit" class="btn btn-success form-control" value="Update Supplier">
            </form>
        </div>
    </div>
</div>

<?php
include('ini/footer.php');

if (isset($_POST['submit'])) {
    // Sanitize Inputs
    $sup_name = mysqli_real_escape_string($con, $_POST['sup_name']);
    $sup_add = mysqli_real_escape_string($con, $_POST['sup_add']);
    $sup_phone = mysqli_real_escape_string($con, $_POST['sup_phone']);
    $sup_email = mysqli_real_escape_string($con, $_POST['sup_email']);
    
    // Server-side Validation
    if (!filter_var($sup_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid Email Format');</script>";
    } elseif (!preg_match('/^[0-9]{11}$/', $sup_phone)) {
        echo "<script>alert('Phone number must be exactly 11 digits');</script>";
    } else {
        $sup_photo = $_FILES['sup_photo']['name'];
        $tmp_name = $_FILES['sup_photo']['tmp_name'];
        $upload_path = 'img/supplier/';

        // Check if a new photo is uploaded
        if (!empty($sup_photo)) {
            if (move_uploaded_file($tmp_name, $upload_path . $sup_photo)) {
                // Update with new photo
                $sql = "UPDATE supplier SET sup_name = '$sup_name', sup_add = '$sup_add', sup_phone = '$sup_phone', sup_email = '$sup_email', sup_photo = '$sup_photo' WHERE id = $idd";
            } else {
                echo "<script>alert('Failed to upload new photo');</script>";
                exit;
            }
        } else {
            // Update without changing the photo
            $sql = "UPDATE supplier SET sup_name = '$sup_name', sup_add = '$sup_add', sup_phone = '$sup_phone', sup_email = '$sup_email' WHERE id = $idd";
        }

        $run = mysqli_query($con, $sql);
        if ($run) {
            echo "
            <script>
                window.alert('Supplier Updated Successfully');
                window.open('view_sup.php','_self');
            </script>";
        } else {
            echo "<script>alert('Database Error: Unable to update');</script>";
        }
    }
}
?>