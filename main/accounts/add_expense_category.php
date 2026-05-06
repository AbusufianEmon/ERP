<?php 
include('ini/header.php'); // Includes session, auth check, and db connection

// Handle Form Submission (Add Category)
if (isset($_POST['add_category'])) {
    $cat_name = mysqli_real_escape_string($con, $_POST['e_cat_name']);
    
    if (!empty($cat_name)) {
        $insert_sql = "INSERT INTO ex_category (e_cat_name) VALUES ('$cat_name')";
        if (mysqli_query($con, $insert_sql)) {
            echo "<script>
                swal('Success!', 'New category added successfully', 'success');
            </script>";
        } else {
            echo "<script>
                swal('Error!', 'Something went wrong', 'error');
            </script>";
        }
    }
}

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM ex_category WHERE exc_id = $del_id";
    if (mysqli_query($con, $delete_sql)) {
        echo "<script>
            window.location.href='add_expense_category.php?deleted=1';
        </script>";
    }
}

// Success alert after redirect
if (isset($_GET['deleted'])) {
    echo "<script>swal('Deleted!', 'Category has been removed', 'success');</script>";
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Expense Categories</h1>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add New Category</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="small font-weight-bold">Category Name</label>
                            <input type="text" name="e_cat_name" class="form-control" placeholder="e.g. Office Supplies" required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary btn-block">
                            <i class="fas fa-plus fa-sm"></i> Save Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Existing Categories</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="categoryTable" width="100%" cellspacing="0">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th width="15%">ID</th>
                                    <th>Category Name</th>
                                    <th width="20%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $query = mysqli_query($con, "SELECT * FROM ex_category ORDER BY exc_id DESC");
                                while($row = mysqli_fetch_assoc($query)) {
                                ?>
                                <tr>
                                    <td><?php echo $row['exc_id']; ?></td>
                                    <td class="font-weight-bold text-gray-800"><?php echo $row['e_cat_name']; ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['exc_id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#categoryTable').DataTable({
        "dom": '<"row"<"col-md-12"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        "lengthChange": false,
        "pageLength": 10,
        "language": {
            "search": "Search Categories:"
        }
    });
});

// Delete Confirmation function
function confirmDelete(id) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this category!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            window.location.href = "add_expense_category.php?delete_id=" + id;
        }
    });
}
</script>