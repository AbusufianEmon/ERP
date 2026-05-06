<?php
include('dbcon.php');

$branch_id = intval($_POST['branch_id']);
$cat_id    = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;

// Base query
$sql = "SELECT ps.*, p.name AS product_name, p.code 
        FROM product_stock ps
        LEFT JOIN product p ON ps.product_id = p.id
        WHERE ps.branch_id = '$branch_id'";

// If category selected, filter it
if(!empty($cat_id)){
    $sql .= " AND ps.cat_id = '$cat_id'";
}

$sql .= " ORDER BY p.name ASC";

$res = mysqli_query($con, $sql);

if(mysqli_num_rows($res) > 0){
    while($row = mysqli_fetch_assoc($res)){

        echo '<tr>
            <td>'.$row['product_name'].'</td>
            <td>'.$row['code'].'</td>
            <td>'.$row['lot_no'].'</td>
            <td>'.$row['buy_price'].'</td>
            <td>'.$row['qty'].'</td>
            <td>
                <button 
                    type="button" 
                    class="btn btn-primary add-product"
                    data-id="'.$row['product_id'].'"
                    data-name="'.htmlspecialchars($row['product_name']).'"
                    data-lot="'.$row['lot_no'].'"
                    data-qty="'.$row['qty'].'"
                    data-buy="'.$row['buy_price'].'"
                    data-sell="'.$row['sell_price'].'"
                >
                    Add
                </button>
            </td>
        </tr>';
    }
}else{
    echo '<tr>
            <td colspan="6" class="text-center text-danger">
                No products found in this branch
            </td>
          </tr>';
}
?>
