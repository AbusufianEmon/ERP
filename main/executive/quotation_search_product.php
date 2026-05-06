<?php
include('dbcon.php');

if(!isset($_POST['key']) || !isset($_POST['branch_id'])) exit;

$key = mysqli_real_escape_string($con, $_POST['key']);
$branch_id = intval($_POST['branch_id']);

$sql = "
SELECT 
    ps.product_stock_id,
    ps.product_id,
    ps.qty,
    ps.lot_no,
    ps.buy_price,
    p.name,
    p.code,
    br.branch_name
FROM product_stock ps
LEFT JOIN product p ON ps.product_id = p.id
LEFT JOIN branches br ON ps.branch_id = br.branch_id
WHERE 
    ps.branch_id = '$branch_id'
AND 
    ps.qty > 0
AND 
    (p.name LIKE '%$key%' OR p.code LIKE '%$key%')
LIMIT 20
";

$res = mysqli_query($con,$sql);

if(mysqli_num_rows($res)==0){
    echo "<tr><td colspan='7'>No product found</td></tr>";
    exit;
}

while($row=mysqli_fetch_assoc($res)){
?>
<tr>
    <td><?= $row['name']; ?></td>
    <td><?= $row['code']; ?></td>
    <td><?= $row['lot_no']; ?></td>
    <td><?= $row['qty']; ?></td>
    <td><?= $row['branch_name']; ?></td>
    <td><?= $row['buy_price']; ?></td>
    <td>
        <button type="button" class="btn btn-sm btn-primary"
        onclick="addProduct(
            '<?= $row['product_id']; ?>',
            '<?= $row['product_stock_id']; ?>',
            '<?= addslashes($row['name']); ?>',
            '<?= $row['lot_no']; ?>',
            '<?= $row['buy_price']; ?>'
        )">
        Add
        </button>
    </td>
</tr>
<?php } ?>
