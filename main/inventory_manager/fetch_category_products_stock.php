<?php
include 'dbcon.php';

$branch = (int)$_POST['branch_id'];
$cat    = (int)$_POST['cat_id'];

$sql = "
SELECT 
    ps.product_stock_id,
    ps.product_id,
    p.name AS product_name,
    p.code AS product_code,
    ps.qty,
    ps.lot_no,
    ps.buy_price,
    ps.sell_price,
    ps.supplier_id,
    ps.cat_id
FROM product_stock ps
JOIN product p ON p.id = ps.product_id
WHERE ps.branch_id = $branch
  AND ps.qty > 0
";

if ($cat > 0) {
    $sql .= " AND ps.cat_id = $cat ";
}

$sql .= " ORDER BY p.name ASC";

$res = mysqli_query($con, $sql);

while ($r = mysqli_fetch_assoc($res)) {
    echo "
    <tr>
        <td>{$r['product_name']}</td>
        <td>{$r['product_code']}</td>
        <td>{$r['lot_no']}</td>
        <td>{$r['buy_price']}</td>
        <td>{$r['qty']}</td>
        <td>
            <button 
                class='btn btn-sm btn-success add-product'
                data-stockid='{$r['product_stock_id']}'
                data-id='{$r['product_id']}'
                data-name='{$r['product_name']}'
                data-code='{$r['product_code']}'
                data-qty='{$r['qty']}'
                data-lot='{$r['lot_no']}'
                data-buy='{$r['buy_price']}'
                data-sell='{$r['sell_price']}'
                data-supplier='{$r['supplier_id']}'
                data-cat='{$r['cat_id']}'>
                Add
            </button>
        </td>
    </tr>";
}
?>