<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['lot_no'])) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Lot No not specified.</div></div>";
    include('ini/footer.php');
    exit();
}

$lot_no = mysqli_real_escape_string($con, $_GET['lot_no']);

// Fetch only products with status = 0 (pending receive)
$sql = "SELECT po.*, p.name AS product_name, p.code AS product_code, p.photo,
               s.sup_name, s.sup_email, s.sup_phone
        FROM purchase_order po
        LEFT JOIN product p ON po.product_id = p.id
        LEFT JOIN supplier s ON po.supplier_id = s.id
        WHERE po.lot_no = '$lot_no' AND po.status = 0";
$res = mysqli_query($con, $sql);

// Fetch first row for header info
$header = mysqli_fetch_assoc($res);
mysqli_data_seek($res, 0);

// Handle Return Selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_selected'])) {
    if (!empty($_POST['selected_items'])) {
        $invoice_no = mysqli_real_escape_string($con, $header['invoice_no']);
        $supplier_id = intval($header['supplier_id']);
        $lot_no = mysqli_real_escape_string($con, $lot_no);

        foreach ($_POST['selected_items'] as $product_id) {
            $product_id = intval($product_id);
            $reason = mysqli_real_escape_string($con, $_POST['reason'][$product_id] ?? 'Returned');

            // Fetch item details from purchase_order
            $q = mysqli_query($con, "SELECT * FROM purchase_order WHERE lot_no='$lot_no' AND product_id=$product_id");
            $item = mysqli_fetch_assoc($q);
            if ($item) {
                $return_qty = $item['qty'];
                $buy_price = $item['buy_price'];
                $total = $return_qty * $buy_price;

                // Insert into purchase_return (without adjustable_amount or paid_amount)
                $insert_sql = "INSERT INTO purchase_return 
                    (lot_no, invoice_no, product_id, supplier_id, qty, buy_price, total, reason, return_date)
                    VALUES ('$lot_no', '$invoice_no', $product_id, $supplier_id, $return_qty, $buy_price, $total, '$reason', NOW())";
                mysqli_query($con, $insert_sql);

                // Update purchase_order: set status = 2 and paid_amount = 0
                $update_sql = "UPDATE purchase_order 
                               SET status = 2, paid_amount = 0 
                               WHERE lot_no='$lot_no' AND product_id=$product_id";
                mysqli_query($con, $update_sql);
            }
        }
        echo "<div class='container mt-3'><div class='alert alert-success'>Selected items returned successfully.</div></div>";
    } else {
        echo "<div class='container mt-3'><div class='alert alert-warning'>No items selected for return.</div></div>";
    }
}
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <div class="invoice-title mb-4">
                <h2 class="float-end">Purchase Order Receive / Return</h2>
                <h4>Lot No: <?php echo htmlspecialchars($lot_no); ?></h4>
                <p>Date: <?php echo date('Y-m-d'); ?></p>
            </div>
            <hr>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h5>Supplier:</h5>
                    <?php if ($header): ?>
                        <p><strong><?php echo htmlspecialchars($header['sup_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($header['sup_email']); ?></p>
                        <p><?php echo htmlspecialchars($header['sup_phone']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6 text-end">
                    <h5>Invoice No:</h5>
                    <p><?php echo htmlspecialchars($header['invoice_no']); ?></p>
                </div>
            </div>

            <form action="" method="POST">
                <input type="hidden" name="lot_no" value="<?php echo htmlspecialchars($lot_no); ?>">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Code</th>
                                <th>Qty</th>
                                <th>Buy Price</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($res && mysqli_num_rows($res) > 0):
                            while ($row = mysqli_fetch_assoc($res)):
                                $total = $row['qty'] * $row['buy_price'];
                                $due = $total - $row['paid_amount'];
                        ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="selected_items[]" value="<?php echo $row['product_id']; ?>" class="item-checkbox">
                                </td>
                                <td class="text-center">
                                    <img src="img/products/<?php echo htmlspecialchars($row['photo']); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                </td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                <td class="text-center"><?php echo $row['qty']; ?></td>
                                <td class="text-end"><?php echo number_format($row['buy_price'],2); ?></td>
                                <td class="text-end total-amount"><?php echo number_format($total,2); ?></td>
                                <td class="text-end">
                                    <input type="number" step="0.01" name="paid_amount[<?php echo $row['product_id']; ?>]" value="<?php echo $row['paid_amount']; ?>" disabled="" class="form-control form-control-sm text-end paid-input" style="min-width:100px;">
                                </td>
                                <td class="text-end due-amount"><?php echo number_format($due,2); ?></td>
                                <td>
                                    <input type="text" name="reason[<?php echo $row['product_id']; ?>]" class="form-control form-control-sm" placeholder="Reason for return">
                                </td>
                            </tr>
                        <?php endwhile;
                        else:
                            echo "<tr><td colspan='10' class='text-center'>No products pending for receive.</td></tr>";
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" name="receive_selected" formaction="receive_lot.php" class="btn btn-success btn-lg">Receive</button>
                    <button type="submit" name="return_selected" class="btn btn-danger btn-lg">Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function(){
    document.querySelectorAll('.item-checkbox').forEach(ch => ch.checked = this.checked);
});

// Dynamic due amount update
document.querySelectorAll('.paid-input').forEach(input=>{
    input.addEventListener('input', function(){
        let row = this.closest('tr');
        let total = parseFloat(row.querySelector('.total-amount').textContent.replace(/[^0-9.-]+/g,'')) || 0;
        let paid = parseFloat(this.value) || 0;
        row.querySelector('.due-amount').textContent = '$ ' + (total - paid).toFixed(2);
    });
});
</script>
