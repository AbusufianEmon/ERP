<?php 
include('ini/header.php'); 
include('dbcon.php');

$invoice_id = mysqli_real_escape_string($con, $_GET['invoice_id']);
$branch_id = intval($_GET['branch_id']);

// Handle the Collection Update Logic
if (isset($_POST['btn_collect'])) {
    $update_sql = "UPDATE corporate_sales SET bill_collection_status = 1 
                   WHERE corporate_sales_invoice_id = '$invoice_id'";
    if (mysqli_query($con, $update_sql)) {
        echo "<script>
                alert('Bill Marked as Collected Successfully!');
                window.location.href='corporate_bill_pending.php?branch_id=$branch_id';
              </script>";
    }
}

// Fetch Invoice General Info (Branch Name & Corporate Name)
$info_sql = "SELECT cs.*, b.branch_name 
             FROM corporate_sales cs 
             LEFT JOIN branches b ON cs.branch_id = b.branch_id 
             WHERE cs.corporate_sales_invoice_id = '$invoice_id' LIMIT 1";
$info_exe = mysqli_query($con, $info_sql);
$invoice_info = mysqli_fetch_assoc($info_exe);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Invoice Details: <?php echo $invoice_id; ?></h6>
            <a href="corporate_bill_pending.php?branch_id=<?php echo $branch_id; ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h5 class="mb-1">Corporate Client:</h5>
                    <strong><?php echo $invoice_info['corporate_name']; ?></strong><br>
                    Corporate Code: <?php echo $invoice_info['corporate_code']; ?>
                </div>
                <div class="col-sm-6 text-right">
                    <h5 class="mb-1">Branch:</h5>
                    <strong><?php echo $invoice_info['branch_name']; ?></strong><br>
                    Collection Deadline: <?php echo date('d-M-Y', strtotime($invoice_info['bill_collection_date'])); ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_bill = 0;
                        $item_sql = "SELECT * FROM corporate_sales WHERE corporate_sales_invoice_id = '$invoice_id'";
                        $item_exe = mysqli_query($con, $item_sql);
                        $count = 1;
                        while($item = mysqli_fetch_assoc($item_exe)) {
                            $subtotal = $item['qty'] * $item['selling_price'];
                            $total_bill += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo $item['product_code']; ?></td>
                            <td><?php echo $item['product_name']; ?></td>
                            <td class="text-center"><?php echo $item['qty']; ?></td>
                            <td class="text-right"><?php echo number_format($item['selling_price'], 2); ?></td>
                            <td class="text-right"><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Grand Total:</th>
                            <th class="text-right text-primary h5"><?php echo number_format($total_bill, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 p-3 border rounded bg-light">
                <form method="POST">
                    <p class="text-muted small">By clicking 'Confirm Collection', you acknowledge that the payment for this invoice has been received and the status will be updated to 'Collected'.</p>
                    <button type="submit" name="btn_collect" class="btn btn-success btn-lg btn-block" onclick="return confirm('Are you sure the payment is received?')">
                        <i class="fa fa-check-circle"></i> Confirm Bill Collection
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>