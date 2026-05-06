<?php
include('dbcon.php');
include('ini/header.php'); 

// Set default dates or catch from POST
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-01'); 
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Master Sales & Due Report</h1>
        <div>
            <a href="#" class="btn btn-sm btn-success shadow-sm" onclick="window.print()">
                <i class="fas fa-print fa-sm text-white-50"></i> Print
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?php echo $from_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?php echo $to_date; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter fa-sm"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="customer_due_report_all.php" class="btn btn-secondary btn-block">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        // FIXED: Subquery used to get unique due per invoice before summing per branch
        $summary_sql = "SELECT branch_name, SUM(unique_due) as total_branch_due 
                        FROM (
                            SELECT b.branch_name, ds.branch_id, ds.invoice_no, MAX(ds.due_amount) as unique_due
                            FROM direct_sales ds
                            LEFT JOIN branches b ON ds.branch_id = b.branch_id
                            WHERE DATE(ds.created_at) BETWEEN '$from_date' AND '$to_date'
                            GROUP BY ds.invoice_no
                        ) as subquery
                        GROUP BY branch_id";
        
        $summary_res = mysqli_query($con, $summary_sql);
        while($s_row = mysqli_fetch_assoc($summary_res)){
            $branchName = $s_row['branch_name'] ?: "Main Office";
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo $branchName; ?> Due</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($s_row['total_branch_due'], 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Sales List: <?php echo date('d M Y', strtotime($from_date)); ?> to <?php echo date('d M Y', strtotime($to_date)); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center" id="masterSalesTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Branch</th>
                            <th>Customer Name</th>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FIXED: Added GROUP BY ds.invoice_no to ensure one row per invoice
                        $query = "SELECT ds.*, c.name as customer_real_name, b.branch_name 
                                  FROM direct_sales ds
                                  LEFT JOIN customer c ON ds.cus_id = c.cus_id
                                  LEFT JOIN branches b ON ds.branch_id = b.branch_id
                                  WHERE DATE(ds.created_at) BETWEEN '$from_date' AND '$to_date'
                                  GROUP BY ds.invoice_no 
                                  ORDER BY ds.created_at DESC";
                        
                        $result = mysqli_query($con, $query);
                        $total_all_due = 0;

                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $total_all_due += $row['due_amount'];
                                $status = ($row['due_amount'] <= 0) ? 
                                    '<span class="badge badge-success">Paid</span>' : 
                                    '<span class="badge badge-danger">Collection Pending</span>';
                                ?>
                                <tr>
                                    <td><?php echo $row['branch_name'] ?: 'Main Office'; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['customer_real_name'] ?: 'N/A'); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['customer_code']); ?></small>
                                    </td>
                                    <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                                    <td><span class="badge badge-light border"><?php echo $row['invoice_no']; ?></span></td>
                                    <td><?php echo number_format($row['total_price'], 2); ?></td>
                                    <td><?php echo number_format($row['paid_amount'], 2); ?></td>
                                    <td class="<?php echo ($row['due_amount'] > 0) ? 'text-danger font-weight-bold' : ''; ?>">
                                        <?php echo number_format($row['due_amount'], 2); ?>
                                    </td>
                                    <td><?php echo $status; ?></td>
                                    <td>
                                        <a href="sale_invoice.php?invoice_id=<?php echo $row['invoice_no']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="6" class="text-right">Total Aggregate Due:</th>
                            <th class="text-danger"><?php echo number_format($total_all_due, 2); ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('ini/footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#masterSalesTable').DataTable({
            dom: 'Bfrtip',
            "order": [[ 2, 'desc' ]], 
            buttons: [
                { extend: 'excelHtml5', footer: true, title: 'Customer_Due_Report' },
                { extend: 'pdfHtml5', footer: true, title: 'Customer_Due_Report', orientation: 'landscape' },
                { extend: 'print', footer: true }
            ],
            "pageLength": 50
        });
    });
</script>