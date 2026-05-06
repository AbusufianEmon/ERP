<?php
session_start();
include('dbcon.php');

if (!isset($_GET['corporate_quotation_invoice_id'])) {
    die("Invalid Invoice");
}

$invoice_id = mysqli_real_escape_string($con, $_GET['corporate_quotation_invoice_id']);

/* ===============================
   Update Offer Price + Qty + Remarks
================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);

    foreach ($_POST['items'] as $quotation_id => $item) {

        $qty   = mysqli_real_escape_string($con, $item['qty']);
        $price = mysqli_real_escape_string($con, $item['offer_price']);
        $quotation_id = intval($quotation_id);

        mysqli_query($con, "
            UPDATE corporate_quotation 
            SET qty='$qty', offer_price='$price', remarks='$remarks'
            WHERE corporate_quotation_id='$quotation_id'
        ");
    }

    header("Location: corporate_quotation_view.php?invoice_id=".$invoice_id);
    exit;
}

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
    cq.corporate_quotation_id,
    cq.product_name,
    p.code,
    cq.qty,
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
<title>Edit Corporate Quotation</title>

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
</style>
</head>

<body>

<div class="invoice-box">

<form method="POST">

<!-- Header -->
<div class="d-flex justify-content-between mb-4">
    <div>
        <h3>Edit Corporate Quotation</h3>
        <p><strong>Invoice:</strong> <?= htmlspecialchars($invoice_id); ?></p>
    </div>

    <div>
        <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> Save Changes
        </button>
        <a href="corporate_quotation_view.php?invoice_id=<?= urlencode($invoice_id); ?>" class="btn btn-secondary">
            Back
        </a>
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

    <td>
        <input type="number" 
               name="items[<?= $row['corporate_quotation_id']; ?>][qty]" 
               value="<?= $row['qty']; ?>" 
               class="form-control qty text-center" min="1">
    </td>

    <td>
        <input type="number" step="0.01"
               name="items[<?= $row['corporate_quotation_id']; ?>][offer_price]" 
               value="<?= $row['offer_price']; ?>" 
               class="form-control price text-center">
    </td>

    <td class="row-total"><?= number_format($line_total,2); ?></td>
</tr>
<?php } ?>
</tbody>

<tfoot>
<tr>
    <th colspan="5" class="text-end">Grand Total</th>
    <th id="grandTotal"><?= number_format($grand_total,2); ?></th>
</tr>
</tfoot>
</table>

<!-- Remarks -->
<div class="mt-3">
    <label class="fw-bold">Remarks</label>
    <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($info['remarks']); ?></textarea>
</div>

</form>

</div>

<script>
function calculate() {
    let grand = 0;

    document.querySelectorAll("tbody tr").forEach(row => {
        let qty = parseFloat(row.querySelector(".qty").value) || 0;
        let price = parseFloat(row.querySelector(".price").value) || 0;
        let total = qty * price;

        row.querySelector(".row-total").innerText = total.toFixed(2);
        grand += total;
    });

    document.getElementById("grandTotal").innerText = grand.toFixed(2);
}

document.querySelectorAll(".qty, .price").forEach(input => {
    input.addEventListener("input", calculate);
});
</script>

</body>
</html>
