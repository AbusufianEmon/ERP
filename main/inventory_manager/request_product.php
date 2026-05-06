<?php
include('ini/header.php');
include('dbcon.php');

if(!isset($_GET['branch_id'])){
    die("From Branch not specified!");
}
$from_branch = intval($_GET['branch_id']);

// Fetch branch name for From Branch
$from_branch_res = mysqli_query($con, "SELECT branch_name FROM branches WHERE branch_id = $from_branch LIMIT 1");
$from_branch_row = mysqli_fetch_assoc($from_branch_res);
$from_branch_name = $from_branch_row['branch_name'];

// Fetch branches for "To Branch"
$branch_res = mysqli_query($con, "SELECT * FROM branches WHERE branch_id != $from_branch");

// Fetch categories
$category_res = mysqli_query($con, "SELECT * FROM category");

// Generate Request ID
function generateRequestID(){
    return "REQ_" . date("Ymd_His") . "_" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"),0,4);
}
$request_id = generateRequestID();
?>

<h1 class="text-center">Request Products to Other Branches</h1>

<div class="row">
    <div class="col-md-10 offset-1 card text-success" style="background:white; font-weight:bold;">
        <div class="card-body">
            <form method="post" action="request_submit.php" id="request_form">

                <!-- From Branch (fixed) -->
                <label>From Branch :</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($from_branch_name); ?>" readonly>
                <input type="hidden" name="from_branch" value="<?= $from_branch; ?>"><br>

                <!-- To Branch -->
                <label>To Branch :</label>
                <select name="to_branch" id="to_branch" class="form-control" required>
                    <option value="">Select Branch</option>
                    <?php while($b = mysqli_fetch_assoc($branch_res)){ ?>
                        <option value="<?= $b['branch_id']; ?>"><?= htmlspecialchars($b['branch_name']); ?></option>
                    <?php } ?>
                </select><br>

                <!-- Category -->
                <label>Select Category :</label>
                <select id="category_select" class="form-control">
                    <option value="">All Categories</option>
                    <?php while($c = mysqli_fetch_assoc($category_res)){ ?>
                        <option value="<?= $c['cat_id']; ?>"><?= htmlspecialchars($c['cat_name']); ?></option>
                    <?php } ?>
                </select><br>

                <input type="hidden" name="request_id" value="<?= $request_id; ?>">

                <!-- Product Search -->
                <label>Search Product in To Branch:</label>
                <input type="text" id="product_input" class="form-control" placeholder="Type product name or code">
                <div id="product_list" class="list-group position-absolute" style="z-index:999; max-height:300px; overflow:auto;"></div><br>

                <!-- Category Products Table -->
                <div id="category_products" style="display:none;">
                    <h5>Products in Selected Category:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="category_product_table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Lot No</th>
                                    <th>Buy Price</th>
                                    <th>Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div><br>

                <!-- Selected Products -->
                <h5>Products to Request:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="product_table">
                        <thead class="table-dark">
                            <tr>
                                <th>Product Name</th>
                                <th>Lot No</th>
                                <th>Qty Available</th>
                                <th>Request Qty</th>
                                <th>Buy Price</th>
                                <th>Sell Price</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success form-control mt-2">Submit Request</button>
            </form>
        </div>
    </div>
</div>

<!-- JQuery + SweetAlert -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
// Product search in To Branch
$('#product_input').keyup(function(){
    let query = $(this).val();
    let toBranch = $('#to_branch').val();
    if(query.length < 1 || !toBranch){ 
        $('#product_list').hide(); 
        return; 
    }
    $.post('fetch_to_branch_products.php', {query: query, branch_id: toBranch}, function(data){
        $('#product_list').html(data).show();
    });
});

// Add product from search
$(document).on('click', '.product-item', function(e){
    e.preventDefault();
    addProductToTable($(this));
});

// Category selection
$('#category_select').change(function(){
    let cat_id = $(this).val();
    let branch_id = $('#to_branch').val();
    if(!branch_id) return alert('Select a To Branch first!');
    $('#category_product_table tbody').empty();
    $('#category_products').hide();
    $.post('fetch_category_to_branch.php', {branch_id: branch_id, cat_id: cat_id}, function(data){
        $('#category_product_table tbody').html(data);
        $('#category_products').show();
    });
});

// Add product from category table
$(document).on('click', '.add-product', function(e){
    e.preventDefault();
    addProductToTable($(this));
});

function addProductToTable(el){
    let pid = el.data('id');
    let pname = el.data('name');
    let lot_no = el.data('lot');
    let qty_avail = el.data('qty');
    let buy_price = el.data('buy');
    let sell_price = el.data('sell');

    if($('#row_'+pid).length) return;

    let row = `
        <tr id="row_${pid}">
            <td>${pname}<input type="hidden" name="product_id[]" value="${pid}"></td>
            <td>${lot_no}<input type="hidden" name="lot_no[]" value="${lot_no}"></td>
            <td>${qty_avail}</td>
            <td><input type="number" name="qty[]" class="form-control" min="1" max="${qty_avail}" value="1" required></td>
            <td><input type="number" name="buy_price[]" class="form-control" value="${buy_price}" step="0.01" disabled></td>
            <td><input type="number" name="sell_price[]" class="form-control" value="${sell_price}" step="0.01" disabled></td>
            <td><button type="button" class="btn btn-danger remove-row">X</button></td>
        </tr>
    `;
    $('#product_table tbody').append(row);
}

// Remove row
$(document).on('click', '.remove-row', function(){
    $(this).closest('tr').remove();
});

// Form submission validation
$('#request_form').submit(function(e){
    let toBranch = $('#to_branch').val();
    if(!toBranch){
        swal("Error", "Please select a To Branch!", "error");
        e.preventDefault();
        return false;
    }
    return true;
});
</script>
<?php include('ini/footer.php'); ?>
