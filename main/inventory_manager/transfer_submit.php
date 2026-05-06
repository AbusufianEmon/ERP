<?php
include 'dbcon.php';

mysqli_begin_transaction($con);

try {
    $transfer_id = $_POST['transfer_id'];
    $from        = (int)$_POST['from_branch'];
    $to          = (int)$_POST['to_branch'];

    if ($from === $to) {
        throw new Exception("Same branch transfer not allowed");
    }

    // Check if the array exists to avoid the Foreach Warning
    if (!isset($_POST['product_stock_id']) || !is_array($_POST['product_stock_id'])) {
        throw new Exception("No products selected for transfer.");
    }

    foreach ($_POST['product_stock_id'] as $i => $stockId) {
        $stockId = (int)$stockId;
        $qty     = (int)$_POST['qty'][$i];

        /* 🔒 Lock source stock row */
        $src = mysqli_query($con, "
            SELECT * FROM product_stock 
            WHERE product_stock_id = $stockId 
              AND branch_id = $from 
            FOR UPDATE
        ");

        if (mysqli_num_rows($src) === 0) {
            throw new Exception("Product stock not found in source branch (Stock ID: $stockId)");
        }

        $srcRow = mysqli_fetch_assoc($src);

        if ($srcRow['qty'] < $qty) {
            throw new Exception("Insufficient stock for product: {$srcRow['lot_no']}");
        }

        /* 🧾 Transfer log */
        $insertTransfer = "
            INSERT INTO product_transfer 
            (transfer_id, product_stock_id, product_id, from_branch, to_branch, qty, lot_no, buy_price, sell_price, stock_manager_approval_status, transfer_status)
            VALUES 
            ('$transfer_id', $stockId, {$srcRow['product_id']}, $from, $to, $qty, '{$srcRow['lot_no']}', '{$srcRow['buy_price']}', '{$srcRow['sell_price']}', 1, 2)
        ";
        mysqli_query($con, $insertTransfer);

        /* ➖ Reduce source quantity */
        mysqli_query($con, "UPDATE product_stock SET qty = qty - $qty WHERE product_stock_id = $stockId");

        /* 🔍 Check destination stock by stock_id and branch_id */
        $dest = mysqli_query($con, "
            SELECT product_stock_id 
            FROM product_stock 
            WHERE stock_id  = {$srcRow['stock_id']} 
              AND branch_id = $to
        ");

        if (mysqli_num_rows($dest) > 0) {
            mysqli_query($con, "UPDATE product_stock SET qty = qty + $qty WHERE stock_id = {$srcRow['stock_id']} AND branch_id = $to");
        } else {
            mysqli_query($con, "
                INSERT INTO product_stock 
                (stock_id, product_id, supplier_id, cat_id, branch_id, qty, lot_no, buy_price, sell_price)
                VALUES 
                ({$srcRow['stock_id']}, {$srcRow['product_id']}, {$srcRow['supplier_id']}, {$srcRow['cat_id']}, $to, $qty, '{$srcRow['lot_no']}', '{$srcRow['buy_price']}', '{$srcRow['sell_price']}')
            ");
        }
    }

    mysqli_commit($con);
    echo "<h3 style='color:green'>Transfer Completed Successfully. Transfer ID: $transfer_id</h3>";

} catch (Exception $e) {
    mysqli_rollback($con);
    echo "<h3 style='color:red'>Error: {$e->getMessage()}</h3>";
}
?>