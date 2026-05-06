<?php
include('ini/header.php');
include('dbcon.php');

// 1. Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Branch ID</div>";
    include('ini/footer.php');
    exit;
}

$branch_id = intval($_GET['id']);

/* ===========================
   Handle Update Request
=========================== */
if (isset($_POST['update'])) {
    // Sanitize inputs
    $branch_name = mysqli_real_escape_string($con, $_POST['branch_name']);
    $address     = mysqli_real_escape_string($con, $_POST['address']);

    // Prepared query string
    $update_sql = "UPDATE branches 
                   SET branch_name = '$branch_name', 
                       address = '$address' 
                   WHERE branch_id = $branch_id";

    if (mysqli_query($con, $update_sql)) {
        // Success: Using a standard redirect if SweetAlert fails, 
        // but keeping your SweetAlert logic as primary.
        echo "
        <script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script>
        <script>
            swal('Success','Branch Updated Successfully','success')
            .then(() => { window.location='branch_list.php'; });
        </script>";
    } else {
        // Error: Show the actual SQL error for debugging
        $db_error = mysqli_error($con);
        echo "
        <script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script>
        <script>
            swal('Error','Update Failed: $db_error','error');
        </script>";
    }
}

/* ===========================
   Fetch Current Branch Info
=========================== */
$res = mysqli_query($con, "SELECT * FROM branches WHERE branch_id = $branch_id LIMIT 1");

if (!$res || mysqli_num_rows($res) == 0) {
    echo "<div class='alert alert-danger'>Branch Not Found in Database</div>";
    include('ini/footer.php');
    exit;
}

$row = mysqli_fetch_assoc($res);
?>

<div class="container">
    <div class="card shadow mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Edit Branch: <?= htmlspecialchars($row['branch_name']); ?></h5>
        </div>

        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label class="font-weight-bold">Branch Name</label>
                    <input type="text" 
                           name="branch_name" 
                           value="<?= htmlspecialchars($row['branch_name']); ?>" 
                           class="form-control" 
                           required>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold">Address</label>
                    <textarea name="address" 
                              class="form-control" 
                              rows="3" 
                              required><?= htmlspecialchars($row['address']); ?></textarea>
                </div>

                <div class="border-top pt-3 text-right">
                    <a href="branch_list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="update" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>