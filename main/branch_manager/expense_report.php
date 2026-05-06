<?php 
include('dbcon.php');

$url_branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

// --- PART 1: FETCH DATA FOR EDITING ---
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_query = mysqli_query($con, "SELECT * FROM expenses WHERE expense_id = '$edit_id'");
    $edit_data = mysqli_fetch_assoc($edit_query);
}

// --- PART 2: PROCESS UPDATE ACTION ---
if (isset($_POST['update_expense'])) {
    $id = intval($_POST['expense_id']);
    $date = $_POST['expense_date'];
    // Capture the category from the dropdown
    $cat = mysqli_real_escape_string($con, $_POST['expense_category']);
    $ven = mysqli_real_escape_string($con, $_POST['vendor_name']);
    $amt = $_POST['amount'];
    $desc = mysqli_real_escape_string($con, $_POST['description']);
    
    $filename = $_POST['old_pic'];
    if (!empty($_FILES['invoice_pic']['name'])) {
        $target_dir = "expense_invoice/";
        $filename = "INV_" . time() . "_" . $_FILES['invoice_pic']['name'];
        move_uploaded_file($_FILES['invoice_pic']['tmp_name'], $target_dir . $filename);
    }

    $update_sql = "UPDATE expenses SET 
                   expense_date='$date', expense_category='$cat', 
                   vendor_name='$ven', amount='$amt', 
                   description='$desc', invoice_pic='$filename' 
                   WHERE expense_id='$id'";
    
    if (mysqli_query($con, $update_sql)) {
        echo "<script>alert('Updated Successfully'); window.location.href='expense_report.php?branch_id=$url_branch_id';</script>";
    }
}

// --- PART 3: MAIN LISTING LOGIC ---
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?? '';

$where_clause = "WHERE branch_id = '$url_branch_id'";
if(!empty($start_date) && !empty($end_date)){
    $where_clause .= " AND expense_date BETWEEN '$start_date' AND '$end_date'";
}

$sql = "SELECT * FROM expenses $where_clause ORDER BY expense_date DESC";
$res = mysqli_query($con, $sql);

$total_amt_sql = "SELECT SUM(amount) as total FROM expenses $where_clause";
$total_res = mysqli_fetch_assoc(mysqli_query($con, $total_amt_sql));
$total_expense_sum = $total_res['total'] ?? 0;

include('ini/header.php'); 
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Expense Management</h1>
        <a href="add_expenses.php?branch_id=<?php echo $url_branch_id; ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Expense
        </a>
    </div>

    <?php if($edit_data): ?>
    <div class="card shadow mb-4 border-left-warning" id="editForm">
        <div class="card-header py-3 bg-warning text-dark d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold">Edit Expense ID: #<?php echo $edit_data['expense_id']; ?></h6>
            <a href="expense_report.php?branch_id=<?php echo $url_branch_id; ?>" class="text-dark"><i class="fas fa-times"></i></a>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="expense_id" value="<?php echo $edit_data['expense_id']; ?>">
                <input type="hidden" name="old_pic" value="<?php echo $edit_data['invoice_pic']; ?>">
                
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Date</label>
                        <input type="date" name="expense_date" class="form-control" value="<?php echo $edit_data['expense_date']; ?>" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Category</label>
                        <select name="expense_category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <?php 
                            // Fetch categories from ex_category table
                            $cat_query = mysqli_query($con, "SELECT * FROM ex_category ORDER BY e_cat_name ASC");
                            while($cat_row = mysqli_fetch_assoc($cat_query)) {
                                // Check if this category matches the saved one
                                $selected = ($edit_data['expense_category'] == $cat_row['e_cat_name']) ? "selected" : "";
                                echo "<option value='".$cat_row['e_cat_name']."' $selected>".$cat_row['e_cat_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Vendor</label>
                        <input type="text" name="vendor_name" class="form-control" value="<?php echo $edit_data['vendor_name']; ?>" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $edit_data['amount']; ?>" required>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold">Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo $edit_data['description']; ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold">Update Invoice</label>
                        <input type="file" name="invoice_pic" class="form-control-file border p-1">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" name="update_expense" class="btn btn-warning btn-block font-weight-bold">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Expenses</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_expense_sum, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" class="row align-items-end">
                        <div class="col-md-4"><label class="small font-weight-bold">Start</label><input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $start_date; ?>"></div>
                        <div class="col-md-4"><label class="small font-weight-bold">End</label><input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $end_date; ?>"></div>
                        <div class="col-md-4"><button type="submit" class="btn btn-sm btn-success btn-block"><i class="fas fa-filter"></i> Filter</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Expense Breakdown</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="expenseTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Invoice</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($res)) { ?>
                        <tr <?php echo (isset($edit_id) && $edit_id == $row['expense_id']) ? 'class="table-warning"' : ''; ?>>
                            <td><?php echo date('d-M-Y', strtotime($row['expense_date'])); ?></td>
                            <td><span class="badge badge-secondary"><?php echo $row['expense_category']; ?></span></td>
                            <td><?php echo $row['vendor_name']; ?></td>
                            <td><small><?php echo $row['description']; ?></small></td>
                            <td class="font-weight-bold text-danger"><?php echo number_format($row['amount'], 2); ?></td>
                            <td class="text-center">
                                <?php if(!empty($row['invoice_pic'])): ?>
                                    <a href="expense_invoice/<?php echo $row['invoice_pic']; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="expense_report.php?branch_id=<?php echo $url_branch_id; ?>&edit_id=<?php echo $row['expense_id']; ?>#editForm" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
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
    $('#expenseTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Export', className: 'btn btn-sm btn-success mr-2' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn btn-sm btn-dark' }
        ],
        "order": [[ 0, "desc" ]]
    });
});
</script>