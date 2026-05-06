<?php
include('ini/header.php');
include('dbcon.php');
?>

<div class="container-fluid">
    <div class="card shadow mb-4">

        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Approved Corporate Customer List
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Corporate Name</th>
                            <th>Number</th>
                            <th>Code</th>
                            <th>Email</th>
                            <th>Address</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $sql = "
                            SELECT * 
                            FROM corporate_customer
                            WHERE accounts_approvel_status = 1
                            ORDER BY corporate_id DESC
                        ";
                        $result = mysqli_query($con, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            $i = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($row['corporate_name']); ?></td>
                                    <td><?= htmlspecialchars($row['corporate_number']); ?></td>
                                    <td><?= htmlspecialchars($row['corporate_code']); ?></td>
                                    <td><?= htmlspecialchars($row['corporate_email']); ?></td>
                                    <td><?= htmlspecialchars($row['corporate_address']); ?></td>
                                    
                                </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="8" class="text-center text-danger">
                                    No approved corporate customers found
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script>
$(document).ready(function () {
    $('#example').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'print']
    });
});
</script>

<?php include('ini/footer.php'); ?>
