<?php
include('ini/header.php');
include('dbcon.php');

// Fetch categories for the dropdown
$category_res = mysqli_query($con, "SELECT * FROM category");

// Generate unique stock_faulty_id
function generateStockFaultyID() {
    return "SF_" . date("Ymd") . "_" . strtoupper(substr(md5(uniqid()), 0, 6));
}
$stock_faulty_id = generateStockFaultyID();
?>

<h1 class="text-center mt-3">Add Stock Faulty</h1>
<div class="row">
    <div class="col-md-11 mx-auto card shadow-sm text-dark" style="background:white; font-weight:bold;">
        <div class="card-body">
            <form method="post" action="" id="stock_faulty_form" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-4">
                        <label>Stock Faulty ID :</label>
                        <input type="text" name="stock_faulty_id" class="form-control" value="<?php echo $stock_faulty_id; ?>" readonly style="background:#f0f0f0;">
                    </div>
                    <div class="col-md-8">
                        <label>Search Product :</label>
                        <input type="text" id="product_input" class="form-control" placeholder="Type product name or code (Auto-search)">
                        <div id="product_list" class="list-group" style="position:absolute; z-index:1000; width:95%;"></div>
                    </div>
                </div>
                <br>

                <h5>Selected Faulty Products:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm text-center align-middle" id="product_table">
                        <thead class="table-success">
                            <tr>
                                <th>Product Name</th>
                                <th>Lot No</th>
                                <th>Current Qty</th>
                                <th>Faulty Qty</th>
                                <th>Buy Price</th>
                                <th>Adjustable Amount</th>
                                <th>Loss Amount</th>
                                <th>Remarks</th>
                                <th>Faulty Photo</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>

                <input type="submit" name="submit" class="btn btn-success form-control mt-4 py-2" value="Confirm & Save Stock Faulty">
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
const CURRENT_BRANCH_ID = 4; 

// AJAX Search Logic
$('#product_input').keyup(function(){
    let query = $(this).val();
    if(query != ''){
        $.ajax({
            url: 'product_search_stock.php',
            method: 'POST',
            data: {query: query, branch_id: CURRENT_BRANCH_ID},
            success: function(data){
                $('#product_list').fadeIn().html(data);
            }
        });
    } else {
        $('#product_list').fadeOut();
    }
});

// Click to add from search
$(document).on('click', '.product-item, .add-product', function(e){
    e.preventDefault();
    let btn = $(this);
    let branch = btn.data('branch');
    
    if(branch != CURRENT_BRANCH_ID){
        swal('Error!', 'This product belongs to another branch!', 'error');
        return;
    }

    let productId = btn.data('id');
    let stockId = btn.data('stockid');
    let productName = btn.data('name');
    let lotNo = btn.data('lot');
    let buyPrice = parseFloat(btn.data('buy'));
    let currentQty = parseInt(btn.data('qty'));
    let supplierId = btn.data('supplier');
    let catId = btn.data('cat');

    // Prevent duplicate lot addition
    if($('#row_'+stockId).length == 0){
        let row = `
            <tr id="row_${stockId}">
                <td class="text-start">${productName}
                    <input type="hidden" name="product_stock_id[]" value="${stockId}">
                    <input type="hidden" name="product_id[]" value="${productId}">
                    <input type="hidden" name="supplier_id[]" value="${supplierId}">
                    <input type="hidden" name="cat_id[]" value="${catId}">
                </td>
                <td><span class="badge bg-secondary">${lotNo}</span></td>
                <td><span class="current_qty_text">${currentQty}</span></td>
                <td><input type="number" name="qty[]" class="form-control qty-input" value="1" min="1" max="${currentQty}" required></td>
                <td><input type="number" name="buy_price[]" class="form-control buy-input" value="${buyPrice}" step="0.01" required></td>
                <td><input type="number" name="adjustable_amount[]" class="form-control adjust-input" value="0.00" step="0.01" required></td>
                <td><input type="number" name="loss_amount[]" class="form-control loss-input" value="${buyPrice}" readonly style="background:#f8f9fa;"></td>
                <td><input type="text" name="remarks[]" class="form-control"></td>
                <td><input type="file" name="faulty_photo[]" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>`;
        $('#product_table tbody').append(row);
    }
    $('#product_list').fadeOut();
    $('#product_input').val('');
});

// Row calculation logic
function calculateRow(row) {
    let qty = parseFloat(row.find('.qty-input').val()) || 0;
    let buy = parseFloat(row.find('.buy-input').val()) || 0;
    let adjust = parseFloat(row.find('.adjust-input').val()) || 0;
    
    let total_cost = buy * qty;
    let loss = total_cost - adjust;
    row.find('.loss-input').val(loss.toFixed(2));
}

$(document).on('input', '.qty-input, .buy-input, .adjust-input', function(){
    let row = $(this).closest('tr');
    let qty = parseInt(row.find('.qty-input').val()) || 0;
    let currentQty = parseInt(row.find('.current_qty_text').text());

    if(qty > currentQty){
        swal('Wait!', 'Stock only has ' + currentQty + ' units available.', 'warning');
        row.find('.qty-input').val(currentQty);
    }
    calculateRow(row);
});

$(document).on('click', '.remove-row', function(){
    $(this).closest('tr').remove();
});
</script>

<?php
if(isset($_POST['submit'])){
    if(!isset($_POST['product_id']) || empty($_POST['product_id'])){
        echo "<script>swal('Error', 'Please add at least one product', 'error');</script>";
    } else {
        $stock_faulty_id = mysqli_real_escape_string($con, $_POST['stock_faulty_id']);
        $product_stock_ids = $_POST['product_stock_id'];
        $product_ids = $_POST['product_id'];
        $supplier_ids = $_POST['supplier_id'];
        $cat_ids = $_POST['cat_id'];
        $qtys = $_POST['qty'];
        $buy_prices = $_POST['buy_price'];
        $adjustable_amounts = $_POST['adjustable_amount'];
        $loss_amounts = $_POST['loss_amount'];
        $remarks_arr = $_POST['remarks'];

        mysqli_begin_transaction($con);
        try {
            for($i=0; $i < count($product_ids); $i++){
                $stock_id = mysqli_real_escape_string($con, $product_stock_ids[$i]);
                
                // Fetch stock data to verify
                $stock_q = mysqli_query($con, "SELECT qty, lot_no, branch_id FROM product_stock WHERE product_stock_id='$stock_id'");
                $stock_data = mysqli_fetch_assoc($stock_q);

                if($stock_data['branch_id'] != 4) throw new Exception('Access Denied: Product Lot not in Branch 4');

                $f_qty = (int)$qtys[$i];
                if($f_qty > $stock_data['qty']) throw new Exception('Stock error for Lot: ' . $stock_data['lot_no']);

                // Image Upload Handling
                $final_photo = "";
                if(!empty($_FILES['faulty_photo']['name'][$i])){
                    $tmp_name = $_FILES['faulty_photo']['tmp_name'][$i];
                    $ext = pathinfo($_FILES['faulty_photo']['name'][$i], PATHINFO_EXTENSION);
                    $final_photo = "FLT_" . time() . "_$i." . $ext;
                    move_uploaded_file($tmp_name, "img/faulty/" . $final_photo);
                }

                // Database Insert
                $sql = "INSERT INTO stock_faulty_items 
                        (stock_faulty_id, product_stock_id, product_id, supplier_id, cat_id, qty, lot_no, faulty_photo, buy_price, adjustable_amount, loss_amount, remarks, created_at, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
                
                $stmt = $con->prepare($sql);
                // "siiiisssddds" explained:
                // stock_faulty_id(s), product_stock_id(i), product_id(i), supplier_id(i), cat_id(i), qty(i), lot_no(s), faulty_photo(s), buy_price(d), adjustable_amount(d), loss_amount(d), remarks(s)
                $stmt->bind_param("siiiisssddds", 
                    $stock_faulty_id, 
                    $product_stock_ids[$i], 
                    $product_ids[$i], 
                    $supplier_ids[$i], 
                    $cat_ids[$i], 
                    $qtys[$i], 
                    $stock_data['lot_no'], 
                    $final_photo, 
                    $buy_prices[$i], 
                    $adjustable_amounts[$i], 
                    $loss_amounts[$i], 
                    $remarks_arr[$i]
                );

                if(!$stmt->execute()) {
                    throw new Exception("Insert Failed: " . $stmt->error);
                }

                // Update Main Stock
                $update_qty = $stock_data['qty'] - $f_qty;
                mysqli_query($con, "UPDATE product_stock SET qty='$update_qty' WHERE product_stock_id='$stock_id'");
            }

            mysqli_commit($con);
            echo "<script>swal('Success', 'Stock Faulty recorded successfully', 'success').then(()=>{ window.location='view_stock_faulty.php'; });</script>";

        } catch (Exception $e) {
            mysqli_rollback($con);
            echo "<script>swal('Database Error', '".$e->getMessage()."', 'error');</script>";
        }
    }
}
include('ini/footer.php');
?>