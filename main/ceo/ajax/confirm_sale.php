<?php
include('../dbcon.php');

$data = json_decode(file_get_contents("php://input"), true);

$cus_id        = intval($data['cus_id']);
$customer_code = intval($data['customer_code']);
$cart          = $data['cart'];
$paid_amount   = floatval($data['paid_amount']);
$branch_id     = intval($data['branch_id']);

$discount_percent = isset($data['discount_percent']) ? floatval($data['discount_percent']) : 0;
$remarks = isset($data['remarks']) ? mysqli_real_escape_string($con, $data['remarks']) : '';

mysqli_begin_transaction($con);

$buyPrices = [];

/* ==========================
   STOCK CHECK
========================== */
foreach ($cart as $item) {
    $stock_id = intval($item['stock_id']);
    $req_qty  = intval($item['qty']);

    // Fetch current stock and lock row for concurrent updates
    $stock_q = mysqli_query($con, "
        SELECT qty, buy_price 
        FROM product_stock 
        WHERE product_stock_id = $stock_id 
        AND branch_id = $branch_id
        FOR UPDATE
    ");

    $stock = mysqli_fetch_assoc($stock_q);

    if (!$stock) {
        mysqli_rollback($con);
        http_response_code(400);
        exit("Invalid product stock");
    }

    if ($req_qty > $stock['qty']) {
        mysqli_rollback($con);
        http_response_code(400);
        exit("Only {$stock['qty']} qty available for {$item['name']}");
    }

    $buyPrices[$stock_id] = $stock['buy_price'];
}

/* ==========================
   TOTAL & CALCULATION
========================== */
$grand_total = 0;
foreach($cart as $item) {
    $grand_total += (floatval($item['price']) * intval($item['qty']));
}

$discount_amount = ($grand_total * $discount_percent) / 100;
$final_total = $grand_total - $discount_amount;

if ($paid_amount > $final_total) {
    mysqli_rollback($con);
    http_response_code(400);
    exit("Paid amount cannot exceed total ($final_total)");
}

$due_amount = $final_total - $paid_amount;

/* ==========================
   LEDGER CHECK
========================== */
$cus_q = mysqli_query($con, "SELECT ledger FROM customer WHERE cus_id = $cus_id");
$cus = mysqli_fetch_assoc($cus_q);

if ($due_amount > 0 && $cus['ledger'] < $due_amount) {
    mysqli_rollback($con);
    http_response_code(400);
    exit("Insufficient ledger balance. Available: " . $cus['ledger']);
}

$invoice = "INV_" . date("Ymd_His") . "_" . rand(10,99);

/* ==========================
   INSERT SALES & UPDATE STOCK
========================== */
foreach ($cart as $item) {
    $stock_id = intval($item['stock_id']);
    $qty      = intval($item['qty']);
    $price    = floatval($item['price']);
    $total    = $qty * $price;
    $name     = mysqli_real_escape_string($con, $item['name']);
    $code     = mysqli_real_escape_string($con, $item['code']);
    $buy_price = floatval($buyPrices[$stock_id]);

    // Added 'due_amount' back into the column list and values list
    $insert_sale = "INSERT INTO direct_sales (
        invoice_no, cus_id, customer_code, product_stock_id, product_name, 
        product_code, qty, buy_price, sell_price, total_price, 
        paid_amount, due_amount, discount_percent, remarks, branch_id
    ) VALUES (
        '$invoice', $cus_id, $customer_code, $stock_id, '$name', 
        '$code', $qty, $buy_price, $price, $total, 
        $paid_amount, $due_amount, $discount_percent, '$remarks', $branch_id
    )";
    
    if(!mysqli_query($con, $insert_sale)) {
        mysqli_rollback($con);
        http_response_code(500);
        exit("Error inserting sale: " . mysqli_error($con));
    }

    // Update Stock
    mysqli_query($con, "
        UPDATE product_stock 
        SET qty = qty - $qty 
        WHERE product_stock_id = $stock_id
    ");
}

/* ==========================
   UPDATE CUSTOMER LEDGER & DUE BALANCE
========================== */
if ($due_amount > 0) {
    // Subtract from ledger
    mysqli_query($con, "
        UPDATE customer 
        SET ledger = ledger - $due_amount 
        WHERE cus_id = $cus_id
    ");

    // Add to customer table's persistent due_amount column
    mysqli_query($con, "
        UPDATE customer 
        SET due_amount = due_amount + $due_amount 
        WHERE cus_id = $cus_id
    ");
}

mysqli_commit($con);
echo "Sale completed successfully. Invoice: $invoice";
?>