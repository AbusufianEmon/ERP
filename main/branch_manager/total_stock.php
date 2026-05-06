<?php
include('ini/header.php');
include('dbcon.php');

/* ===============================
   Fetch ALL Stock (All Branches)
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
ORDER BY p.name ASC
";

$result = mysqli_query($con, $sql);

/* ===============================
   Total Qty PER Product (All Branches)
================================ */
$totals = [];
$total_sql = "SELECT product_id, SUM(qty) AS total_qty FROM product_stock GROUP BY product_id";
$total_res = mysqli_query($con, $total_sql);

while ($row = mysqli_fetch_assoc($total_res)) {
    $totals[$row['product_id']] = $row['total_qty'];
}
?>

<!-- ===============================
     DataTables CSS
================================ -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<div class="container-fluid">
    <div class="card shadow mb-4">

        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Product Stock (All Branches)
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
                                <td colspan="11" class="text-danger text-center">
                                    No stock records found
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===============================
     DataTables JS
================================ -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Required for CSV -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
$(document).ready(function () {
    $('#example').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'CSV',
                title: 'Overall_Product_Stock'
            },
            {
                extend: 'print',
                text: 'Print Table',
                title: 'Overall Product Stock'
            }
        ]
    });
});
</script>

<?php include('ini/footer.php'); ?>
