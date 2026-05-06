<?php
session_start();
include('dbcon.php');

if (!isset($_GET['invoice_id'])) {
    die("Invalid Invoice");
}

$invoice_id = mysqli_real_escape_string($con, $_GET['invoice_id']);

/* ===============================
   Fetch quotation header
================================ */
$sql_info = "
SELECT 
    cq.corporate_quotation_invoice_id,
    cq.manager_approvel_status,
    cq.remarks,
    cc.corporate_name,
    cc.corporate_email,
    cc.corporate_number,
    cc.corporate_address
FROM corporate_quotation cq
LEFT JOIN corporate_customer cc 
    ON cq.corporate_id = cc.corporate_id
WHERE cq.corporate_quotation_invoice_id = '$invoice_id'
LIMIT 1
";

$res_info = mysqli_query($con, $sql_info);
$info = mysqli_fetch_assoc($res_info);

if (!$info) {
    die("Quotation not found");
}

/* ===============================
   Fetch quotation items
================================ */
$sql_items = "
SELECT 
    cq.product_name,
    p.code,
    cq.qty,
    cq.buy_price,
    cq.offer_price
FROM corporate_quotation cq
LEFT JOIN product p 
    ON cq.product_id = p.id
WHERE cq.corporate_quotation_invoice_id = '$invoice_id'
";

$res_items = mysqli_query($con, $sql_items);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Corporate Quotation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


<style>
body { background:#f4f4f4; }
.invoice-box {
    background:#fff;
    padding:40px;
    margin:30px auto;
    max-width:1100px;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}
.table th { background:#333;color:#fff;text-align:center; }
.table td { text-align:center; }
.remarks-box {
    background:#f8f9fa;
    padding:15px;
    border-left:5px solid #0d6efd;
}
</style>
</head>

<body>

<div class="invoice-box">

<!-- Header -->
<div class="d-flex justify-content-between mb-4">
    <div>
        <h3>Corporate Quotation</h3>
        <p><strong>Invoice:</strong> <?= htmlspecialchars($invoice_id); ?></p>
    </div>

    <div class="text-end">
        
        <?php if ($info['manager_approvel_status'] == 1) { ?>
            <span class="text-success fw-bold">Approved</span>
        <?php } else { ?>
            <a href="approve_quotation.php?corporate_quotation_invoice_id=<?= urlencode($invoice_id); ?>" 
               class="btn btn-success btn-sm">
                Approve
            </a>
            <a href="edit_quotation.php?corporate_quotation_invoice_id=<?= urlencode($invoice_id); ?>"class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
           <a href="delete_quotation.php?invoice_id=<?= urlencode($invoice_id); ?>" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure you want to delete this quotation?');">
   <i class="fa fa-trash"></i> Decline
</a>

        <?php } ?>
    </div>
</div>

<!-- Corporate Info -->
<div class="mb-4">
    <h5>Corporate Details</h5>
    <p><strong><?= htmlspecialchars($info['corporate_name']); ?></strong></p>
    <p>Email: <?= htmlspecialchars($info['corporate_email']); ?></p>
    <p>Phone: <?= htmlspecialchars($info['corporate_number']); ?></p>
    <p>Address: <?= htmlspecialchars($info['corporate_address']); ?></p>
</div>

<!-- Items -->
<table class="table table-bordered">
<thead>
<tr>
    <th>SL</th>
    <th>Product</th>
    <th>Code</th>
    <th>Qty</th>
    <th>Offer Price</th>
    <th>Total</th>
</tr>
</thead>

<tbody>
<?php
$i = 1;
$grand_total = 0;

while ($row = mysqli_fetch_assoc($res_items)) {
    $line_total = $row['qty'] * $row['offer_price'];
    $grand_total += $line_total;
?>
<tr>
    <td><?= $i++; ?></td>
    <td><?= htmlspecialchars($row['product_name']); ?></td>
    <td><?= htmlspecialchars($row['code']); ?></td>
    <td><?= $row['qty']; ?></td>
    <td><?= number_format($row['offer_price'], 2); ?></td>
    <td><?= number_format($line_total, 2); ?></td>
</tr>
<?php } ?>
</tbody>

<tfoot>
<tr>
    <th colspan="5" class="text-end">Grand Total</th>
    <th><?= number_format($grand_total, 2); ?></th>
</tr>
</tfoot>
</table>

<!-- Remarks -->
<?php if (!empty($info['remarks'])) { ?>
<div class="mb-4">
    <h5>Remarks</h5>
    <div class="remarks-box">
        <?= nl2br(htmlspecialchars($info['remarks'])); ?>
    </div>
</div>
<?php } ?>

<div class="text-end mt-4 d-print-none">
    <button onclick="window.print()" class="btn btn-success">
        Print Quotation
    </button>
</div>

</div>

</body>
</html>
