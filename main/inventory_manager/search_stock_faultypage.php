<?php
include('dbcon.php');

if(isset($_POST['query'])){
    $query = mysqli_real_escape_string($con, $_POST['query']);
    $sql = "
        SELECT ps.*, p.name, p.code, p.photo 
        FROM product_stock ps 
        JOIN product p ON ps.product_id = p.id
        WHERE p.name LIKE '%$query%' OR p.code LIKE '%$query%' OR ps.lot_no LIKE '%$query%' 
        LIMIT 10";
    $res = mysqli_query($con, $sql);

    if(mysqli_num_rows($res) > 0){
        while($r = mysqli_fetch_assoc($res)){
            echo '
            <a href="#" class="list-group-item list-group-item-action product-item"
                data-stockid="'.$r['product_stock_id'].'"
                data-product="'.$r['product_id'].'"
                data-supplier="'.$r['supplier_id'].'"
                data-cat="'.$r['cat_id'].'"
                data-name="'.$r['name'].'"
                data-lot="'.$r['lot_no'].'"
                data-buy="'.$r['buy_price'].'">
                '.$r['name'].' ('.$r['code'].') - Lot: '.$r['lot_no'].'
            </a>';
        }
    } else {
        echo '<p class="list-group-item">No products found</p>';
    }
}
?>
