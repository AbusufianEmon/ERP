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

/* ==========================
   NEW VALIDATIONS
========================== */
if ($paid_amount <= 0) {
    http_response_code(400);
    exit("Paid amount must be greater than zero.");
}

if ($discount_percent < 0) {
    http_response_code(400);
    exit("Discount percentage cannot be negative.");
}

mysqli_begin_transaction($con);

$buyPrices = [];

/* ==========================
   STOCK CHECK (WITH LOCK LOGIC)
========================== */
foreach ($cart as $item) {
    $stock_id = intval($item['stock_id']);
    $req_qty  = intval($item['qty']);

    // We check physical qty MINUS sum of qty where status = 0 (the 'locked' amount)
    $stock_q = mysqli_query($con, "
        SELECT 
            ps.qty as physical_qty, 
            ps.buy_price,
            (SELECT IFNULL(SUM(qty), 0) FROM direct_sales WHERE product_stock_id = ps.product_stock_id AND status = 0) as locked_qty
        FROM product_stock ps 
        WHERE ps.product_stock_id = $stock_id 
        AND ps.branch_id = $branch_id
        FOR UPDATE
    ");

    $stock = mysqli_fetch_assoc($stock_q);

    if (!$stock) {
        mysqli_rollback($con);
        http_response_code(400);
        exit("Invalid product stock");
    }

    // Real Available = Physical - Locked
    $real_available = $stock['physical_qty'] - $stock['locked_qty'];

    if ($req_qty > $real_available) {
        mysqli_rollback($con);
        http_response_code(400);
        exit("Insufficient available stock. {$real_available} remaining (some are locked in pending sales).");
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
   INSERT SALES (STATUS 0 BY DEFAULT)
========================== */
foreach ($cart as $item) {
    $stock_id = intval($item['stock_id']);
    $qty      = intval($item['qty']);
    $price    = floatval($item['price']);
    $total    = $qty * $price;
    $name     = mysqli_real_escape_string($con, $item['name']);
    $code     = mysqli_real_escape_string($con, $item['code']);
    $buy_price = floatval($buyPrices[$stock_id]);

    // Note: status is 0 here. We DON'T update product_stock qty.
    $insert_sale = "INSERT INTO direct_sales (
        invoice_no, cus_id, customer_code, product_stock_id, product_name, 
        product_code, qty, buy_price, sell_price, total_price, 
        paid_amount, due_amount, discount_percent, remarks, branch_id, status
    ) VALUES (
        '$invoice', $cus_id, $customer_code, $stock_id, '$name', 
        '$code', $qty, $buy_price, $price, $total, 
        $paid_amount, $due_amount, $discount_percent, '$remarks', $branch_id, 0
    )";
    
    if(!mysqli_query($con, $insert_sale)) {
        mysqli_rollback($con);
        http_response_code(500);
        exit("Error inserting sale: " . mysqli_error($con));
    }
}

/* ==========================
   UPDATE CUSTOMER LEDGER & DUE BALANCE
========================== */
if ($due_amount > 0) {
    mysqli_query($con, "UPDATE customer SET ledger = ledger - $due_amount WHERE cus_id = $cus_id");
    mysqli_query($con, "UPDATE customer SET due_amount = due_amount + $due_amount WHERE cus_id = $cus_id");
}

mysqli_commit($con);
echo "Sale submitted. Stock is locked until manager approval. Invoice: $invoice";
?>