<?php 
session_start();

if(isset($_SESSION['email'])) {
    $id = $_SESSION['id'];
    include('dbcon.php');
    
    if(isset($_GET['invoice_no'])) {
        $invoice_no = mysqli_real_escape_string($con, $_GET['invoice_no']);
        
        // Fetch invoice data for all products including buy_price
        $sql = "SELECT si.invoice_no AS invoice_no, si.product_id, si.product_name, 
                       p.code, si.paid_amount, si.due_amount, si.qty, 
                       supplier.sup_name, supplier.sup_email, supplier.sup_phone, si.datee,
                       po.buy_price
                FROM sup_invoice si
                LEFT JOIN product p ON si.product_id = p.id
                LEFT JOIN supplier ON si.supp_id = supplier.id 
                LEFT JOIN purchase_order po ON po.invoice_no = si.invoice_no AND po.product_id = si.product_id
                WHERE si.invoice_no = '$invoice_no'";
        
        $exe = mysqli_query($con, $sql);

        // Fetch supplier + invoice header data (first row)
        $data = mysqli_fetch_assoc($exe);

        // Reset pointer to fetch all rows later
        mysqli_data_seek($exe, 0);
    } else {
        $data = null;
    }
} else {
    header('location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice Update - Dual Table Sync</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="js/sweetalert.min.js"></script>
    <style type="text/css">
        body { margin-top: 20px; background-color: #eee; }
        .card { box-shadow: 0 20px 27px 0 rgb(0 0 0 / 5%); border-radius: 1rem; }
        .table thead { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="invoice-title">
                            <h4 class="float-end font-size-15 text-primary">Invoice #<?php echo htmlspecialchars($invoice_no); ?></h4>
                            <div class="mb-4">
                                <h2 class="mb-1 text-success fw-bold">ETM.ERP</h2>
                            </div>
                            <div class="text-muted">
                                <p class="mb-1">Inventory Management Solutions</p>
                                <p><i class="fa fa-phone me-1"></i> Support Desk Linked</p>
                            </div>
                        </div>
                        <hr class="my-4">

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="text-muted">
                                    <h5 class="font-size-16 mb-3 text-uppercase">Billed To:</h5>
                                    <?php if ($data): ?>
                                        <h5 class="font-size-15 mb-2"><?php echo $data['sup_name'] ?></h5>
                                        <p class="mb-1"><?php echo $data['sup_email'] ?></p>
                                        <p><?php echo $data['sup_phone'] ?></p>
                                    <?php else: ?>
                                        <p>No customer data found for this invoice.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <div>
                                    <h5 class="font-size-15 mb-1">Status:</h5>
                                    <span class="badge bg-warning text-dark">Awaiting Payment Update</span>
                                </div>
                                <?php if ($data): ?>
                                    <div class="mt-4">
                                        <h5 class="font-size-15 mb-1">Invoice Date:</h5>
                                        <p><?php echo date('d M, Y', strtotime($data['datee'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="py-2">
                            <h5 class="font-size-15">Order Summary & Payment Adjustment</h5>
                            <form action="" method="post">
                                <div class="table-responsive">
                                    <table class="table align-middle table-nowrap table-centered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Product Code</th>
                                                <th class="text-center">Quantity</th>
                                                <th width="200">Paid Amount</th>
                                                <th class="text-end">Due Amount (Auto)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($data): ?>
                                                <?php while($row = mysqli_fetch_assoc($exe)): ?>
                                                    <tr>
                                                        <td><strong><?php echo $row['product_name'] ?></strong></td>
                                                        <td><code><?php echo $row['code'] ?></code></td>
                                                        <td class="text-center"><?php echo $row['qty']; ?></td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" step="0.01" min="0" name="paid_amount[<?php echo $row['product_id']; ?>]" 
                                                                       value="<?php echo $row['paid_amount']; ?>" class="form-control paid_amount" 
                                                                       data-buy-price="<?php echo $row['buy_price']; ?>" data-qty="<?php echo $row['qty']; ?>">
                                                            </div>
                                                        </td>
                                                        <td class="due_amount text-end fw-bold text-danger">
                                                            <?php 
                                                            $due_amount = ($row['buy_price'] * $row['qty']) - $row['paid_amount'];
                                                            echo number_format($due_amount, 2); 
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-print-none mt-4 border-top pt-3">
                                    <div class="float-end">
                                        <a href="supplier_invoice_list.php" class="btn btn-light me-2">Cancel</a>
                                        <input type="submit" name="submit" class="btn btn-success shadow-sm px-4" value="Update">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>

<script>
// Live due_amount calculation
document.querySelectorAll('.paid_amount').forEach(function(input){
    input.addEventListener('input', function(){
        let row = input.closest('tr');
        let buy_price = parseFloat(input.dataset.buyPrice) || 0;
        let qty = parseFloat(input.dataset.qty) || 0;
        let paid = parseFloat(input.value) || 0;

        // Force value to 0 if negative is typed manually
        if(paid < 0) {
            input.value = 0;
            paid = 0;
        }

        let due = (buy_price * qty) - paid;
        row.querySelector('.due_amount').textContent = due.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });
});
</script>

</body>
</html>

<?php 
if (isset($_POST['submit'])) {
    foreach($_POST['paid_amount'] as $product_id => $paid_amount) {
        
        // Server-side check: skip update if amount is negative
        if ($paid_amount < 0) {
            continue; 
        }

        $product_id_safe = mysqli_real_escape_string($con, $product_id);
        $paid_amount_safe = mysqli_real_escape_string($con, $paid_amount);

        // 1. Fetch buy_price & qty to calculate due_amount for sup_invoice
        $result = mysqli_query($con, "SELECT po.buy_price, si.qty 
                                      FROM sup_invoice si 
                                      LEFT JOIN purchase_order po ON po.invoice_no=si.invoice_no AND po.product_id=si.product_id 
                                      WHERE si.invoice_no='$invoice_no' AND si.product_id='$product_id_safe'");
        
        $calc_row = mysqli_fetch_assoc($result);
        
        $qty = $calc_row['qty'];
        $buy_price = $calc_row['buy_price'];
        $due_amount = ($buy_price * $qty) - $paid_amount_safe;

        // 2. Update sup_invoice Table
        $update_si = "UPDATE sup_invoice 
                      SET paid_amount='$paid_amount_safe', 
                          due_amount='$due_amount' 
                      WHERE invoice_no='$invoice_no' AND product_id='$product_id_safe'";
        mysqli_query($con, $update_si);

        // 3. Update purchase_order Table (Syncing the paid_amount)
        $update_po = "UPDATE purchase_order 
                      SET paid_amount='$paid_amount_safe' 
                      WHERE invoice_no='$invoice_no' AND product_id='$product_id_safe'";
        mysqli_query($con, $update_po);
    }

    echo '<script>
        swal({
            title: "Sync Complete!",
            text: "Invoice and Purchase Order tables updated successfully (Negative values were ignored)",
            icon: "success"
        }).then(function() {
            window.location = "supplier_invoice.php?invoice_no=' . $invoice_no . '";
        });
    </script>';
}
?>