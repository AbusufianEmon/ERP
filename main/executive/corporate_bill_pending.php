<?php 
include('ini/header.php'); 
include('dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = intval($_SESSION['id']); 
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

// Fetch User and Branch Data for the Page Header
$sql = "SELECT u.*, b.branch_name 
        FROM user u 
        LEFT JOIN branches b ON u.branch_id = b.branch_id 
        WHERE u.id = $id";
$exe = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($exe);

$display_branch_name = $data['branch_name'] ?? 'All Branches';
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Corporate Bill Pending 
            <span class="text-primary">(Branch: <?php echo $display_branch_name; ?>)</span>
        </h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pending Collections List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Branch</th> <th>Corporate Name</th>
                            <th>Sale Date</th>
                            <th>Collection Deadline</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Aging</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Updated Query to JOIN branches table to get branch_name for each row
                        $query = "SELECT cs.*, b.branch_name 
                                  FROM corporate_sales cs
                                  LEFT JOIN branches b ON cs.branch_id = b.branch_id
                                  WHERE cs.branch_id = '$branch_id' 
                                  AND cs.bill_collection_status = 0";
                        
                        $result = mysqli_query($con, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $sale_date = $row['created_at'];
                                $collection_deadline = $row['bill_collection_date'];
                                
                                $today = date('Y-m-d');
                                $datetime1 = new DateTime($today);
                                $datetime2 = new DateTime($collection_deadline);
                                
                                $interval = $datetime1->diff($datetime2);
                                $days_diff = $interval->format('%r%a');

                                if ($days_diff < 0) {
                                    $status_label = '<span class="badge badge-danger">Collection Date Over</span>';
                                    $row_class = 'table-danger text-danger font-weight-bold';
                                    $days_text = abs($days_diff) . " Days Overdue";
                                } elseif ($days_diff == 0) {
                                    $status_label = '<span class="badge badge-warning">Due Today</span>';
                                    $row_class = 'table-warning text-dark';
                                    $days_text = "Due Today";
                                } else {
                                    $status_label = '<span class="badge badge-info">Pending</span>';
                                    $row_class = '';
                                    $days_text = $days_diff . " Days Remaining";
                                }
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo $row['corporate_sales_invoice_id']; ?></td>
                                <td><?php echo $row['branch_name']; ?></td> <td><?php echo $row['corporate_name']; ?></td>
                                <td><?php echo date('d-M-Y', strtotime($sale_date)); ?></td>
                                <td><?php echo date('d-M-Y', strtotime($collection_deadline)); ?></td>
                                <td><?php echo number_format($row['selling_price'], 2); ?></td>
                                <td><?php echo $row['qty']; ?></td>
                                <td><?php echo $days_text; ?></td>
                                <td><?php echo $status_label; ?></td>
                                <td class="text-center">
                                    <a href="corporate_bill_collection_view.php?invoice_id=<?php echo $row['corporate_sales_invoice_id']; ?>&branch_id=<?php echo $branch_id; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-money"></i> Collect
                                    </a>
                                </td>
                            </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>No pending bills found for this branch.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }

        $('#dataTable').DataTable({
            "retrieve": true,
            "dom": 'Bfrtip',
            "buttons": ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
            "order": [[4, 'asc']] // Updated index because of new column
        });
    });
</script>