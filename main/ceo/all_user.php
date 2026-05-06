<?php
include('ini/header.php');
include('dbcon.php');

// Fetch only approved users as per your original logic
$sql = "SELECT * FROM user WHERE status = 1";
$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Users Management</h1>
        <a href="u_request.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-user-clock fa-sm text-white-50"></i> Pending Requests
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Active Users List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="userListTable" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Address Details</th>
                            <th>Image</th>
                            <th>NID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($data = mysqli_fetch_assoc($run)) { ?>
                            <tr>
                                <td class="font-weight-bold"><?php echo $data['s_name']; ?></td>
                                <td class="text-left">
                                    <small>
                                        <i class="fa fa-envelope"></i> <?php echo $data['email']; ?><br>
                                        <i class="fa fa-phone"></i> <?php echo $data['phone']; ?>
                                    </small>
                                </td>
                                <td class="text-left">
                                    <small>
                                        <b>Village:</b> <?php echo $data['per_village']; ?><br>
                                        <b>Dist:</b> <?php echo $data['pdist']; ?>
                                    </small>
                                </td>
                                <td>
                                    <img src="../../user_img/<?php echo $data['image']; ?>" class="rounded shadow-sm" height="50" width="50" style="object-fit: cover;">
                                </td>
                                <td>
                                    <img src="../../user_img/nid/<?php echo $data['nid']; ?>" class="rounded shadow-sm" height="50" width="50" style="object-fit: cover;">
                                </td>
                                <td>
                                    <span class="badge badge-success px-3 py-2">Approved</span>
                                </td>
                                <td>
                                    <a href="view_user.php?id=<?php echo $data['id']; ?>" class="btn btn-info btn-circle btn-sm shadow-sm">
                                        <i class="fa fa-eye"></i>
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

<script>
    $(document).ready(function() {
        // Check if DataTable is already initialized to prevent errors
        if ($.fn.DataTable.isDataTable('#userListTable')) {
            $('#userListTable').DataTable().destroy();
        }

        $('#userListTable').DataTable({
            "dom": 'Bfrtip', // This enables the Buttons
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success btn-sm mr-2',
                    title: 'Active_Users_Report',
                    exportOptions: {
                        columns: [0, 1, 2, 5] // Only export Text columns (skip images/action)
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print List',
                    className: 'btn btn-dark btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 5]
                    }
                }
            ],
            "pageLength": 10,
            "order": [[0, "asc"]], // Sort by Name
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search user..."
            }
        });
    });
</script>