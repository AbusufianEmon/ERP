<?php
include('ini/header.php');
include('dbcon.php');

if (!isset($_GET['product_id'])) {
    echo "<h3 class='text-danger text-center'>Invalid Request</h3>";
    include('ini/footer.php');
    exit();
}

$pid = intval($_GET['product_id']);

// Fetch product with supplier + branch stock
$d = "SELECT p.*, s.sup_name, ps.qty, ps.buy_price, ps.sell_price, ps.branch_id, ps.stock_id, ps.supplier_id
      FROM product p
      JOIN product_stock ps ON ps.product_id = p.id
      LEFT JOIN supplier s ON ps.supplier_id = s.id
      WHERE p.id='$pid' LIMIT 1";

$r = mysqli_query($con, $d);
$data = mysqli_fetch_assoc($r);

if (!$data) {
    echo "<h3 class='text-danger text-center'>Product not found</h3>";
    include('ini/footer.php');
    exit();
}

// ✅ Fetch overall total quantity across all branches
$total_qty_sql = "SELECT SUM(qty) as total_qty FROM product_stock WHERE product_id='$pid'";
$total_qty_res = mysqli_query($con, $total_qty_sql);
$total_qty_data = mysqli_fetch_assoc($total_qty_res);
$total_qty = $total_qty_data['total_qty'] ?? 0;

// Fetch all suppliers
$s = "SELECT * FROM supplier";
$sr = mysqli_query($con, $s);
?>

<h1 class="text-center">Update Product Stock</h1>
<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="container" onsubmit="return validateForm()">
                <label class="form-group">Product Name :</label>
                <input type="text" disabled value="<?php echo $data['name'] ?>" class="form-control"><br>

                <label class="form-group">Product Code :</label>
                <input type="text" disabled value="<?php echo $data['code'] ?>" class="form-control"><br>

                <label class="form-group">Current Quantity (Overall) :</label>
                <input type="number" disabled value="<?php echo $total_qty ?>" class="form-control"><br>

                <label class="form-group">New Quantity (Must be > <?php echo $total_qty ?>):</label>
                <input type="number" name="qty" id="qty" min="<?php echo $total_qty + 1 ?>" value="<?php echo $total_qty ?>" class="form-control" required><br>

                <label class="form-group">Buying Price :</label>
                <input type="number" name="buy_price" required value="<?php echo $data['buy_price'] ?>" class="form-control"><br>

                <label class="form-group">Selling Price :</label>
                <input type="number" name="sell_price" step="0.01" class="form-control" value="<?php echo $data['sell_price'] ?>"><br>

                <label class="form-group">Paid Amount :</label>
                <input type="number" name="paid_amount" class="form-control" placeholder="Paid Amount"><br>

                <label class="form-group">Supplier :</label>
                <select name="sup_id" class="form-control" required>
                    <option value="<?php echo $data['supplier_id'] ?>"><?php echo $data['sup_name'] ?></option>
                    <?php while ($t = mysqli_fetch_assoc($sr)) { ?>
                        <option value="<?php echo $t['id'] ?>"><?php echo $t['sup_name'] ?></option>
                    <?php } ?>
                </select>
                <br><br>
                <input type="submit" name="submit" class="btn btn-success form-control">
            </form>
        </div>
    </div>
</div>

<script>
function validateForm() {
    var currentQty = <?php echo $total_qty; ?>;
    var newQty = parseInt(document.getElementById('qty').value);
    if (newQty <= currentQty) {
        alert("New quantity must be greater than the current total quantity (" + currentQty + ").");
        return false;
    }
    return true;
}
</script>

<?php
include('ini/footer.php');

if (isset($_POST['submit'])) {
    $new_qty = intval($_POST['qty']);
    $buy_price = $_POST['buy_price'];
    $sell_price = $_POST['sell_price'];
    $paid_amount = $_POST['paid_amount'];
    $sup_id = $_POST['sup_id'];

    $existing_qty = $total_qty; // overall qty
    $difference_qty = $new_qty - $existing_qty;
    $datee = date("Y-m-d");

    if ($difference_qty > 0) {
        // ✅ Update stock table for this branch
        $update_stock_sql = "UPDATE product_stock 
                             SET qty = qty + $difference_qty, buy_price='$buy_price', sell_price='$sell_price', supplier_id='$sup_id' 
                             WHERE stock_id='{$data['stock_id']}'";
        $update_run = mysqli_query($con, $update_stock_sql);

        // ✅ Update product table (base info only)
        $update_product_sql = "UPDATE product 
                               SET buy_price='$buy_price', sell_price='$sell_price', sup_id='$sup_id' 
                               WHERE id='$pid'";
        mysqli_query($con, $update_product_sql);

        if ($update_run) {
            // ✅ Insert supplier invoice for new stock only
            $due_amount = ($buy_price * $difference_qty) - $paid_amount;
            $sql_invoice = "INSERT INTO sup_invoice (product_id, product_name, supp_id, paid_amount, due_amount, qty, code, datee) 
                            VALUES ('$pid', '{$data['name']}', '$sup_id', '$paid_amount', '$due_amount', '$difference_qty', '{$data['code']}', '$datee')";
            $run_invoice = mysqli_query($con, $sql_invoice);

            if ($run_invoice) {
                $last_invoice_id = mysqli_insert_id($con);
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire("Success!", "Stock updated successfully!", "success").then(function() {
                        window.location.href = 'supplier_invoice.php?invoice_id=<?php echo $last_invoice_id; ?>';
                    });
                </script>
                <?php
            } else {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>Swal.fire("Error!", "Failed to insert supplier invoice!", "error");</script>
                <?php
            }
        } else {
            ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>Swal.fire("Error!", "Failed to update stock!", "error");</script>
            <?php
        }
    } else {
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>Swal.fire("Error!", "New quantity must be greater than the overall quantity.", "error");</script>
        <?php
    }
}
?>
