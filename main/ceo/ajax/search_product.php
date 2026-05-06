<?php
include('../dbcon.php');

$branch_id = intval($_GET['branch_id']);
$term = mysqli_real_escape_string($con, $_GET['term']);

$sql = "
SELECT 
    ps.product_stock_id,
    p.name,
    p.code,
    ps.sell_price
FROM product_stock ps
JOIN product p ON ps.product_id = p.id
WHERE ps.branch_id = $branch_id
AND ps.qty > 0
AND (p.name LIKE '%$term%' OR p.code LIKE '%$term%')
LIMIT 10
";

$res = mysqli_query($con, $sql);

while ($r = mysqli_fetch_assoc($res)) {
    echo "
    <div class='list-group-item product-item'
        data-id='{$r['product_stock_id']}'
        data-name='{$r['name']}'
        data-code='{$r['code']}'
        data-price='{$r['sell_price']}'>
        {$r['name']} ({$r['code']})
    </div>";
}
