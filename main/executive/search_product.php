<?php
include('dbcon.php');

$key = $_POST['key'];

$sql = "
SELECT 
    ps.product_stock_id,
    ps.qty,
    ps.buy_price,
    ps.lot_no,
    b.branch_name,
    p.id,
    p.name,
    p.code
FROM product_stock ps
JOIN product p ON ps.product_id = p.id
JOIN branches b ON ps.branch_id = b.branch_id
WHERE p.name LIKE '%$key%'
   OR p.code LIKE '%$key%'
LIMIT 10
";

$res = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($res)) {
?>
<tr>
    <td><?= $row['name']; ?></td>
    <td><?= $row['code']; ?></td>
    <td><?= $row['lot_no']; ?></td>
    <td><?= $row['branch_name']; ?></td>
    <td><?= $row['qty']; ?></td>
    <td><?= $row['buy_price']; ?></td>
    <td>
        <button type="button" class="btn btn-sm btn-primary"
            onclick="addProduct(
                '<?= $row['id']; ?>',
                '<?= $row['product_stock_id']; ?>',
                '<?= $row['name']; ?>',
                '<?= $row['lot_no']; ?>',
                '<?= $row['branch_name']; ?>',
                '<?= $row['buy_price']; ?>'
            )">
            Add
        </button>
    </td>
</tr>
<?php } ?>
