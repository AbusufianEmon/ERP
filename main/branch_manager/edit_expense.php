<?php
include('dbcon.php');

if (!isset($_GET['expense_id']) || !isset($_GET['branch_id'])) {
    die("Invalid Request");
}

$expense_id = intval($_GET['expense_id']);
$branch_id  = intval($_GET['branch_id']);

/* =========================
   FETCH OLD DATA
========================= */
$fetch_sql = "SELECT * FROM expenses WHERE expense_id = '$expense_id'";
$fetch_res = mysqli_query($con, $fetch_sql);

if (!$fetch_res || mysqli_num_rows($fetch_res) == 0) {
    die("Expense not found");
}

$data = mysqli_fetch_assoc($fetch_res);


/* =========================
   UPDATE EXPENSE
========================= */
if (isset($_POST['update_expense'])) {

    $expense_date     = $_POST['expense_date'] ?? '';
    $amount           = $_POST['amount'] ?? 0;
    $expense_category = mysqli_real_escape_string($con, $_POST['expense_category'] ?? '');
    $vendor_name      = mysqli_real_escape_string($con, $_POST['vendor_name'] ?? '');
    $description      = mysqli_real_escape_string($con, $_POST['description'] ?? '');

    $invoice_pic = $data['invoice_pic'] ?? '';

    if (!empty($_FILES['invoice_pic']['name'])) {

        $file_name = time() . "_" . $_FILES['invoice_pic']['name'];
        $tmp_name  = $_FILES['invoice_pic']['tmp_name'];
        $path      = "expense_invoice/" . $file_name;

        move_uploaded_file($tmp_name, $path);
        $invoice_pic = $file_name;
    }

    $update_sql = "UPDATE expenses SET 
        expense_date = '$expense_date',
        amount = '$amount',
        expense_category = '$expense_category',
        vendor_name = '$vendor_name',
        description = '$description',
        invoice_pic = '$invoice_pic'
        WHERE expense_id = '$expense_id'";

    if (mysqli_query($con, $update_sql)) {
        header("Location: expense_report.php?branch_id=$branch_id");
        exit();
    } else {
        $error_msg = "Update failed!";
    }
}

include('ini/header.php');
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Expense</h1>
        <a href="expense_report.php?branch_id=<?php echo $branch_id; ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Report
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-dark">
                        Update Details for Expense ID #<?php echo $expense_id; ?>
                    </h6>
                </div>

                <div class="card-body">
                    <?php if(isset($error_msg)) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Expense Date</label>
                                <input type="date" name="expense_date" class="form-control"
                                       value="<?php echo $data['expense_date'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control"
                                       value="<?php echo $data['amount'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Category</label>
                                <input type="text" name="expense_category" class="form-control"
                                       value="<?php echo $data['expense_category'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Vendor Name</label>
                                <input type="text" name="vendor_name" class="form-control"
                                       value="<?php echo $data['vendor_name'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo $data['description'] ?? ''; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="font-weight-bold">Invoice Attachment</label>
                            <div class="mb-2">
                                <?php if(!empty($data['invoice_pic'] ?? '')): ?>
                                    <a href="expense_invoice/<?php echo $data['invoice_pic']; ?>" target="_blank">
                                        <?php echo $data['invoice_pic']; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No attachment</span>
                                <?php endif; ?>
                            </div>

                            <input type="file" name="invoice_pic" class="form-control-file border p-1 rounded w-100">
                            <small class="text-info">Upload only if you want to replace.</small>
                        </div>

                        <button type="submit" name="update_expense"
                                class="btn btn-warning btn-block font-weight-bold">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>
