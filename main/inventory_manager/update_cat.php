<?php
include('ini/header.php');
include('dbcon.php');

// 1. Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $cat_id = mysqli_real_escape_string($con, $_GET['id']);
    
    // Fetch existing data for this category
    $fetch_sql = "SELECT * FROM category WHERE cat_id = '$cat_id'";
    $fetch_run = mysqli_query($con, $fetch_sql);
    $data = mysqli_fetch_assoc($fetch_run);

    if (!$data) {
        echo "<script>alert('Category not found!'); window.location='all_category.php';</script>";
        exit;
    }
} else {
    header('location:all_category.php');
    exit;
}

// 2. Handle the Update Logic
if (isset($_POST['update_btn'])) {
    $new_cat_name = mysqli_real_escape_string($con, $_POST['cat_name']);

    $update_sql = "UPDATE category SET cat_name = '$new_cat_name' WHERE cat_id = '$cat_id'";
    $update_run = mysqli_query($con, $update_sql);

    if ($update_run) {
        echo "<script>
                alert('Category Updated Successfully!');
                window.location='all_category.php'; 
              </script>";
    } else {
        echo "<script>alert('Update Failed. Please try again.');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Category</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="cat_name">Category Name</label>
                            <input type="text" name="cat_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['cat_name']); ?>" required>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" name="update_btn" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Changes
                            </button>
                            <a href="all_category.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>