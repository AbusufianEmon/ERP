<?php
include 'dbcon.php';

$branch = isset($_POST['branch_id']);
$cat    = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;

if ($branch === 0) {
    echo "<tr><td colspan='6' class='text-center text-danger'>Branch ID missing.</td></tr>";
    exit;
}

$sql = "SELECT ps.product_stock_id, ps.product_id, p.name, p.code, ps.qty, ps.lot_no, ps.buy_price, ps.supplier_id, ps.cat_id, ps.branch_id 
        FROM product_stock ps 
        JOIN product p ON p.id = ps.product_id 
        WHERE ps.branch_id = $branch AND ps.qty > 0";

if ($cat > 0) { $sql .= " AND ps.cat_id = $cat "; }

$res = mysqli_query($con, $sql);

while ($r = mysqli_fetch_assoc($res)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($r['name']); ?></td>
        <td><?php echo htmlspecialchars($r['code']); ?></td>
        <td><?php echo $r['lot_no']; ?></td>
        <td><?php echo $r['buy_price']; ?></td>
        <td><?php echo $r['qty']; ?></td>
        <td>
            <button class="btn btn-sm btn-success add-product" 
                data-branch="<?php echo $r['branch_id']; ?>" 
                data-stockid="<?php echo $r['product_stock_id']; ?>"
                data-id="<?php echo $r['product_id']; ?>"
                data-name="<?php echo htmlspecialchars($r['name']); ?>"
                data-lot="<?php echo $r['lot_no']; ?>"
                data-buy="<?php echo $r['buy_price']; ?>"
                data-qty="<?php echo $r['qty']; ?>"
                data-supplier="<?php echo $r['supplier_id']; ?>"
                data-cat="<?php echo $r['cat_id']; ?>">
                Add
            </button>
        </td>
    </tr>
<?php } ?>