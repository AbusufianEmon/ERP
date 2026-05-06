<?php
include('dbcon.php');

$query     = mysqli_real_escape_string($con, $_POST['query']);
$branch_id = intval($_POST['branch_id']);

$sql = "SELECT ps.*, p.name AS product_name, p.code 
        FROM product_stock ps
        LEFT JOIN product p ON ps.product_id = p.id
        WHERE ps.branch_id = '$branch_id'
        AND (p.name LIKE '%$query%' OR p.code LIKE '%$query%')
        ORDER BY p.name ASC 
        LIMIT 10";

$res = mysqli_query($con, $sql);

if(mysqli_num_rows($res) > 0){
    while($row = mysqli_fetch_assoc($res)){

        echo '<a href="#" 
            class="list-group-item list-group-item-action product-item"
            data-id="'.$row['product_id'].'"
            data-name="'.htmlspecialchars($row['product_name']).'"
            data-lot="'.$row['lot_no'].'"
            data-qty="'.$row['qty'].'"
            data-buy="'.$row['buy_price'].'"
            data-sell="'.$row['sell_price'].'"
        >
            '.$row['product_name'].' ('.$row['code'].') - Qty: '.$row['qty'].'
        </a>';
    }
}else{
    echo '<a href="#" class="list-group-item list-group-item-action disabled">
            No products found
          </a>';
}
?>
