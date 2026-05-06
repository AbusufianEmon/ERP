<?php
include('ini/header.php');
$d = "SELECT * FROM category";
include('dbcon.php');
$r = mysqli_query($con, $d);
$s = "SELECT * FROM supplier";
$sr = mysqli_query($con, $s);
?>

<h1 class="text-center">Add Products :</h1>
<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="container">
                <label class="form-group">Product Name :</label>
                <input type="text" name="name" required placeholder="Product Name" class="form-control"><br>

                <label class="form-group">Product Code :</label>
                <input type="text" name="code" required placeholder="Product Code" class="form-control"><br>

                <label class="form-group">Product Qty :</label>
                <input type="number" name="qty" required placeholder="Product Qty" class="form-control"><br>

                <label class="form-group">Buying Price :</label>
                <input type="number" name="buy_price" required placeholder="Buying Price" step="0.01" class="form-control"><br>

                <label class="form-group">Selling Price :</label>
                <input type="number" name="sell_price" step="0.01" class="form-control" placeholder="Selling Price"><br>

                <label class="form-group">Paid Amount :</label>
                <input type="number" name="paid_ammount" class="form-control" placeholder="Paid Amount"><br>

                <label class="form-group">Product Photo :</label>
                <input type="file" name="photo" class="form-control" placeholder="Photo"><br>

                <label class="form-group">Supplier :</label>
                <select name="sup_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    <?php while ($t = mysqli_fetch_assoc($sr)) { ?>
                        <option value="<?php echo $t['id'] ?>"><?php echo $t['sup_name'] ?></option>
                    <?php } ?>
                </select>

                <label class="form-group">Select Category :</label>
                <select name="cat_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php while ($ta = mysqli_fetch_assoc($r)) { ?>
                        <option value="<?php echo $ta['cat_id'] ?>"><?php echo $ta['cat_name'] ?></option>
                    <?php } ?>
                </select>
                <br><br>
                <input type="submit" name="submit" class="btn btn-success form-control">
            </form>
        </div>
    </div>
</div>

<?php
include('ini/footer.php');

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $qty = $_POST['qty'];
    $buy_price = $_POST['buy_price'];
    $sell_price = $_POST['sell_price'];
    $paid_ammount = $_POST['paid_ammount'];
    $sup_id = $_POST['sup_id'];
    $cat_id = $_POST['cat_id'];
    $datee = date("Y-m-d");

    $photo = $_FILES['photo']['name'];
    $tmp_name = $_FILES['photo']['tmp_name'];
    $upload_path = 'img/products/';
    $upload_check = move_uploaded_file($tmp_name, $upload_path.$photo);

    if ($upload_check) {
        // Check if the code already exists
        $check_code_sql = "SELECT * FROM product WHERE code = '$code'";
        $check_code_result = mysqli_query($con, $check_code_sql);

        if (mysqli_num_rows($check_code_result) > 0) {
            ?>
            <script>
                swal("Error!", "Product Code already exists!", "error");
            </script>
            <?php
        } else {
            // Insert into product table
            $sql = "INSERT INTO product (name, code, qty, buy_price, sell_price, paid_ammount, sup_id, cat_id, photo) 
                    VALUES ('$name', '$code', '$qty', '$buy_price', '$sell_price', '$paid_ammount', '$sup_id', '$cat_id', '$photo')";
            $run = mysqli_query($con, $sql);

            if ($run) {
                $last_product_id = mysqli_insert_id($con);

                // ✅ Auto insert into product_stock for branch_id = 4
                $branch_id = 4;
                $sql_stock = "INSERT INTO product_stock (product_id, branch_id, qty) 
                              VALUES ('$last_product_id', '$branch_id', '$qty')";
                $run_stock = mysqli_query($con, $sql_stock);

                if (!$run_stock) {
                    ?>
                    <script>
                        swal("Warning!", "Product added but failed to insert stock record!", "warning");
                    </script>
                    <?php
                }

                // Calculate due amount
                $due_amount = ($buy_price * $qty) - $paid_ammount;

                // Insert into supplier invoice
                $sql_invoice = "INSERT INTO sup_invoice (product_id, product_name, supp_id, paid_amount, due_amount, qty, code, datee) 
                                VALUES ('$last_product_id', '$name', '$sup_id', '$paid_ammount', '$due_amount', '$qty', '$code', '$datee')";
                $run_invoice = mysqli_query($con, $sql_invoice);

                if ($run_invoice) {
                    $last_invoice_id = mysqli_insert_id($con);
                    ?>
                    <script>
                        swal("Success!", "Product, Product Stock and Invoice added successfully!", "success").then(function() {
                            window.location.href = 'supplier_invoice.php?invoice_id=' + <?php echo $last_invoice_id; ?>;
                        });
                    </script>
                    <?php
                } else {
                    ?>
                    <script>
                        swal("Oops!", "Failed to insert into sup_invoice table!", "error");
                    </script>
                    <?php
                }
            } else {
                ?>
                <script>
                    swal("Oops!", "Failed to insert into product table!", "error");
                </script>
                <?php
            }
        }
    } else {
        ?>
        <script>
            swal("Failed!", "Failed to upload the photo!", "error");
        </script>
        <?php
    }
}
?>
