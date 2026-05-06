<?php
include('ini/header.php');
include('dbcon.php');
?>

<h1 class="text-center text-primary">Add Corporate Customer</h1>

<div class="row">
    <div class="col-md-6 offset-md-3 card shadow" style="background: white; font-weight: bold;">
        <div class="card-body">

            <form method="post" action="" class="container">

                <label class="form-group">Corporate Name :</label>
                <input type="text" name="corporate_name" required class="form-control" 
                       title="Name must contain letters (numbers allowed but not only numbers)" 
                       placeholder="e.g. Acme Corp 101"><br>

                <label class="form-group">Corporate Number :</label>
                <input type="text" name="corporate_number" required class="form-control" 
                       pattern="01[0-9]{9}" maxlength="11" 
                       placeholder="017XXXXXXXX" title="Must be 11 digits starting with 01"><br>

                <label class="form-group">Corporate Address :</label>
                <textarea name="corporate_address" required class="form-control"></textarea><br>

                <label class="form-group">Corporate Code :</label>
                <input type="text" name="corporate_code" required class="form-control" placeholder="C-1002"><br>

                <label class="form-group">Corporate Email :</label>
                <input type="email" name="corporate_email" required class="form-control" placeholder="info@company.com"><br>

                <input type="submit" name="submit" value="Add Corporate"
                       class="btn btn-success form-control">

            </form>

        </div>
    </div>
</div>

<?php
if (isset($_POST['submit'])) {

    $corporate_name    = trim($_POST['corporate_name']);
    $corporate_number  = trim($_POST['corporate_number']);
    $corporate_address = trim($_POST['corporate_address']);
    $corporate_code    = trim($_POST['corporate_code']);
    $corporate_email   = trim($_POST['corporate_email']);

    /* 🛑 VALIDATION LOGIC */

    // 1. Name: Check if it's NOT just numbers (must have at least one letter)
    if (!preg_match("/[a-zA-Z]/", $corporate_name)) {
        echo "<script>swal('Invalid Name!', 'Corporate Name must contain at least some letters.', 'warning');</script>";
    }
    // 2. Number: Exactly 11 digits and starts with 01
    elseif (!preg_match("/^01[0-9]{9}$/", $corporate_number)) {
        echo "<script>swal('Phone Error!', 'Number must be 11 digits and start with 01.', 'warning');</script>";
    }
    // 3. Email: Valid format check
    elseif (!filter_var($corporate_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>swal('Email Error!', 'Please enter a valid email address.', 'warning');</script>";
    }
    else {
        /* 🔍 CHECK DUPLICATE */
        $check_sql = "SELECT corporate_id FROM corporate_customer WHERE corporate_number = ? OR corporate_code = ?";
        $check_stmt = $con->prepare($check_sql);
        $check_stmt->bind_param("ss", $corporate_number, $corporate_code);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>
                swal('Duplicate Entry!', 'Corporate Number or Corporate Code already exists.', 'error');
            </script>";
        } else {
            /* ✅ INSERT DATA */
            $insert_sql = "INSERT INTO corporate_customer (corporate_name, corporate_number, corporate_address, corporate_code, corporate_email) VALUES (?, ?, ?, ?, ?)";

            $stmt = $con->prepare($insert_sql);
            $stmt->bind_param('sssss', $corporate_name, $corporate_number, $corporate_address, $corporate_code, $corporate_email);

            if ($stmt->execute()) {
                echo "<script>
                    swal('Success!', 'Corporate customer added successfully!', 'success')
                    .then(() => { window.location.href = 'view_corporate.php'; });
                </script>";
            } else {
                echo "<script>swal('Error!', 'Failed to add corporate customer.', 'error');</script>";
            }
        }
    }
}
?>

<?php include('ini/footer.php'); ?>