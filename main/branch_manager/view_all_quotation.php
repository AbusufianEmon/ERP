<?php
include('ini/header.php');
include('dbcon.php');

$branch_id = $_GET['branch_id'];
$sql = "
SELECT 
    cq.corporate_quotation_id,
    cq.corporate_quotation_invoice_id,
    cq.product_name,
    cq.qty,
    cq.offer_price,
    cq.manager_approvel_status,
    cc.corporate_name,
    cc.corporate_code,

    (
        SELECT SUM(q.qty * q.offer_price)
        FROM corporate_quotation q
        WHERE q.corporate_quotation_invoice_id = cq.corporate_quotation_invoice_id
    ) AS total_offer_amount

FROM corporate_quotation cq
LEFT JOIN corporate_customer cc 
    ON cq.corporate_id = cc.corporate_id
    WHERE branch_id = $branch_id

ORDER BY 
    cq.corporate_quotation_invoice_id DESC,
    cq.corporate_quotation_id ASC
";

$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
<div class="card shadow mb-4">

<div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">
        Corporate Quotation (All Products)
    </h6>
</div>

<div class="card-body">
<div class="table-responsive">

<table class="table table-bordered table-striped text-center" id="example" width="100%">
<thead class="table-dark">
<tr>
    <th>Invoice ID</th>
    <th>Corporate</th>
    <th>Code</th>
    <th>Product</th>
    <th>Qty</th>
    <th>Offer Price</th>
    <th>Line Total</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php while ($row = mysqli_fetch_assoc($run)) { 
    $line_total = $row['qty'] * $row['offer_price'];
?>
<tr>
    <td><?= $row['corporate_quotation_invoice_id']; ?></td>

    <td><?= htmlspecialchars($row['corporate_name']); ?></td>
    <td><?= htmlspecialchars($row['corporate_code']); ?></td>

    <td><?= htmlspecialchars($row['product_name']); ?></td>

    <td><?= (int)$row['qty']; ?></td>

    <td><?= number_format($row['offer_price'], 2); ?></td>

    <td><?= number_format($line_total, 2); ?></td>


    <td>
        <?php if ($row['manager_approvel_status'] == 1) { ?>
            <span class="badge bg-success">Approved</span>
        <?php } else { ?>
            <span class="badge bg-warning text-dark">Pending</span>
        <?php } ?>
    </td>

    <td>
        <a href="corporate_quotation_view.php?invoice_id=<?= $row['corporate_quotation_invoice_id']; ?>"
           class="btn btn-info btn-sm">
           View
        </a>
    </td>
</tr>
<?php } ?>
</tbody>

</table>

</div>
</div>
</div>
</div>

<!-- DataTables -->
<script>
$(document).ready(function () {
    $('#example').DataTable({
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'print']
    });
});
</script>

<?php include('ini/footer.php'); ?>
