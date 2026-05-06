<?php
include('dbcon.php');

// --- DELETE LOGIC START ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Check if branch has products before deleting (Optional but recommended)
    // $check_sql = "SELECT id FROM product_stock WHERE branch_id = $delete_id LIMIT 1";
    
    $delete_query = "DELETE FROM branches WHERE branch_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Redirect to same page to clear the GET parameter and refresh the list
        header("Location: branch_list.php?msg=deleted");
        exit();
    } else {
        $error = "Error deleting record: " . mysqli_error($con);
    }
}
// --- DELETE LOGIC END ---

include('ini/header.php');
?>

<div class="container-fluid">

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Branch deleted successfully!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0">Branch List</h5>
            <a href="add_branch.php" class="btn btn-light btn-sm">Add New Branch</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center" id="dataTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Branch Name</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $sql = "SELECT * FROM branches ORDER BY branch_id DESC";
                        $res = mysqli_query($con, $sql);
                        $i = 1;

                        while ($row = mysqli_fetch_assoc($res)) {
                        ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['branch_name']); ?></td>
                                <td><?= htmlspecialchars($row['address']); ?></td>
                                <td>
                                    <a href="branch_edit.php?id=<?= $row['branch_id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <a href="branch_list.php?delete_id=<?= $row['branch_id']; ?>" 
                                       onclick="return confirm('Warning: Deleting this branch may affect related records. Are you sure?')" 
                                       class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
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

<?php include('ini/footer.php'); ?>