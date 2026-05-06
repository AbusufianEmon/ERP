<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['branch_id'])) {
    die("Branch not selected");
}

$branch_id = intval($_GET['branch_id']);
?>

<div class="container-fluid">
<div class="card shadow">
<div class="card-header bg-primary text-white">
    <h5>Direct Sales POS</h5>
</div>

<div class="card-body">

<label>Customer Search (Code / Name)</label>
<input type="text" id="customer_search" class="form-control mb-2">
<div id="customer_result" class="list-group mb-2"></div>
<div id="customer_info" class="text-success mb-2"></div>

<hr>

<label>Product Search (Code / Name)</label>
<input type="text" id="product_search" class="form-control mb-2">
<div id="product_result" class="list-group mb-2"></div>

<hr>

<table class="table table-bordered text-center">
<thead class="table-dark">
<tr>
    <th>Product</th>
    <th width="80">Qty</th>
    <th width="120">Sell Price</th>
    <th>Total</th>
    <th>Remove</th>
</tr>
</thead>
<tbody id="cart_body"></tbody>
</table>

<label>Remarks</label>
<textarea name="remarks" class="form-control" placeholder="Enter remarks"></textarea>

<div class="row mt-3">
    <div class="col-md-4">
        <label>Paid Amount</label>
        <input type="number" id="paid_amount" class="form-control" value="0">
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-4">
        <label>Discount (%)</label>
        <input type="number" id="discount_percent" class="form-control" value="0" min="0" max="100">
    </div>
</div>

<h4 class="mt-3">
    Grand Total: <span id="grand_total">0.00</span><br>
    Due Amount: <span id="due_amount">0.00</span>
</h4>

<button class="btn btn-success mt-3" id="confirm_sale">Confirm Sale</button>

</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let selectedCustomer = null;
let cart = [];

/* CUSTOMER SEARCH */
$('#customer_search').keyup(function () {
    let term = $(this).val();
    if (term.length < 1) return $('#customer_result').html('');
    $.get('ajax/search_customer.php', { term }, data => $('#customer_result').html(data));
});

$(document).on('click', '.customer-item', function () {
    selectedCustomer = {
        cus_id: $(this).data('id'),
        customer_code: $(this).data('code'),
        name: $(this).data('name')
    };
    $('#customer_info').html(`<b>${selectedCustomer.name}</b>`);
    $('#customer_result').html('');
    $('#customer_search').val('');
});

/* PRODUCT SEARCH */
$('#product_search').keyup(function () {
    let term = $(this).val();
    if (term.length < 1) return $('#product_result').html('');
    $.get('ajax/search_product.php', { term, branch_id: <?= $branch_id ?> }, data => {
        $('#product_result').html(data);
    });
});

$(document).on('click', '.product-item', function () {
    let stock_id = $(this).data('id');
    let name = $(this).data('name');
    let code = $(this).data('code');
    let price = parseFloat($(this).data('price'));

    let found = cart.find(p => p.stock_id == stock_id);
    if (found) {
        found.qty++;
    } else {
        cart.push({ stock_id, name, code, price, qty: 1, total: price });
    }
    renderCart();
    $('#product_result').html('');
    $('#product_search').val('');
});

/* CART */
function renderCart() {
    let html = '';
    let grand = 0;

    cart.forEach((p, i) => {
        p.total = p.qty * p.price;
        grand += p.total;

        html += `
        <tr>
            <td>${p.name}</td>
            <td>
                <input type="number" min="1" value="${p.qty}"
                       class="form-control form-control-sm"
                       onchange="updateQty(${i}, this.value)">
            </td>
            <td>
                <input type="number" step="0.01" value="${p.price}"
                       class="form-control form-control-sm"
                       onchange="updatePrice(${i}, this.value)">
            </td>
            <td>${p.total.toFixed(2)}</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="removeItem(${i})">X</button>
            </td>
        </tr>`;
    });

    let discount = parseFloat($('#discount_percent').val()) || 0;
    let discountAmount = (grand * discount) / 100;
    let finalTotal = grand - discountAmount;
    let paid = parseFloat($('#paid_amount').val()) || 0;

    $('#cart_body').html(html);
    $('#grand_total').text(finalTotal.toFixed(2));
    $('#due_amount').text((finalTotal - paid).toFixed(2));
}

function updateQty(i, qty) {
    cart[i].qty = parseInt(qty) || 1;
    renderCart();
}

function updatePrice(i, price) {
    cart[i].price = parseFloat(price) || 0;
    renderCart();
}

function removeItem(i) {
    cart.splice(i, 1);
    renderCart();
}

$('#paid_amount, #discount_percent').on('input', renderCart);

/* CONFIRM SALE */
$('#confirm_sale').click(function () {

    if (!selectedCustomer) {
        return Swal.fire('Error', 'Select customer', 'error');
    }
    if (cart.length === 0) {
        return Swal.fire('Error', 'Cart is empty', 'error');
    }

    let payload = {
        cus_id: selectedCustomer.cus_id,
        customer_code: selectedCustomer.customer_code,
        cart: cart,
        paid_amount: parseFloat($('#paid_amount').val()),
        discount_percent: parseFloat($('#discount_percent').val()) || 0,
        remarks: $('textarea[name="remarks"]').val(),
        branch_id: <?= $branch_id ?>
    };

    $.ajax({
        url: 'ajax/confirm_sale.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: res => {
            Swal.fire('Success', res, 'success').then(() => location.reload());
        },
        error: xhr => {
            Swal.fire('Error', xhr.responseText, 'error');
        }
    });
});
</script>

<?php include('ini/footer.php'); ?>
