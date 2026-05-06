<?php 
include('ini/header.php');
include('dbcon.php');

/* ============================================================
   1. FETCH CURRENT DATA
   ============================================================ */
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM customer WHERE cus_id = $id";
    $run = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($run);

    if(!$row) {
        echo "<script>alert('Customer not found'); window.location.href='customer_list.php';</script>";
        exit();
    }
}

/* ============================================================
   2. UPDATE LEDGER ONLY
   ============================================================ */
if(isset($_POST['update_ledger'])) {
    $new_ledger = mysqli_real_escape_string($con, $_POST['ledger']);
    
    // BACKEND VALIDATION: Check if the value contains a minus sign or is numerically less than 0
    if (is_numeric($new_ledger) && floatval($new_ledger) < 0) {
        echo "<script>alert('Error: Negative values are not accepted in the ledger.');</script>";
    } else {
        $update = "UPDATE customer SET ledger = '$new_ledger' WHERE cus_id = $id";

        if(mysqli_query($con, $update)) {
            echo "<script>
                    alert('Ledger Updated Successfully'); 
                    window.location.href='customer_list.php';
                  </script>";
        } else {
            echo "<script>alert('Update Failed: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Customer Ledger</h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modify Ledger for: <?php echo htmlspecialchars($row['name']); ?></h6>
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return validateLedger()">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-dark font-weight-bold">Customer Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control bg-light" readonly>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label class="text-dark font-weight-bold">Customer Code</label>
                                <input type="text" value="<?php echo htmlspecialchars($row['customer_code']); ?>" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-dark font-weight-bold">Phone</label>
                                <input type="text" value="<?php echo htmlspecialchars($row['phone']); ?>" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-dark font-weight-bold">Current Due Amount</label>
                                <input type="text" value="<?php echo number_format($row['due_amount'], 2); ?>" class="form-control bg-light text-danger font-weight-bold" readonly>
                            </div>

                            <div class="col-md-12 form-group">
                                <hr>
                                <label class="text-primary font-weight-bold">Customer Ledger (Editable)</label>
                                <textarea 
                                    id="ledger_input"
                                    name="ledger" 
                                    class="form-control border-left-primary" 
                                    rows="4" 
                                    required 
                                    oninput="this.value = this.value.replace(/-/g, '')"
                                ><?php echo htmlspecialchars($row['ledger']); ?></textarea>
                                <small class="form-text text-muted">Update ledger details. <b>Note:</b> Negative values are not allowed.</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="update_ledger" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Ledger Changes
                            </button>
                            <a href="customer_list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Customer Summary</h6>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Additional JavaScript validation to catch any bypasses
function validateLedger() {
    var ledgerValue = document.getElementById('ledger_input').value;
    if (ledgerValue.includes("-") || (parseFloat(ledgerValue) < 0)) {
        alert("Negative values are not permitted in the ledger.");
        return false;
    }
    return true;
}
</script>

<?php include('ini/footer.php'); ?>