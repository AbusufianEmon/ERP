<?php
session_start();
include('dbcon.php');

/* ===============================
    ERROR REPORTING
================================ */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ===============================
    VALIDATE INPUT
================================ */
if (!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])) {
    die("Invalid Invoice");
}

$invoice_no = mysqli_real_escape_string($con, $_GET['invoice_id']);
$message = "";
$type = "success";
$branch_id = 0; 

try {
    /* ===============================
        1. FETCH & VALIDATE STATUS
    ================================ */
    // Fetch branch_id and status to prevent double processing
    $checkQuery = mysqli_query($con, "SELECT branch_id, status FROM direct_sales WHERE invoice_no = '$invoice_no' LIMIT 1");
    $invoiceData = mysqli_fetch_assoc($checkQuery);
    
    if (!$invoiceData) {
        throw new Exception("Invoice not found.");
    }

    // IMPORTANT: If status is already 1, stop the script to prevent double deduction
    if ($invoiceData['status'] == 1) {
        throw new Exception("This invoice is already approved and stock has been deducted.");
    }

    $branch_id = $invoiceData['branch_id'];

    /* ===============================
        START TRANSACTION
    ================================ */
    mysqli_begin_transaction($con);

    /* ===============================
        2. APPROVE INVOICE (Set status = 1)
    ================================ */
    mysqli_query($con, "
        UPDATE direct_sales 
        SET status = 1 
        WHERE invoice_no = '$invoice_no'
    ");

    /* ===============================
        3. FETCH SOLD ITEMS
    ================================ */
    $itemsRes = mysqli_query($con, "
        SELECT product_stock_id, qty 
        FROM direct_sales 
        WHERE invoice_no = '$invoice_no'
    ");

    if (mysqli_num_rows($itemsRes) == 0) {
        throw new Exception("No items found for this invoice");
    }

    /* ===============================
        4. UPDATE STOCK
    ================================ */
    while ($row = mysqli_fetch_assoc($itemsRes)) {

        $stock_id = (int)$row['product_stock_id'];
        $sold_qty = (int)$row['qty'];

        // Locking the row for update to prevent race conditions
        $stockRes = mysqli_query(
            $con,
            "SELECT qty FROM product_stock WHERE product_stock_id = $stock_id FOR UPDATE"
        );
        $stock = mysqli_fetch_assoc($stockRes);

        if (!$stock) {
            throw new Exception("Stock record not found for ID: $stock_id");
        }

        if ($stock['qty'] < $sold_qty) {
            throw new Exception("Insufficient stock. Available: " . $stock['qty']);
        }

        // Subtracting sold qty from product_stock qty
        mysqli_query($con, "
            UPDATE product_stock 
            SET qty = qty - $sold_qty 
            WHERE product_stock_id = $stock_id
        ");
    }

    mysqli_commit($con);
    $message = "Invoice approved and stock reduced successfully.";

} catch (Exception $e) {
    mysqli_rollback($con);
    $type = "error";
    $message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Sale Approval</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: "<?= $type ?>",
    title: "<?= $type === 'success' ? 'Approved!' : 'Error!' ?>",
    text: "<?= htmlspecialchars($message) ?>",
    confirmButtonText: "OK"
}).then(() => {
    // Redirect back to the approval list with the correct branch_id
    window.location.href = "direct_sales_approval.php?branch_id=<?php echo $branch_id; ?>";
});
</script>

</body>
</html>