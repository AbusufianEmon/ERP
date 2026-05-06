<?php
include('ini/header.php');
include('dbcon.php');

/* Fetch branches */
$branch_res = mysqli_query($con, "SELECT * FROM branches");

/* Fetch categories */
$category_res = mysqli_query($con, "SELECT * FROM category");

/* Generate Transfer ID */
function generateTransferID() {
    return "TRF_" . date("Ymd_His") . "_" . rand(1000,9999);
}
$transfer_id = generateTransferID();
?>

<h2 class="text-center">New Product Transfer</h2>

<form method="post" action="transfer_submit.php" id="transfer_form">

<label>From Branch</label>
<select name="from_branch" id="from_branch" class="form-control" required>
    <option value="">Select</option>
    <?php while($b=mysqli_fetch_assoc($branch_res)){ ?>
        <option value="<?= $b['branch_id'] ?>"><?= $b['branch_name'] ?></option>
    <?php } ?>
</select><br>

<label>To Branch</label>
<select name="to_branch" id="to_branch" class="form-control" required>
    <option value="">Select</option>
    <?php
    mysqli_data_seek($branch_res,0);
    while($b=mysqli_fetch_assoc($branch_res)){
        echo "<option value='{$b['branch_id']}'>{$b['branch_name']}</option>";
    }
    ?>
</select><br>

<label>Select Category</label>
<select id="category_select" class="form-control">
    <option value="">All</option>
    <?php while($c=mysqli_fetch_assoc($category_res)){ ?>
        <option value="<?= $c['cat_id'] ?>"><?= $c['cat_name'] ?></option>
    <?php } ?>
</select><br>

<input type="hidden" name="transfer_id" value="<?= $transfer_id ?>">

<label>Search Product</label>
<input type="text" id="product_input" class="form-control">
<div id="product_list" class="list-group"></div><br>

<table class="table table-bordered" id="product_table">
<thead class="table-dark">
<tr>
    <th>Name</th>
    <th>Lot</th>
    <th>Available</th>
    <th>Transfer Qty</th>
    <th>Buy</th>
    <th>Sell</th>
    <th>X</th>
</tr>
</thead>
<tbody></tbody>
</table>

<button class="btn btn-success w-100">Submit Transfer</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
$('#product_input').keyup(function(){
    let q = $(this).val();
    let branch = $('#from_branch').val();
    if(!q || !branch) return;
    $.post('fetch_product_stock.php',{query:q,branch_id:branch},function(d){
        $('#product_list').html(d);
    });
});

$(document).on('click','.product-item',function(e){
    e.preventDefault();

    let id=$(this).data('id');
    if($('#row_'+id).length) return;

    let qty=$(this).data('qty');
    if(qty<=0){
        swal("Error","No stock available","error");
        return;
    }
$('#product_table tbody').append(`
<tr id="row_${id}">
<td>${$(this).data('name')}
<input type="hidden" name="product_id[]" value="${id}">
</td>

<td>${$(this).data('lot')}
<input type="hidden" name="lot_no[]" value="${$(this).data('lot')}">
</td>

<td>${qty}</td>

<td>
<input type="number" name="qty[]" min="1" max="${qty}" value="1" class="form-control">
</td>

<td>${$(this).data('buy')}
<input type="hidden" name="buy_price[]" value="${$(this).data('buy')}">
</td>

<td>${$(this).data('sell')}
<input type="hidden" name="sell_price[]" value="${$(this).data('sell')}">
</td>

<input type="hidden" name="supplier_id[]" value="${$(this).data('supplier')}">
<input type="hidden" name="cat_id[]" value="${$(this).data('cat')}">

<td><button type="button" class="btn btn-danger remove">X</button></td>
</tr>
`);

</script>
