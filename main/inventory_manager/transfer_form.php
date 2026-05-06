<?php
include('ini/header.php');
include('dbcon.php');

$branch_res = mysqli_query($con, "SELECT * FROM branches");
$category_res = mysqli_query($con, "SELECT * FROM category");

function generateTransferID() {
    return "TRF_" . date("Ymd_His") . "_" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 4);
}
$transfer_id = generateTransferID();
?>
<div class="text-left">
    <a href="view_stock.php" class="btn btn-warning btn-sm font-weight-bold text-primary">View Stock</a>
</div>
<h1 class="text-center">New Product Transfer</h1>

<div class="row">
    <div class="col-md-10 offset-1 card text-success" style="background:white; font-weight:bold;">
        <div class="card-body">
            <form method="post" action="transfer_submit.php" id="transfer_form">
                <label>From Branch :</label>
                <select name="from_branch" id="from_branch" class="form-control" required>
                    <option value="">Select Branch</option>
                    <?php while($b = mysqli_fetch_assoc($branch_res)){ ?>
                        <option value="<?php echo $b['branch_id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                    <?php } ?>
                </select><br>

                <label>To Branch :</label>
                <select name="to_branch" id="to_branch" class="form-control" required>
                    <option value="">Select Branch</option>
                    <?php
                    mysqli_data_seek($branch_res, 0);
                    while($b = mysqli_fetch_assoc($branch_res)){
                        echo "<option value='{$b['branch_id']}'>{$b['branch_name']}</option>";
                    }
                    ?>
                </select><br>

                <label>Select Category :</label>
                <select id="category_select" class="form-control">
                    <option value="">All Categories</option>
                    <?php while($c = mysqli_fetch_assoc($category_res)){ ?>
                        <option value="<?php echo $c['cat_id']; ?>"><?php echo htmlspecialchars($c['cat_name']); ?></option>
                    <?php } ?>
                </select><br>

                <input type="hidden" name="transfer_id" value="<?php echo $transfer_id; ?>">

                <label>Search Product in From Branch:</label>
                <input type="text" id="product_input" class="form-control" placeholder="Type product name or code">
                <div id="product_list" class="list-group position-absolute" style="z-index:999; max-height:300px; overflow:auto;"></div><br>

                <div id="category_products" style="display:none;">
                    <h5>Products in Selected Category:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="category_product_table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product Name</th><th>Code</th><th>Lot No</th><th>Buy Price</th><th>Qty</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div><br>

                <h5>Products to Transfer:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="product_table">
                        <thead class="table-dark">
                            <tr>
                                <th>Product Name</th><th>Lot No</th><th>Qty Available</th><th>Transfer Qty</th><th>Buy Price</th><th>Sell Price</th><th>Remove</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success form-control mt-2">Submit Transfer</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
$('#product_input').keyup(function(){
    let query = $(this).val();
    let fromBranch = $('#from_branch').val();
    if(query.length < 1 || !fromBranch){ $('#product_list').hide(); return; }
    $.post('fetch_product_stock.php', {query: query, branch_id: fromBranch}, function(data){
        $('#product_list').html(data).show();
    });
});

$(document).on('click', '.product-item', function(e){
    e.preventDefault();
    addProductToTable($(this));
});

$('#category_select').change(function(){
    let cat_id = $(this).val();
    let branch_id = $('#from_branch').val();
    if(!branch_id) return alert('Select a branch first!');
    $.post('fetch_category_products_stock.php', {branch_id: branch_id, cat_id: cat_id}, function(data){
        $('#category_product_table tbody').html(data);
        $('#category_products').show();
    });
});

$(document).on('click', '.add-product', function(e){
    e.preventDefault();
    addProductToTable($(this));
});

function addProductToTable(el){
    let stockid = el.data('stockid'); // Captured here
    let pid = el.data('id');
    let pname = el.data('name');
    let lot_no = el.data('lot');
    let qty_avail = el.data('qty');
    let buy_price = el.data('buy');
    let sell_price = el.data('sell');

    if(qty_avail <= 0){
        swal("Error", `Product "${pname}" has 0 quantity!`, "error");
        return;
    }

    if($('#row_'+stockid).length) return;

    let row = `
        <tr id="row_${stockid}">
            <td>${pname}<input type="hidden" name="product_stock_id[]" value="${stockid}"><input type="hidden" name="product_id[]" value="${pid}"></td>
            <td>${lot_no}<input type="hidden" name="lot_no[]" value="${lot_no}"></td>
            <td>${qty_avail}</td>
            <td><input type="number" name="qty[]" class="form-control" min="1" max="${qty_avail}" value="1" required></td>
            <td><input type="number" class="form-control" value="${buy_price}" step="0.01" disabled></td>
            <td><input type="number" class="form-control" value="${sell_price}" step="0.01" disabled></td>
            <td><button type="button" class="btn btn-danger remove-row">X</button></td>
        </tr>
    `;
    $('#product_table tbody').append(row);
}

$(document).on('click', '.remove-row', function(){ $(this).closest('tr').remove(); });

$('#transfer_form').submit(function(e){
    if($('#from_branch').val() === $('#to_branch').val()){
        swal("Error", "Branches cannot be the same!", "error");
        return false;
    }
    if($('#product_table tbody tr').length === 0){
        swal("Error", "Please add at least one product!", "error");
        return false;
    }
    return true;
});
</script>