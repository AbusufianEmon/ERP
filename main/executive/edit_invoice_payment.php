<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['invoice_id'])) {
    die("Invalid Invoice");
}

$invoice_no = mysqli_real_escape_string($con, $_GET['invoice_id']);

/* ============================================================
   1. FETCH INVOICE DATA
   ============================================================ */
$sql_info = "SELECT ds.invoice_no, ds.cus_id, ds.discount_percent, c.name AS customer_name 
             FROM direct_sales ds 
             LEFT JOIN customer c ON ds.cus_id = c.cus_id 
             WHERE ds.invoice_no = '$invoice_no' LIMIT 1";
$res_info = mysqli_query($con, $sql_info);
$info = mysqli_fetch_assoc($res_info);

if (!$info) { die("Invoice not found"); }

$cus_id = $info['cus_id'];
$discount_percent = floatval($info['discount_percent']);

/* ============================================================
   2. FETCH ITEMS & CALCULATE TOTALS
   ============================================================ */
$sql_items = "SELECT * FROM direct_sales WHERE invoice_no = '$invoice_no'";
$res_items = mysqli_query($con, $sql_items);

$items = [];
$total_actual_price = 0;
$old_single_paid = 0;

while ($row = mysqli_fetch_assoc($res_items)) {
    $total_actual_price += ($row['sell_price'] * $row['qty']);
    $old_single_paid = floatval($row['paid_amount']); 
    $items[] = $row;
}

$grand_discount = $total_actual_price * ($discount_percent / 100);
$grand_total_after_discount = $total_actual_price - $grand_discount;

/* ============================================================
   3. HANDLE UPDATE LOGIC
   ============================================================ */
if (isset($_POST['update_invoice_payment'])) {
    $new_single_paid = (int)$_POST['new_paid_amount'];

    // PHP Validation: Prevent minus amount
    if ($new_single_paid < 0) {
        echo "<script>alert('Error: Paid amount cannot be negative.'); window.history.back();</script>";
        exit;
    }

    $payment_difference = $new_single_paid - (int)$old_single_paid;
    $new_invoice_total_due = $grand_total_after_discount - $new_single_paid;

    mysqli_begin_transaction($con);

    try {
        // A. Update all items in direct_sales
        foreach ($items as $item) {
            $sid = $item['sale_id'];
            
            $update_ds = "UPDATE direct_sales SET 
                          paid_amount = '$new_single_paid', 
                          due_amount = '$new_invoice_total_due' 
                          WHERE sale_id = '$sid'";
            mysqli_query($con, $update_ds);
        }

        // B. Update Customer Due Amount
        $update_cus_due = "UPDATE customer SET due_amount = due_amount - $payment_difference WHERE cus_id = '$cus_id'";
        mysqli_query($con, $update_cus_due);

        // C. UPDATE LEDGER
        $update_ledger = "UPDATE customer SET ledger = ledger + $payment_difference WHERE cus_id = '$cus_id'";
        
        if(!mysqli_query($con, $update_ledger)) {
            throw new Exception("Ledger Update Failed: " . mysqli_error($con));
        }

        mysqli_commit($con);
        echo "<script>alert('Success! Updated Due to " . number_format($new_invoice_total_due, 2) . " and synced Ledger.'); window.location.href='sale_invoice.php?invoice_id=$invoice_no';</script>";
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Update Payment: Invoice #<?= $invoice_no ?></h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4 border-bottom pb-3">
                        <div class="col-md-6">
                            <h5><strong>Customer:</strong> <?= htmlspecialchars($info['customer_name']) ?></h5>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5 class="text-dark">Grand Total: <strong><?= number_format($grand_total_after_discount, 2) ?></strong></h5>
                            <h6 class="text-danger">Current Due: <strong id="current_due_display"><?= number_format($grand_total_after_discount - $old_single_paid, 2) ?></strong></h6>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Paid Amount Before</label>
                                <div class="form-control bg-light"><?= number_format($old_single_paid, 2) ?></div>
                            </div>
                            <div class="col-md-5">
                                <label class="fw-bold text-primary">Update Total Paid Amount</label>
                                <input type="number" id="new_paid_input" name="new_paid_amount" 
                                       class="form-control form-control-lg border-primary" 
                                       value="<?= (int)$old_single_paid ?>" min="0" 
                                       oninput="if(this.value < 0) this.value = 0;" required>
                            </div>
                            <div class="col-md-3">
                                <button type="button" onclick="setFullPayment()" class="btn btn-outline-primary w-100">Pay Full</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <h6 class="mb-1 font-weight-bold text-muted">Cash Received Now:</h6>
                                        <h4 class="mb-0 text-success">+ <span id="diff_display">0</span> TK</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <h6 class="mb-1 font-weight-bold text-muted">New Invoice Balance:</h6>
                                        <h4 class="mb-0 text-danger"><span id="new_due_display"><?= number_format($grand_total_after_discount - $old_single_paid, 2) ?></span> TK</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="sale_invoice.php?invoice_id=<?= $invoice_no ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="update_invoice_payment" class="btn btn-success px-5 fw-bold shadow">
                                Confirm & Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const input = document.getElementById('new_paid_input');
const diffDisplay = document.getElementById('diff_display');
const newDueDisplay = document.getElementById('new_due_display');
const oldPaid = <?= (int)$old_single_paid ?>;
const grandTotal = <?= (int)$grand_total_after_discount ?>;

input.addEventListener('input', function() {
    let newVal = parseInt(this.value) || 0;
    
    // JS Validation: Reset to 0 if negative
    if (newVal < 0) {
        newVal = 0;
        this.value = 0;
    }
    
    // Difference for Ledger
    let diff = newVal - oldPaid;
    diffDisplay.innerText = diff.toLocaleString();

    // Remaining Due for Table
    let remainingDue = grandTotal - newVal;
    newDueDisplay.innerText = remainingDue.toLocaleString(undefined, {minimumFractionDigits: 2});
});

function setFullPayment() {
    input.value = Math.round(grandTotal);
    input.dispatchEvent(new Event('input'));
}
</script>

<?php include('ini/footer.php'); ?>