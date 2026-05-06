<?php 
include('ini/header.php'); // This includes your session_start and db connection
include('dbcon.php');

// Get Branch ID from URL
$url_branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

// Handle Form Submission
if (isset($_POST['submit_expense'])) {
    $expense_date = mysqli_real_escape_string($con, $_POST['expense_date']);
    $category_id = mysqli_real_escape_string($con, $_POST['expense_category']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $amount = mysqli_real_escape_string($con, $_POST['amount']);
    $vendor_name = mysqli_real_escape_string($con, $_POST['vendor_name']);
    
    // PHP Validation: Amount must be greater than 0
    if ($amount <= 0) {
        echo "<script>
                swal('Invalid Amount', 'Expense amount must be 1 or greater!', 'error');
              </script>";
    } else {
        // File Upload Logic
        $invoice_pic = "";
        if (!empty($_FILES['invoice_pic']['name'])) {
            $target_dir = "expense_invoice/";
            $file_extension = pathinfo($_FILES["invoice_pic"]["name"], PATHINFO_EXTENSION);
            $new_file_name = "EXP_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["invoice_pic"]["tmp_name"], $target_file)) {
                $invoice_pic = $new_file_name;
            }
        }

        $insert_sql = "INSERT INTO expenses (branch_id, expense_date, expense_category, description, amount, vendor_name, invoice_pic) 
                       VALUES ('$url_branch_id', '$expense_date', '$category_id', '$description', '$amount', '$vendor_name', '$invoice_pic')";

        if (mysqli_query($con, $insert_sql)) {
            echo "<script>
                    swal('Success', 'Expense added successfully!', 'success').then(() => {
                        window.location.href = 'add_expenses.php?branch_id=$url_branch_id';
                    });
                  </script>";
        } else {
            echo "<script>swal('Error', 'Failed to add expense', 'error');</script>";
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Expense</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success">
                    <h6 class="m-0 font-weight-bold text-white">Expense Details</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Category</label>
                                <select name="expense_category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $cat_query = mysqli_query($con, "SELECT * FROM ex_category ORDER BY e_cat_name ASC");
                                    while($cat = mysqli_fetch_assoc($cat_query)) {
                                        echo "<option value='".$cat['e_cat_name']."'>".$cat['e_cat_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Amount</label>
                                <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Vendor/Payee Name</label>
                                <input type="text" name="vendor_name" class="form-control" placeholder="Enter vendor name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label>Invoice Picture/Receipt</label>
                            <input type="file" name="invoice_pic" class="form-control-file" accept="image/*">
                            <small class="text-muted">Allowed: JPG, PNG. Max 2MB.</small>
                        </div>

                        <hr>
                        <button type="submit" name="submit_expense" class="btn btn-success btn-icon-split">
                            <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                            <span class="text">Save Expense</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Branch Expenses</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent = mysqli_query($con, "SELECT * FROM expenses WHERE branch_id = '$url_branch_id' ORDER BY expense_id DESC LIMIT 5");
                                if(mysqli_num_rows($recent) > 0) {
                                    while($r = mysqli_fetch_assoc($recent)) {
                                        echo "<tr>
                                                <td>".date('d M', strtotime($r['expense_date']))."</td>
                                                <td>".$r['expense_category']."</td>
                                                <td class='text-danger'>".number_format($r['amount'], 2)."</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center'>No records found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>