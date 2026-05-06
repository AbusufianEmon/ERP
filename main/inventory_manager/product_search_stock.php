<?php
include('dbcon.php');

if(isset($_POST['query'])){
    $query = mysqli_real_escape_string($con, $_POST['query']);
    $branch_id = isset($_POST['branch_id']) ? (int)$_POST['branch_id'] : 4;

    $sql = "
        SELECT ps.product_stock_id, ps.product_id, ps.supplier_id, ps.cat_id, ps.qty, ps.lot_no,
               ps.buy_price, ps.branch_id, p.name, p.code
        FROM product_stock ps
        JOIN product p ON ps.product_id = p.id
        WHERE (p.name LIKE '%$query%' OR p.code LIKE '%$query%')
        AND ps.branch_id = $branch_id
        AND ps.qty > 0
    ";
    
    $result = mysqli_query($con, $sql);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo '<a href="#" class="list-group-item list-group-item-action product-item"
                data-stockid="'.$row['product_stock_id'].'"
                data-id="'.$row['product_id'].'"
                data-name="'.htmlspecialchars($row['name']).'"
                data-supplier="'.$row['supplier_id'].'"
                data-cat="'.$row['cat_id'].'"
                data-lot="'.$row['lot_no'].'"
                data-buy="'.$row['buy_price'].'"
                data-qty="'.$row['qty'].'"
                data-branch="'.$row['branch_id'].'">
                '.htmlspecialchars($row['name']).' ('.$row['code'].') - Lot: '.$row['lot_no'].' | Qty: '.$row['qty'].'
            </a>';
        }
    } else {
        echo '<a href="#" class="list-group-item list-group-item-action disabled">No products found for this branch</a>';
    }
}
?>