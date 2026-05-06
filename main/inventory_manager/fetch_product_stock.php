<?php
include 'dbcon.php';

$q      = mysqli_real_escape_string($con, $_POST['query']);
$branch = (int)$_POST['branch_id'];

$sql = "
SELECT 
    ps.product_stock_id,
    ps.product_id,
    p.name AS product_name,
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
  AND (
        p.name LIKE '%$q%'
        OR p.code LIKE '%$q%'
        OR ps.product_id LIKE '%$q%'
      )
ORDER BY p.name ASC
";

$res = mysqli_query($con, $sql);

while ($r = mysqli_fetch_assoc($res)) {
    echo "
<a href='#' class='list-group-item product-item'
data-stockid='{$r['product_stock_id']}'
data-id='{$r['product_id']}'
data-name='{$r['product_name']}'
data-qty='{$r['qty']}'
data-lot='{$r['lot_no']}'
data-buy='{$r['buy_price']}'
data-sell='{$r['sell_price']}'
data-supplier='{$r['supplier_id']}'
data-cat='{$r['cat_id']}'>
{$r['product_name']} (Available: {$r['qty']})
</a>";
}
?>