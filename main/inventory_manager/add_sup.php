<?php
include('ini/header.php');
?>

<h1 class="text-center">Add Supplier :</h1>
<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="container">
                <label class="form-group">Supplier Name :</label>
                <input type="text" name="sup_name" required="" placeholder="Supplier Name" class="form-control"><br>
                
                <label class="form-group">Supplier Address :</label>
                <input type="text" name="sup_add" required="" placeholder="Supplier Address" class="form-control"><br>
                
                <label class="form-group">Supplier Phone :</label>
                <input type="text" name="sup_phone" required="" placeholder="01XXXXXXXXX" pattern="[0-9]{11}" maxlength="11" title="Please enter exactly 11 digits" class="form-control"><br>
                
                <label class="form-group">Supplier Email :</label>
                <input type="email" name="sup_email" required="" placeholder="example@mail.com" class="form-control"><br>
                
                <label class="form-group">Supplier Photo :</label>
                <input type="file" name="sup_photo" class="form-control"><br>
                
                <input type="submit" name="submit" class="btn btn-success form-control">
            </form>
        </div>
    </div>
</div>

<?php
include('ini/footer.php');
include('dbcon.php');

if ((isset($_POST['submit']))) {
    // Sanitize inputs to prevent SQL Injection
    $sup_name = mysqli_real_escape_string($con, $_POST['sup_name']);
    $sup_add = mysqli_real_escape_string($con, $_POST['sup_add']);
    $sup_phone = mysqli_real_escape_string($con, $_POST['sup_phone']);
    $sup_email = mysqli_real_escape_string($con, $_POST['sup_email']);
    
    // PHP Server-side Validation
    $errors = [];

    // Validate Phone (exactly 11 digits)
    if (!preg_match('/^[0-9]{11}$/', $sup_phone)) {
        $errors[] = "Phone number must be exactly 11 digits.";
    }

    // Validate Email
    if (!filter_var($sup_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>swal('Validation Error', '$error', 'error');</script>";
        }
    } else {
        // Handle File Upload
        $sup_photo = $_FILES['sup_photo']['name'];
        $tmp_name = $_FILES['sup_photo']['tmp_name'];
        $upload_path = 'img/supplier/';
        
        // Ensure folder exists or handle empty photo if needed
        $upload_check = move_uploaded_file($tmp_name, $upload_path.$sup_photo);

        if ($upload_check == 0 && !empty($sup_photo)) {
            echo "<script>swal('Error', 'Failed to load files', 'error');</script>";
        } else {
            $sql = "INSERT INTO supplier(sup_name, sup_add, sup_phone, sup_email, sup_photo) VALUES('$sup_name','$sup_add', '$sup_phone', '$sup_email','$sup_photo')";
            $run = mysqli_query($con, $sql);
            
            if ($run) {
                ?>
                <script>
                   swal("Success!!", "Data inserted successfully", "success");
                </script>
                <?php
            } else {
                ?>
                <script>
                   swal("Error!!", "Database insertion failed", "error");
                </script>
                <?php
            }
        }
    }
}
?>