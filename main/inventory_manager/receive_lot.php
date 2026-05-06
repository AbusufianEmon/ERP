<?php
include('dbcon.php');

$lot_no = $_POST['lot_no'] ?? '';
$selected_items = $_POST['selected_items'] ?? [];
$paid_amounts = $_POST['paid_amount'] ?? [];

// Fixed branch ID
$branch_id = 4;

if (empty($selected_items)) {
    echo "<script>alert('Please select at least one product.'); window.history.back();</script>";
    exit;
}

mysqli_begin_transaction($con);

try {
    $ids = implode(',', array_map('intval', $selected_items));

    // Fetch purchase_order items with status = 0 (not yet received)
    $res = mysqli_query($con, "SELECT * FROM purchase_order WHERE lot_no='$lot_no' AND status=0 AND product_id IN ($ids)");
    if (!$res) throw new Exception("Purchase orders fetch failed");

    // Get invoice_no from first product
    $invoice_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT invoice_no FROM purchase_order WHERE lot_no='$lot_no' LIMIT 1"));
    $invoice_no = $invoice_row['invoice_no'] ?? '';

    mysqli_data_seek($res, 0);

    while ($row = mysqli_fetch_assoc($res)) {
        $product_id = $row['product_id'];
        $stock_id = $row['stock_id'];
        $supplier_id = $row['supplier_id'];

        // Get category ID from product table
        $cat_id_res = mysqli_query($con, "SELECT cat_id FROM product WHERE id=$product_id");
        $cat_id_row = mysqli_fetch_assoc($cat_id_res);
        $cat_id = $cat_id_row['cat_id'] ?? 0;

        $qty = $row['qty'];
        $buy_price = $row['buy_price'];
        $sell_price = $row['sell_price'];
        $old_paid = $row['paid_amount'];
        $new_paid = $paid_amounts[$product_id] ?? $old_paid;
        $total = $qty * $buy_price;
        $due = $total - $new_paid;

        // ✅ Insert into product_stock with branch_id = 4
        $stmt = $con->prepare("
            INSERT INTO product_stock 
            (stock_id, product_id, supplier_id, cat_id, branch_id, qty, lot_no, buy_price, sell_price, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiiiidsdd", $stock_id, $product_id, $supplier_id, $cat_id, $branch_id, $qty, $lot_no, $buy_price, $sell_price);
        if (!$stmt->execute()) throw new Exception("Insert product_stock failed");

        // Update purchase_order status & paid_amount for this product
        $update_po = mysqli_query($con, "UPDATE purchase_order SET status=1, paid_amount=$new_paid WHERE invoice_no='$invoice_no' AND product_id=$product_id");
        if (!$update_po) throw new Exception("Purchase order update failed");

        // Update sup_invoice only if paid_amount changed
        if ($new_paid != $old_paid) {
            $paid_amount_safe = mysqli_real_escape_string($con, $new_paid);
            $product_id_safe = mysqli_real_escape_string($con, $product_id);
            $due_amount = ($buy_price * $qty) - $new_paid;

            $update_invoice = mysqli_query($con, "
                UPDATE sup_invoice 
                SET paid_amount='$paid_amount_safe', due_amount='$due_amount' 
                WHERE invoice_no='$invoice_no' AND product_id='$product_id_safe'
            ");
            if (!$update_invoice) throw new Exception("Invoice update failed");
        }
    }

    mysqli_commit($con);
    echo "<script>
        alert('Selected products received successfully.');
        window.location.href='supplier_invoice.php?invoice_no=" . urlencode($invoice_no) . "';
    </script>";

} catch (Exception $e) {
    mysqli_rollback($con);
    echo "<script>alert('Process failed: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>
