<?php
include('dbcon.php');
include('ini/header.php');

// Handle Form Submission
if (isset($_POST['save_customer'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    
    // Cast to int to ensure database compatibility with INT type
    $customer_code = intval($_POST['customer_code']); 
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $ledger = 0; // Defaulting to 0

    // Validation: Check if 6 digits
    if ($customer_code < 100000 || $customer_code > 999999) {
        echo "<script>
                swal('Format Error!', 'Customer Code must be exactly 6 digits (e.g., 100201)!', 'warning');
              </script>";
    } else {
        // Basic Validation: Check if Customer Code or Phone already exists
        $check_query = "SELECT * FROM customer WHERE customer_code = $customer_code OR phone = '$phone' LIMIT 1";
        $check_res = mysqli_query($con, $check_query);

        if (mysqli_num_rows($check_res) > 0) {
            echo "<script>
                    swal('Error!', 'Customer Code or Phone Number already exists!', 'error');
                  </script>";
        } else {
            // Note: customer_code is passed without quotes in SQL because it is INT
            $sql = "INSERT INTO customer (`name`, `email`, `phone`, `customer_code`, `ledger`, `address`) 
                    VALUES ('$name', '$email', '$phone', $customer_code, '$ledger', '$address')";
            
            $run = mysqli_query($con, $sql);

            if ($run) {
                echo "<script>
                        swal('Success!', 'Customer added successfully!', 'success').then(() => {
                            window.location.href = 'view_customer.php';
                        });
                      </script>";
            } else {
                // If insertion fails, show the mysql error for debugging
                $error = mysqli_error($con);
                echo "<script>
                        swal('Database Error!', 'Error: $error', 'error');
                      </script>";
            }
        }
    }
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Customer</h1>
        <a href="view_customer.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list fa-sm text-white-50"></i> View Customer List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Customer Information</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" required placeholder="Category Name (Letters only)" 
                       pattern="^[a-zA-Z\s\-]+$" title="Numbers are not allowed in the product name" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">6-Digit Customer Code <span class="text-danger">*</span></label>
                                <input type="number" name="customer_code" class="form-control" placeholder="100001" min="100000" max="999999" required>
                                <small class="text-muted">Must be a number between 100000 and 999999</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" pattern="[0-9]{11}" placeholder="Enter Phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter Email">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Address</label>
                                <textarea name="address" class="form-control" rows="1" placeholder="Enter Address"></textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group text-right">
                            <button type="reset" class="btn btn-secondary px-4">Reset</button>
                            <button type="submit" name="save_customer" class="btn btn-success px-5">Save Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include('ini/footer.php'); ?>