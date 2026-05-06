<?php
include('ini/header.php');
include('dbcon.php');

// Fetch suppliers
$supplier_res = mysqli_query($con, "SELECT * FROM supplier");

// Fetch categories
$category_res = mysqli_query($con, "SELECT * FROM category");

// Generate random lot number
function generateLotNo() {
    return "LOT_" . date("Ymd") . "_" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
}
$lot_no = generateLotNo();

// Generate unique invoice number
function generateUniqueInvoiceNo($con) {
    do {
        $invoice_no = 'INV' . date('Ymd') . '-' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        $check1 = mysqli_query($con, "SELECT * FROM sup_invoice WHERE invoice_no='$invoice_no' LIMIT 1");
        $check2 = mysqli_query($con, "SELECT * FROM purchase_order WHERE invoice_no='$invoice_no' LIMIT 1");
    } while (mysqli_num_rows($check1) > 0 || mysqli_num_rows($check2) > 0);
    return $invoice_no;
}
?>

<h1 class="text-center">Add Purchase Order :</h1>
<div class="row">
    <div class="col-md-10 offset-1 card text-success" style="background:white; font-weight:bold;">
        <div class="card-body">
            <form method="post" action="" id="stock_form">

                <label>Order To :</label>
                <select name="sup_id" id="sup_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    <?php while ($s = mysqli_fetch_assoc($supplier_res)) { ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['sup_name']); ?></option>
                    <?php } ?>
                </select><br>

                <div id="adjustment_section" style="display:none;">
                    <h5>Available Faulty Pools (supplier)</h5>
                    <div id="faulty_list" class="mb-3"></div>
                </div>
                <hr>

                <label>Lot Number :</label>
                <input type="text" name="lot_no" class="form-control" value="<?php echo $lot_no; ?>" readonly style="background:#f0f0f0;"><br>

                <label>Search Product :</label>
                <input type="text" id="product_input" class="form-control" placeholder="Type product name or code">
                <div id="product_list" class="list-group position-absolute" style="z-index:999; max-height:300px; overflow:auto;"></div><br>

                <label>Select Category :</label>
                <select id="category_select" class="form-control">
                    <option value="">Select Category</option>
                    <?php while ($c = mysqli_fetch_assoc($category_res)) { ?>
                        <option value="<?php echo $c['cat_id']; ?>"><?php echo htmlspecialchars($c['cat_name']); ?></option>
                    <?php } ?>
                </select><br>

                <div id="category_products" style="display:none;">
                    <h5>Products in Selected Category:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="category_product_table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div><br>

                <h5>Selected Products:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="product_table">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width:220px">Product Name</th>
                                <th style="width:90px">Qty</th>
                                <th style="width:120px">Buying Price</th>
                                <th style="width:120px">Selling Price</th>
                                <th style="width:140px">Paid Amount</th>
                                <th style="width:140px">Due Amount</th>
                                <th style="min-width:240px">Adjust With (Faulty)</th>
                                <th style="width:60px">Remove</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <label>Total Paid Amount :</label>
                <input type="number" name="total_paid_amount" id="total_paid_amount" class="form-control mb-2" step="0.01" value="0" readonly>

                <label>Total Due Amount :</label>
                <input type="number" name="total_due_amount" id="total_due_amount" class="form-control mb-3" step="0.01" value="0" readonly>

                <input type="submit" name="submit" class="btn btn-success form-control" value="Add Purchase Order">
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
$('#sup_id').change(function(){
    let supplierId = $(this).val();
    $('#faulty_list').empty();
    $('#adjustment_section').hide();
    if(!supplierId) return;
    $.post('fetch_faulty_items.php', {supplier_id: supplierId}, function(data){
        $('#faulty_list').html(data);
        $('#adjustment_section').show();
    });
});

$('#product_input').keyup(function(){
    let q = $(this).val();
    if(q.length < 1){ $('#product_list').hide(); return; }
    $.post('product_search.php', {query: q}, function(data){
        $('#product_list').html(data).show();
    });
});

$(document).on('click', '.product-item', function(e){
    e.preventDefault();
    let productId = $(this).data('id');
    let productName = $(this).data('name');
    addProductRow(productId, productName);
    $('#product_list').hide();
    $('#product_input').val('');
});

$('#category_select').change(function(){
    let cat_id = $(this).val();
    $('#category_product_table tbody').empty();
    $('#category_products').hide();
    if(!cat_id) return;
    $.post('fetch_category_products_purchashe.php', {cat_id: cat_id}, function(data){
        $('#category_product_table tbody').html(data);
        $('#category_products').show();
    });
});

$(document).on('click', '.add-product', function(e){
    e.preventDefault();
    let pid = $(this).data('id'), pname = $(this).data('name');
    addProductRow(pid, pname);
});

function addProductRow(productId, productName){
    if($('#row_'+productId).length) return;
    let row = `
      <tr id="row_${productId}">
        <td>
            ${productName}
            <input type="hidden" name="products[]" value="${productId}">
        </td>
        <td><input type="number" name="qty[]" class="form-control qty" value="1" min="1" required style='width:100px;'></td>
        <td><input type="number" name="buy_price[]" class="form-control buy_price" step="0.01" min="1" required style='width:100px;'></td>
        <td><input type="number" name="sell_price[]" class="form-control sell_price" step="0.01" min="1" required style='width:100px;'></td>
        <td><input type="number" name="paid_amount[]" class="form-control paid_amount" step="0.01" min="1" required style='width:100px;'></td>
        <td><input type="number" name="due_amount[]" class="form-control due_amount" step="0.01" value="0" readonly style='width:100px;'></td>
        <td>
           <div class="d-flex gap-2 align-items-center">
             <select class="form-control faulty-select" name="adjusted_with[]" data-product="${productId}">
                <option value="">-- Select Faulty (optional) --</option>
             </select>
             <input type="number" class="form-control adjust_amount_input" name="adjust_amount[]" step="0.01" value="0" style="max-width:140px" placeholder="Amt">
           </div>
           <small class="form-text text-muted faulty-available"></small>
        </td>
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
      </tr>
    `;
    $('#product_table tbody').append(row);

    let supplierId = $('#sup_id').val();
    if(supplierId){ loadFaultyOptionsForRow(supplierId, productId); }
    calculateTotals();
}

$(document).on('click', '.remove-row', function(){
    $(this).closest('tr').remove();
    calculateTotals();
});

$('#sup_id').on('change', function(){
    let supplierId = $(this).val();
    $('#product_table tbody tr').each(function(){
        let productId = $(this).find('.faulty-select').data('product');
        if(productId) loadFaultyOptionsForRow(supplierId, productId);
    });
});

function loadFaultyOptionsForRow(supplierId, productId){
    if(!supplierId) return;
    $.post('fetch_faulty_for_product.php', {supplier_id: supplierId, product_id: productId}, function(resp){
        let $select = $('#row_'+productId).find('.faulty-select');
        $select.html(resp);
        let totalAvail = 0;
        $select.find('option[data-available]').each(function(){
            totalAvail += parseFloat($(this).attr('data-available')) || 0;
        });
        if(totalAvail > 0){
            $('#row_'+productId).find('.faulty-available').text('Total adjustable: ' + totalAvail.toFixed(2));
        } else {
            $('#row_'+productId).find('.faulty-available').text('No adjustable amount.');
        }
    }, 'html');
}

$(document).on('change', '.faulty-select', function(){
    let opt = $(this).find('option:selected');
    let available = parseFloat(opt.attr('data-available')) || 0;
    $(this).closest('tr').find('.adjust_amount_input').val(available.toFixed(2));
    calculateTotals();
});

$(document).on('input', '.qty, .buy_price, .sell_price, .paid_amount, .adjust_amount_input', function(){
    calculateTotals();
});

function calculateTotals(){
    let totalPaid = 0, totalDue = 0;
    $('#product_table tbody tr').each(function(){
        let qty = parseFloat($(this).find('.qty').val()) || 0;
        let buy = parseFloat($(this).find('.buy_price').val()) || 0;
        let paid = parseFloat($(this).find('.paid_amount').val()) || 0;
        let adjust = parseFloat($(this).find('.adjust_amount_input').val()) || 0;
        
        let totalCost = qty * buy;

        // Validation: Paid amount cannot be greater than total cost
        if(paid > totalCost) {
            paid = totalCost;
            $(this).find('.paid_amount').val(paid.toFixed(2));
        }

        let paidTotal = paid + adjust;
        let due = totalCost - paidTotal;
        if(due < 0) due = 0;

        $(this).find('.due_amount').val(due.toFixed(2));
        totalPaid += paidTotal;
        totalDue += due;
    });
    $('#total_paid_amount').val(totalPaid.toFixed(2));
    $('#total_due_amount').val(totalDue.toFixed(2));
}
</script>

<?php
if(isset($_POST['submit'])){
    $sup_id = intval($_POST['sup_id']);
    $lot_no = mysqli_real_escape_string($con, $_POST['lot_no']);
    $products = $_POST['products'] ?? [];
    $qty_arr = $_POST['qty'] ?? [];
    $buy_price_arr = $_POST['buy_price'] ?? [];
    $sell_price_arr = $_POST['sell_price'] ?? [];
    $paid_amount_arr = $_POST['paid_amount'] ?? [];
    $adjusted_with_arr = $_POST['adjusted_with'] ?? [];
    $adjust_amount_arr = $_POST['adjust_amount'] ?? [];
    $datee = date("Y-m-d H:i:s");

    $invoice_no = generateUniqueInvoiceNo($con);

    mysqli_begin_transaction($con);
    try {
        if(empty($products)) throw new Exception("Please select at least one product.");

        for($i=0; $i<count($products); $i++){
            $product_id = intval($products[$i]);
            $qty = floatval($qty_arr[$i]);
            $buy_price = floatval($buy_price_arr[$i]);
            $sell_price = floatval($sell_price_arr[$i]);
            $paid_input = floatval($paid_amount_arr[$i]);
            $adjusted_with = trim($adjusted_with_arr[$i]) ?: null;
            $adjust_amount = floatval($adjust_amount_arr[$i]);

            // PHP VALIDATION
            if($buy_price < 1 || $sell_price < 1 || $paid_input < 1) {
                throw new Exception("Buying Price, Selling Price, and Paid Amount must be 1 or greater and cannot be negative.");
            }

            $total_cost = $qty * $buy_price;
            if($paid_input > $total_cost) {
                throw new Exception("Paid Amount cannot be greater than the total item cost ($total_cost).");
            }

            if($adjusted_with){
                $sfid_safe = mysqli_real_escape_string($con, $adjusted_with);
                $q_sf = mysqli_query($con, "SELECT id, adjustable_amount FROM stock_faulty_items WHERE stock_faulty_id = '$sfid_safe' AND product_id = $product_id AND adjustable_amount > 0 LIMIT 1 FOR UPDATE");
                $sf_row = mysqli_fetch_assoc($q_sf);
                if(!$sf_row) throw new Exception("No adjustable faulty amount found for a selected item.");
                $sf_item_id = intval($sf_row['id']);
                $available_adj = floatval($sf_row['adjustable_amount']);
                if($adjust_amount > $available_adj) throw new Exception("Adjust amount exceeds available for product ID: $product_id");
            } else {
                $sf_item_id = null;
                $adjust_amount = 0;
            }

            $adj_with_sql = ($adjusted_with) ? "'" . mysqli_real_escape_string($con, $adjusted_with) . "'" : "NULL";
            $paid_after_adjust = $paid_input + $adjust_amount;
            $due_after = max(0, $total_cost - $paid_after_adjust);

            $sql_po = "INSERT INTO purchase_order (product_id, supplier_id, qty, lot_no, buy_price, sell_price, paid_amount, status, invoice_no, adjusted_with, created_at)
                       VALUES ($product_id, $sup_id, $qty, '$lot_no', $buy_price, $sell_price, $paid_after_adjust, 0, '$invoice_no', $adj_with_sql, '$datee')";
            if(!mysqli_query($con, $sql_po)) throw new Exception(mysqli_error($con));

            $prod = mysqli_fetch_assoc(mysqli_query($con, "SELECT name, code FROM product WHERE id = $product_id LIMIT 1"));
            $pname = mysqli_real_escape_string($con, $prod['name']);
            $pcode = mysqli_real_escape_string($con, $prod['code']);

            $sql_si = "INSERT INTO sup_invoice (invoice_no, product_id, product_name, supp_id, paid_amount, due_amount, qty, code, adjusted_with, datee)
                       VALUES ('$invoice_no', $product_id, '$pname', $sup_id, $paid_after_adjust, $due_after, $qty, '$pcode', $adj_with_sql, '$datee')";
            if(!mysqli_query($con, $sql_si)) throw new Exception(mysqli_error($con));

            if($sf_item_id){
                $new_adj = $available_adj - $adjust_amount;
                mysqli_query($con, "UPDATE stock_faulty_items SET adjustable_amount = $new_adj WHERE id = $sf_item_id");
            }
        }

        mysqli_commit($con);
        echo "<script>swal('Success!', 'Purchase order created successfully!', 'success').then(()=>window.location='supplier_invoice.php?invoice_no=$invoice_no');</script>";
        exit;

    } catch(Exception $e){
        mysqli_rollback($con);
        $msg = addslashes($e->getMessage());
        echo "<script>swal('Error', '$msg', 'error');</script>";
    }
}
?>

<?php include('ini/footer.php'); ?>