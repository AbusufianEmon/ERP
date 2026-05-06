<?php
include('ini/header.php');
include('dbcon.php');

/* ===============================
   Get & Secure Branch ID
================================ */
if (!isset($_GET['branch_id'])) {
    echo "<div class='alert alert-danger'>Branch not selected</div>";
    exit;
}

$branch_id = intval($_GET['branch_id']); // secure input

/* ===============================
   Fetch Stock ONLY for this Branch
================================ */
$sql = "
SELECT 
    ps.product_stock_id,
    ps.stock_id,
    ps.product_id,
    ps.supplier_id,
    ps.cat_id,
    ps.branch_id,
    ps.qty,
    ps.lot_no,
    ps.buy_price,
    ps.sell_price,
    ps.created_at,
    p.name AS product_name,
    p.code AS product_code,
    p.photo,
    s.sup_name,
    c.cat_name,
    b.branch_name
FROM product_stock ps
LEFT JOIN product p ON ps.product_id = p.id
LEFT JOIN supplier s ON ps.supplier_id = s.id
LEFT JOIN category c ON ps.cat_id = c.cat_id
LEFT JOIN branches b ON ps.branch_id = b.branch_id
WHERE ps.branch_id = $branch_id
ORDER BY p.name ASC
";

$result = mysqli_query($con, $sql);

/* ===============================
   Total Qty PER Product (Branch Wise)
================================ */
$totals = [];
$total_sql = "
SELECT product_id, SUM(qty) AS total_qty
FROM product_stock
WHERE branch_id = $branch_id
GROUP BY product_id
";

$total_res = mysqli_query($con, $total_sql);
while ($row = mysqli_fetch_assoc($total_res)) {
    $totals[$row['product_id']] = $row['total_qty'];
}
?>


<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Product Stock (Branch: <?php echo htmlspecialchars($data['branch_name']); ?>)
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Photo</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Branch</th>
                            <th>Lot No</th>
                            <th>Quantity</th>
                            <th>Buy Price</th>
                            <th>Sell Price</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {

                                $product_id = $row['product_id'];
                                $overall_qty = $totals[$product_id] ?? 0;

                                $color_class = ($overall_qty < 10)
                                    ? "text-danger font-weight-bold"
                                    : "text-success font-weight-bold";
                                ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['photo'])) { ?>
                                            <img src="../inventory_manager/img/products/<?php echo $row['photo']; ?>" width="50">
                                        <?php } else { ?>
                                            <img src="img/no-image.png" width="50">
                                        <?php } ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cat_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sup_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lot_no']); ?></td>

                                    <td class="<?php echo $color_class; ?>">
                                        <?php echo $row['qty']; ?>
                                    </td>

                                    <td><?php echo number_format($row['buy_price'], 2); ?></td>
                                    <td><?php echo number_format($row['sell_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="11" class="text-center text-danger">
                                    No stock found for this branch
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
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'print']
    });
});
</script>

<?php include('ini/footer.php'); ?>
