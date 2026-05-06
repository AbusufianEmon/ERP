<?php
include 'dbcon.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    
    // Safety check: ensure we only delete if qty is 0 and branch is 4
    $check = mysqli_query($con, "SELECT branch_id, qty FROM product_stock WHERE product_stock_id = $id");
    $data = mysqli_fetch_assoc($check);

    if($data['branch_id'] == 4 && $data['qty'] == 0){
        $delete = mysqli_query($con, "DELETE FROM product_stock WHERE product_stock_id = $id");
        if($delete){
            echo "success";
        } else {
            echo "Database error";
        }
    } else {
        echo "Unauthorized delete attempt";
    }
}
?>