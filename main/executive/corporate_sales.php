<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['branch_id'])) {
    die("Branch not selected");
}
$branch_id = intval($_GET['branch_id']);
$corporate_sales_invoice_id = "CS-" . date("YmdHis");
?>

<div class="container-fluid">
<div class="card shadow">
<div class="card-header bg-primary text-white">
    <h5>Corporate Sales</h5>
</div>

<div class="card-body">

<form method="post">

<!-- Search Quotation -->
<div class="row mb-3">
    <div class="col-md-6">
        <label>Search Approved Quotation</label>
        <input type="text" id="quotation_search" class="form-control" placeholder="Type quotation invoice id">
        <input type="hidden" name="quotation_invoice_id" id="quotation_invoice_id">
    </div>

    <div class="col-md-6">
        <label>Sales Invoice</label>
        <input type="text" class="form-control" value="<?= $corporate_sales_invoice_id ?>" readonly>
    </div>
</div>

<!-- Dropdown Suggestions -->
<div class="list-group mb-3" id="quotation_list"></div>

<!-- Loaded Quotation Data -->
<div id="quotation_data"></div>

<!-- GRAND TOTAL DISPLAY -->
<div class="row mt-3">
    <div class="col-md-4 offset-md-8">
        <label><b>Grand Total</b></label>
        <input type="text" id="grand_total_display" class="form-control fw-bold text-end" readonly>
    </div>
</div>

<!-- Extra Info -->
<div class="row mt-4">
    <div class="col-md-4">
        <label>Delivery Date</label>
        <input type="date" name="delivery_date" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label>Bill Collection Date</label>
        <input type="date" name="bill_collection_date" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label>Remarks</label>
        <textarea name="remarks" class="form-control"></textarea>
    </div>
</div>

<button name="submit_sales" class="btn btn-success mt-4">
    Confirm Corporate Sales
</button>

</form>

</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
<script>
// SEARCH QUOTATION (only bill_status = 0)
$('#quotation_search').keyup(function(){
    let key = $(this).val();
    if (key.length < 2) return;

    $.post('fetch_quotation_sales.php',{search:key},function(data){
        $('#quotation_list').html(data);
    });
});

// SELECT QUOTATION
function selectQuotation(invoice){
    $('#quotation_invoice_id').val(invoice);
    $('#quotation_search').val(invoice);
    $('#quotation_list').html('');

    $.post('fetch_quotation_sales.php',{invoice:invoice},function(data){
        $('#quotation_data').html(data);

        setTimeout(function(){
            let gt = $('input[name="grand_total"]').val();
            $('#grand_total_display').val(gt);
        }, 200);
    });
}
</script>

<?php
if (isset($_POST['submit_sales'])) {

    mysqli_begin_transaction($con);

    try {
        $delivery_date = $_POST['delivery_date'];
        $bill_date = $_POST['bill_collection_date'];
        $remarks = mysqli_real_escape_string($con,$_POST['remarks']);
        $quotation_invoice = mysqli_real_escape_string($con,$_POST['quotation_invoice_id']);

        $short_products = [];

        // 1️⃣ Check stock availability
        foreach ($_POST['product_id'] as $i => $pid) {

            $stock_id = (int)$_POST['product_stock_id'][$i];
            $qty = (int)$_POST['qty'][$i];

            $stock_sql = "SELECT qty, branch_id FROM product_stock WHERE product_stock_id = $stock_id";
            $stock_res = mysqli_query($con, $stock_sql);
            if (!$stock_res || mysqli_num_rows($stock_res) == 0) {
                throw new Exception("Product stock not found for stock ID $stock_id");
            }
            $stock_row = mysqli_fetch_assoc($stock_res);

            if ((int)$stock_row['branch_id'] !== (int)$branch_id) {
                throw new Exception("Product stock ID $stock_id does not belong to this branch");
            }

            $total_stock = (int)$stock_row['qty'];

            $lock_sql = "SELECT IFNULL(SUM(qty),0) AS locked_qty 
                         FROM direct_sales 
                         WHERE product_stock_id = $stock_id AND status = 0";
            $lock_res = mysqli_query($con, $lock_sql);
            $lock_row = mysqli_fetch_assoc($lock_res);
            $locked_qty = (int)$lock_row['locked_qty'];

            $available_qty = $total_stock - $locked_qty;

            if ($qty > $available_qty) {
                $short_products[] = $_POST['product_name'][$i] . " (Available: $available_qty, Required: $qty)";
            }
        }

        if (count($short_products) > 0) {
            $message = implode("<br>", $short_products);
            throw new Exception($message);
        }

        // 2️⃣ Insert corporate sales and adjust stock
        foreach ($_POST['product_id'] as $i => $pid) {

            $stock_id = (int)$_POST['product_stock_id'][$i];
            $qty = (int)$_POST['qty'][$i];
            $cq_id = (int)$_POST['corporate_quotation_id'][$i];

            $price_sql = "SELECT buy_price, offer_price 
                          FROM corporate_quotation 
                          WHERE corporate_quotation_id = $cq_id 
                          AND product_id = $pid 
                          LIMIT 1";
            $price_res = mysqli_query($con, $price_sql);
            $price_row = mysqli_fetch_assoc($price_res);
            $buy_price = (float)$price_row['buy_price'];
            $selling_price = (float)$price_row['offer_price'];

            $insert_sql = "
                INSERT INTO corporate_sales (
                    corporate_sales_invoice_id,
                    corporate_quotation_id,
                    corporate_id,
                    product_id,
                    product_stock_id,
                    corporate_code,
                    product_code,
                    corporate_name,
                    product_name,
                    branch_id,
                    qty,
                    buy_price,
                    selling_price,
                    delivery_date,
                    bill_collection_date,
                    remarks
                ) VALUES (
                    '$corporate_sales_invoice_id',
                    '".mysqli_real_escape_string($con,$_POST['corporate_quotation_id'][$i])."',
                    '".mysqli_real_escape_string($con,$_POST['corporate_id'][$i])."',
                    '$pid',
                    '$stock_id',
                    '".mysqli_real_escape_string($con,$_POST['corporate_code'][$i])."',
                    '".mysqli_real_escape_string($con,$_POST['product_code'][$i])."',
                    '".mysqli_real_escape_string($con,$_POST['corporate_name'][$i])."',
                    '".mysqli_real_escape_string($con,$_POST['product_name'][$i])."',
                    '$branch_id',
                    '$qty',
                    '$buy_price',
                    '$selling_price',
                    '$delivery_date',
                    '$bill_date',
                    '$remarks'
                )
            ";
            if (!mysqli_query($con, $insert_sql)) {
                throw new Exception("Failed to insert corporate sale for product ID $pid");
            }

            $update_stock = "UPDATE product_stock SET qty = qty - $qty WHERE product_stock_id = '$stock_id'";
            if (!mysqli_query($con, $update_stock)) {
                throw new Exception("Failed to update stock for stock ID $stock_id");
            }
        }

        // 3️⃣ Mark quotation as billed
        $update_bill = "
            UPDATE corporate_quotation 
            SET bill_status = 1 
            WHERE corporate_quotation_invoice_id = '$quotation_invoice'
        ";
        mysqli_query($con, $update_bill);

        mysqli_commit($con);

        echo "<script>
            swal('Success','Corporate Sale Completed','success').then(()=>{window.location='view_all_quotation.php'});
        </script>";

    } catch (Exception $e) {
        mysqli_rollback($con);
        $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo "<script>
            swal({title:'Error', html:'$msg', icon:'error'});
        </script>";
    }
}
?>

<?php include('ini/footer.php'); ?>
