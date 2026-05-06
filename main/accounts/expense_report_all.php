<?php 
include('ini/header.php'); // This already starts the session and checks auth

// Capture Search/Filters
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?? '';
$branch_filter = $_POST['branch_id'] ?? '';

// Build the Query
$where_clauses = [];
if(!empty($start_date) && !empty($end_date)){
    $where_clauses[] = "e.expense_date BETWEEN '$start_date' AND '$end_date'";
}
if(!empty($branch_filter)){
    $where_clauses[] = "e.branch_id = '$branch_filter'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// Main query
$sql = "SELECT e.*, b.branch_name 
        FROM expenses e 
        LEFT JOIN branches b ON e.branch_id = b.branch_id 
        $where_sql 
        ORDER BY e.expense_date DESC";
$res = mysqli_query($con, $sql);

// Summary Total
$total_amt_sql = "SELECT SUM(amount) as total FROM expenses e $where_sql";
$total_res = mysqli_fetch_assoc(mysqli_query($con, $total_amt_sql));
$grand_total = $total_res['total'] ?? 0;
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Master Expense Report</h1>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Accumulated Expense</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($grand_total, 2); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calculator fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Filter Records</h6></div>
        <div class="card-body">
            <form method="POST" class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="small">Branch</label>
                    <select name="branch_id" class="form-control form-control-sm">
                        <option value="">All Branches</option>
                        <?php 
                        $b_query = mysqli_query($con, "SELECT * FROM branches");
                        while($b = mysqli_fetch_assoc($b_query)){
                            $sel = ($branch_filter == $b['branch_id']) ? 'selected' : '';
                            echo "<option value='".$b['branch_id']."' $sel>".$b['branch_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-search fa-sm"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="masterExpenseTable" width="100%" cellspacing="0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>ID</th>
                            <th>Branch</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($res)) { ?>
                        <tr>
                            <td><?php echo $row['expense_id']; ?></td>
                            <td class="font-weight-bold"><?php echo $row['branch_name']; ?></td>
                            <td><?php echo date('d-M-Y', strtotime($row['expense_date'])); ?></td>
                            <td><span class="badge badge-info"><?php echo $row['expense_category']; ?></span></td>
                            <td><?php echo $row['vendor_name']; ?></td>
                            <td class="text-danger font-weight-bold"><?php echo number_format($row['amount'], 2); ?></td>
                            <td><small><?php echo $row['description']; ?></small></td>
                            <td class="text-center">
                                <?php if(!empty($row['invoice_pic'])): ?>
                                    <a href="../branch_manager/expense_invoice/<?php echo $row['invoice_pic']; ?>" target="_blank" class="btn btn-xs btn-outline-secondary">
                                        <i class="fas fa-image"></i>
                                    </a>
                                <?php endif; ?>
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
    // Check if DataTable is already initialized and destroy it to avoid conflicts
    if ($.fn.DataTable.isDataTable('#masterExpenseTable')) {
        $('#masterExpenseTable').DataTable().destroy();
    }

    $('#masterExpenseTable').DataTable({
        // "l" is removed from DOM to hide the "Show entries" dropdown
        "dom": '<"dt-buttons mb-3"B><"row"<"col-md-12"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm mt-1',
                title: 'Expense_Report_<?php echo date("d_m_Y"); ?>',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print Report',
                className: 'btn btn-info btn-sm mt-1',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
            }
        ],
        "lengthChange": false, // Formally disables the ability to change page length
        "pageLength": 25,      // Optional: Set a default number of rows to show per page
        "order": [[ 2, "desc" ]],
        "language": {
            "search": "Quick Search:"
        }
    });
});
</script>