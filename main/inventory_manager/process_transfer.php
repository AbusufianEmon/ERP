<?php
include('dbcon.php');

if (isset($_POST['transfer'])) {
    $from_branch = intval($_POST['from_branch'] ?? 0);
    $to_branch   = intval($_POST['to_branch'] ?? 0);
    $stock_ids   = $_POST['stock_id'] ?? [];
    $quantities  = $_POST['qty'] ?? [];

    if ($from_branch == 0 || $to_branch == 0) {
        header("Location: inter_branch_transfer.php?msg=invalid");
        exit();
    }

    if ($from_branch == $to_branch) {
        header("Location: inter_branch_transfer.php?msg=same_branch");
        exit();
    }

    $success = false;

    foreach ($stock_ids as $stock_id) {
        $transfer_qty = intval($quantities[$stock_id] ?? 0);

        if ($transfer_qty <= 0) continue;

        // Get stock details from source branch
        $sql = "SELECT * FROM product_stock WHERE stock_id = $stock_id AND branch_id = $from_branch";
        $res = mysqli_query($con, $sql);
        if (!$res || mysqli_num_rows($res) == 0) continue;

        $stock = mysqli_fetch_assoc($res);

        // Check available qty
        if ($stock['qty'] < $transfer_qty) continue;

        // Deduct from source branch
        $new_from_qty = $stock['qty'] - $transfer_qty;
        mysqli_query($con, "UPDATE product_stock SET qty = $new_from_qty WHERE stock_id = $stock_id");

        $product_id  = $stock['product_id'];
        $supplier_id = $stock['supplier_id'];
        $buy_price   = $stock['buy_price'];
        $sell_price  = $stock['sell_price'];
        $paid_amount = $stock['paid_amount'];

        // Check if product already exists in destination branch
        $check_sql = "SELECT * FROM product_stock 
                      WHERE product_id = $product_id 
                      AND branch_id = $to_branch 
                      AND supplier_id = $supplier_id
                      AND buy_price = $buy_price 
                      AND sell_price = $sell_price 
                      LIMIT 1";
        $check_res = mysqli_query($con, $check_sql);

        if ($check_res && mysqli_num_rows($check_res) > 0) {
            // Update existing qty
            $dest = mysqli_fetch_assoc($check_res);
            $new_qty = $dest['qty'] + $transfer_qty;
            mysqli_query($con, "UPDATE product_stock SET qty = $new_qty WHERE stock_id = " . $dest['stock_id']);
        } else {
            // Insert new row in destination branch
            $insert_sql = "INSERT INTO product_stock 
                (product_id, branch_id, supplier_id, qty, buy_price, sell_price, paid_amount) 
                VALUES 
                ($product_id, $to_branch, $supplier_id, $transfer_qty, $buy_price, $sell_price, $paid_amount)";
            mysqli_query($con, $insert_sql);
        }

        $success = true;
    }

    if ($success) {
        header("Location: view_transfers.php?msg=success");
    } else {
        header("Location: inter_branch_transfer.php?msg=failed");
    }
    exit();
}
?>
