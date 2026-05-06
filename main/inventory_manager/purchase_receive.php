<?php
include('ini/header.php');
include('dbcon.php');
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4 text-danger">Purchase Receive Pending</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="example">
            <thead class="thead-dark">
                <tr>
                    <th>Lot No</th>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch unique lot_no with status = 0
                $sql = "SELECT po.lot_no, po.invoice_no, po.created_at
                        FROM purchase_order po
                        WHERE po.status = 0
                        GROUP BY po.lot_no
                        ORDER BY MIN(po.created_at) DESC";

                $res = mysqli_query($con, $sql);

                if ($res && mysqli_num_rows($res) > 0) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['lot_no']) . "</td>
                                <td>" . htmlspecialchars($row['invoice_no']) . "</td>
                                <td>" . htmlspecialchars($row['created_at']) . "</td>
                                <td>
                                    <a href='view_lot.php?lot_no=" . urlencode($row['lot_no']) . "' class='btn btn-success btn-sm'><i class='fa fa-eye'></i></a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No pending purchase orders.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.dataTables.min.css">

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'print'
            ]
        });
    });
</script>

<?php
include('ini/footer.php');
?>
