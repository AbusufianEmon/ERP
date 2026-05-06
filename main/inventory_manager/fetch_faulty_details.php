<?php
include('dbcon.php');
$id = $_POST['stock_faulty_id'];

$q = mysqli_query($con, "SELECT * FROM stock_faulty_items WHERE stock_faulty_id='$id'");
if(mysqli_num_rows($q) > 0){
    echo "<table class='table table-bordered'>
            <thead><tr><th>Product</th><th>Qty</th><th>Buy Price</th><th>Adjustable Amount</th><th>Remarks</th></tr></thead><tbody>";
    $total_adj = 0;
    while($row = mysqli_fetch_assoc($q)){
        $prod = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM product WHERE id={$row['product_id']}"));
        $pname = $prod['name'] ?? 'Unknown';
        $total_adj += $row['adjustable_amount'];
        echo "<tr>
                <td>{$pname}</td>
                <td>{$row['qty']}</td>
                <td>{$row['buy_price']}</td>
                <td>{$row['adjustable_amount']}</td>
                <td>{$row['remarks']}</td>
              </tr>";
    }
    echo "</tbody></table>
          <div class='mt-2'>
            <label>Adjust Amount:</label>
            <input type='number' step='0.01' id='adjust_amount_$id' class='form-control' value='$total_adj'>
            <button type='button' class='btn btn-success mt-2 apply-adjustment' data-faultyid='$id'>Apply Adjustment</button>
          </div>";
} else {
    echo "<p>No details found for this stock_faulty_id.</p>";
}
?>
