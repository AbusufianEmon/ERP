<?php
// get_faulty_details.php
include('dbcon.php');

if (!isset($_POST['stock_faulty_id'])) {
    echo '<div class="alert alert-danger">Missing pool id</div>';
    exit;
}
$pool = mysqli_real_escape_string($con, $_POST['stock_faulty_id']);

// fetch items in this pool (show adjustable_amount remaining)
$sql = "SELECT sfi.*, p.name AS product_name, p.code 
        FROM stock_faulty_items sfi
        LEFT JOIN product p ON sfi.product_id = p.id
        WHERE sfi.stock_faulty_id = '$pool'
        ORDER BY sfi.id ASC";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    echo '<div class="text-muted">No items found for this pool.</div>';
    exit;
}

$total = 0;
?>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Lot No</th>
                <th>Qty</th>
                <th>Buy Price</th>
                <th>Adjustable Amount</th>
                <th>Remarks</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
<?php
$i = 1;
while ($r = mysqli_fetch_assoc($res)) {
    $total += (float)$r['adjustable_amount'];
    $status_text = ($r['status']==1) ? 'Returned' : 'Pending';
    echo '<tr>';
    echo '<td>'.$i++.'</td>';
    echo '<td>'.htmlspecialchars($r['product_name']).' ('.htmlspecialchars($r['code']).')</td>';
    echo '<td>'.htmlspecialchars($r['lot_no']).'</td>';
    echo '<td>'.intval($r['qty']).'</td>';
    echo '<td>$ '.number_format($r['buy_price'],2).'</td>';
    echo '<td>$ '.number_format($r['adjustable_amount'],2).'</td>';
    echo '<td>'.htmlspecialchars($r['remarks']).'</td>';
    echo '<td>'.$status_text.'</td>';
    echo '</tr>';
}
?>
        </tbody>
        <tfoot>
            <tr class="table-info">
                <td colspan="5" class="text-end"><strong>Total Adjust.</strong></td>
                <td colspan="3"><strong>$ <?php echo number_format($total,2); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>
