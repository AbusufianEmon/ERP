<?php
include('ini/header.php');
include('dbcon.php');

/* ===============================
   Get Branch From URL
================================ */
if(!isset($_GET['branch_id'])){
    die("Branch not selected");
}
$branch_id = intval($_GET['branch_id']);

/* ===============================
   Generate Quotation Invoice ID
================================ */
$quotation_invoice_id = "CQ-" . date("YmdHis");

/* ===============================
   Fetch Approved Corporate Customers
================================ */
$corporates = mysqli_query(
    $con,
    "SELECT corporate_id, corporate_name, corporate_code
     FROM corporate_customer
     WHERE accounts_approvel_status = 1"
);

/* ===============================
   Save Quotation
================================ */
if (isset($_POST['save_quotation'])) {

    $corporate_id   = mysqli_real_escape_string($con, $_POST['corporate_id']);
    $corporate_code = mysqli_real_escape_string($con, $_POST['corporate_code']);
    $remarks        = mysqli_real_escape_string($con, $_POST['remarks']);

    foreach ($_POST['product_id'] as $i => $pid) {
        
        $p_id = mysqli_real_escape_string($con, $pid);
        $ps_id = mysqli_real_escape_string($con, $_POST['product_stock_id'][$i]);
        $p_name = mysqli_real_escape_string($con, $_POST['product_name'][$i]);
        $qty = floatval($_POST['qty'][$i]);
        $buy = floatval($_POST['buy_price'][$i]);
        $offer = floatval($_POST['offer_price'][$i]);

        /* ==========================
           VALIDATION: Price > 0
        ========================== */
        if ($offer <= 0) {
            echo "<script>alert('Error: Offer price for $p_name must be greater than zero.'); window.history.back();</script>";
            exit();
        }

        $sql = "INSERT INTO corporate_quotation (
                    corporate_quotation_invoice_id,
                    corporate_id,
                    product_id,
                    product_stock_id,
                    corporate_code,
                    branch_id,
                    product_name,
                    qty,
                    buy_price,
                    offer_price,
                    remarks
                ) VALUES (
                    '$quotation_invoice_id',
                    '$corporate_id',
                    '$p_id',
                    '$ps_id',
                    '$corporate_code',
                    '$branch_id',
                    '$p_name',
                    '$qty',
                    '$buy',
                    '$offer',
                    '$remarks'
                )";

        mysqli_query($con, $sql);
    }

    echo "<script>
        swal('Success','Corporate Quotation Created','success');
    </script>";
}
?>

<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Create Corporate Quotation</h5>
        </div>

        <div class="card-body">
            <form method="post">
                <input type="hidden" id="branch_id" value="<?= $branch_id ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Corporate Customer</label>
                        <select name="corporate_id" id="corporate_id" class="form-control" required>
                            <option value="">Select Corporate</option>
                            <?php while($c=mysqli_fetch_assoc($corporates)){ ?>
                            <option value="<?= $c['corporate_id']; ?>"
                                    data-code="<?= $c['corporate_code']; ?>">
                                <?= $c['corporate_name']; ?>
                            </option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="corporate_code" id="corporate_code">
                    </div>

                    <div class="col-md-6">
                        <label>Quotation Invoice</label>
                        <input type="text" class="form-control"
                               value="<?= $quotation_invoice_id; ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Search Product (Name / Code)</label>
                        <input type="text" id="product_search" class="form-control" placeholder="Type at least 2 characters...">
                    </div>
                </div>

                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Lot</th>
                            <th>Qty</th>
                            <th>Branch</th>
                            <th>Buy</th>
                            <th>Add</th>
                        </tr>
                    </thead>
                    <tbody id="product_result"></tbody>
                </table>

                <table class="table table-bordered text-center mt-4">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Lot</th>
                            <th>Qty</th>
                            <th>Offer Price</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody id="selected_products"></tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="4" class="text-end">Grand Total Offer Amount</th>
                            <th id="grand_total">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <button type="submit" name="save_quotation" class="btn btn-success px-4">
                    <i class="fas fa-save"></i> Save Quotation
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$('#corporate_id').change(function(){
    $('#corporate_code').val($(this).find(':selected').data('code'));
});

/* Product Search AJAX */
$('#product_search').keyup(function(){
    let key = $(this).val();
    let branch_id = $('#branch_id').val();

    if(key.length < 2) {
        $('#product_result').html('');
        return;
    }

    $.post('quotation_search_product.php',{
        key:key,
        branch_id:branch_id
    },function(data){
        $('#product_result').html(data);
    });
});

/* Add Product to selection */
function addProduct(pid, sid, name, lot, buy){
    let row=`
    <tr>
        <td>${name}
            <input type="hidden" name="product_id[]" value="${pid}">
            <input type="hidden" name="product_stock_id[]" value="${sid}">
            <input type="hidden" name="product_name[]" value="${name}">
            <input type="hidden" name="buy_price[]" value="${buy}">
        </td>
        <td>${lot}</td>
        <td><input type="number" name="qty[]" value="1" step="any" min="0.01"
                   class="form-control qty" onkeyup="calc(this)" onchange="calc(this)"></td>
        <td><input type="number" name="offer_price[]" value="${buy}" step="0.01" min="0.01"
                   class="form-control price" onkeyup="calc(this)" onchange="calc(this)"></td>
        <td class="row_total">0.00</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm"
            onclick="$(this).closest('tr').remove();calcTotal();">✖</button>
        </td>
    </tr>`;
    $('#selected_products').append(row);
    let lastRow = $('#selected_products tr:last');
    calc(lastRow.find('.qty'));
}

function calc(el){
    let r=$(el).closest('tr');
    let q=parseFloat(r.find('.qty').val())||0;
    let p=parseFloat(r.find('.price').val())||0;
    
    // UI Validation: Change color to red if price is 0 or less
    if(p <= 0) {
        r.find('.price').addClass('is-invalid');
    } else {
        r.find('.price').removeClass('is-invalid');
    }

    r.find('.row_total').text((q*p).toFixed(2));
    calcTotal();
}

function calcTotal(){
    let t=0;
    $('.row_total').each(function(){
        t+=parseFloat($(this).text())||0;
    });
    $('#grand_total').text(t.toFixed(2));
}
</script>

<style>
    .is-invalid { border: 2px solid red !important; }
</style>

<?php include('ini/footer.php'); ?>