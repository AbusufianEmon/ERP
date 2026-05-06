<?php
session_start();
include('dbcon.php');

if (!isset($_GET['invoice_id'])) {
    die("Invalid Invoice");
}

$invoice_no = mysqli_real_escape_string($con, $_GET['invoice_id']);

/* ===============================
    Fetch invoice header
================================ */
$sql_info = "
SELECT 
    ds.invoice_no,
    ds.status,
    ds.cus_id,
    ds.remarks,
    ds.discount_percent,
    c.name AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    c.address AS customer_address
FROM direct_sales ds
LEFT JOIN customer c ON ds.cus_id = c.cus_id
WHERE ds.invoice_no = '$invoice_no'
LIMIT 1
";

$res_info = mysqli_query($con, $sql_info);
$info = mysqli_fetch_assoc($res_info);

if (!$info) {
    die("Invoice not found");
}

/* ===============================
    Fetch invoice items
================================ */
$sql_items = "
SELECT 
    product_name, product_code, qty, sell_price, total_price, paid_amount, due_amount
FROM direct_sales
WHERE invoice_no = '$invoice_no'
";

$res_items = mysqli_query($con, $sql_items);

$total_actual_price = 0;
$total_paid = 0;
$total_due = 0;
$items = [];

while ($row = mysqli_fetch_assoc($res_items)) {
    $line_actual_price = $row['sell_price'] * $row['qty'];
    $total_actual_price += $line_actual_price;
    $total_paid = $row['paid_amount']; 
    $total_due = $row['due_amount'];
    $row['line_actual_price'] = $line_actual_price;
    $items[] = $row;
}

$discount_percent = $info['discount_percent'];
$grand_discount = $total_actual_price * ($discount_percent / 100);
$grand_total_after_discount = $total_actual_price - $grand_discount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Direct Sales Invoice - <?= $invoice_no ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f4f4; font-family: 'Segoe UI', sans-serif; }
        .invoice-box {
            background:#fff; padding:40px; margin:30px auto;
            max-width:1100px; box-shadow:0 0 10px rgba(0,0,0,.1); border-radius: 8px;
        }
        .table th { background:#333; color:#fff; text-align:center; }
        .table td { text-align:center; vertical-align: middle; }
        .remarks-box { background:#f8f9fa; padding:15px; border-left:5px solid #0d6efd; font-style: italic; }
        @media print { .d-print-none { display: none !important; } }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="d-flex justify-content-between mb-4">
        <div>
            <h2 class="text-uppercase fw-bold text-primary">Invoice</h2>
            <p><strong>Invoice No:</strong> <?= htmlspecialchars($invoice_no); ?></p>
        </div>
        <div class="text-end d-print-none">
            <?php if ($info['status'] == 1) { ?>
                <span class="badge bg-success fs-6">Approved</span>
            <?php } else { ?>
                <span class="badge bg-warning fs-6">Not Approved</span>
            <?php } ?>
        </div>
    </div>

    <hr>

    <div class="row mb-4">
        <div class="col-md-6">
            <h5 class="fw-bold border-bottom pb-2">Bill To:</h5>
            <p class="mb-1"><strong><?= htmlspecialchars($info['customer_name'] ?? 'N/A'); ?></strong></p>
            <p class="mb-1">Phone: <?= htmlspecialchars($info['customer_phone'] ?? 'N/A'); ?></p>
            <p class="mb-1">Address: <?= htmlspecialchars($info['customer_address'] ?? 'N/A'); ?></p>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>SL</th>
                <th>Product</th>
                <th>Code</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach($items as $row): ?>
            <tr>
                <td><?= $i++; ?></td>
                <td><?= htmlspecialchars($row['product_name']); ?></td>
                <td><?= htmlspecialchars($row['product_code']); ?></td>
                <td><?= $row['qty']; ?></td>
                <td><?= number_format($row['sell_price'], 2); ?></td>
                <td><?= number_format($row['line_actual_price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end fw-bold">Subtotal</td>
                <td><?= number_format($total_actual_price, 2); ?></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end fw-bold text-danger">Discount (<?= $discount_percent ?>%)</td>
                <td class="text-danger">-<?= number_format($grand_discount, 2); ?></td>
            </tr>
            <tr class="table-dark">
                <td colspan="5" class="text-end fw-bold">Grand Total</td>
                <td><?= number_format($grand_total_after_discount, 2); ?></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end fw-bold text-primary">Paid Amount</td>
                <td class="text-primary"><?= number_format($total_paid, 2); ?></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end fw-bold text-danger">Due Amount</td>
                <td class="text-danger fw-bold"><?= number_format($total_due, 2); ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($info['remarks'])): ?>
    <div class="mt-4">
        <h6 class="fw-bold">Remarks:</h6>
        <div class="remarks-box"><?= nl2br(htmlspecialchars($info['remarks'])); ?></div>
    </div>
    <?php endif; ?>

    <div class="text-end mt-5 d-print-none">
    <button onclick="window.print()" class="btn btn-dark px-4">Print Invoice</button>
</div>
</div>

</body>
</html>