<?php
include('ini/header.php');
include('dbcon.php');

$idd = $_GET['id'];

// Fetch product with category
$sql = "SELECT p.id, p.name, p.code, p.photo, p.cat_id, c.cat_name 
        FROM product p
        LEFT JOIN category c ON p.cat_id = c.cat_id 
        WHERE p.id = '$idd'";
$run = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($run);

// Fetch categories for dropdown
$d = "SELECT * FROM category";
$r = mysqli_query($con, $d);
?>

<h1 class="text-center">Edit Product :</h1>
<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="container">
                <label class="form-group">Product Name :</label>
                <input type="text" name="name" required value="<?php echo $data['name'] ?>" class="form-control"><br>

                <label class="form-group">Product Code :</label>
                <input type="text" name="code" required value="<?php echo $data['code'] ?>" class="form-control"><br>

                <label class="form-group">Product Photo :</label><br>
                <?php if (!empty($data['photo'])) { ?>
                    <img src="img/products/<?php echo $data['photo']; ?>" width="100" height="100" style="border-radius: 10px; margin-bottom:10px;">
                <?php } ?>
                <input type="file" name="photo" class="form-control"><br>

                <label class="form-group">Select Category :</label>
                <select name="cat_id" class="form-control" required>
                    <option value="<?php echo $data['cat_id'] ?>"><?php echo $data['cat_name']; ?></option>
                    <?php while ($ta = mysqli_fetch_assoc($r)) { ?>
                        <option value="<?php echo $ta['cat_id'] ?>"><?php echo $ta['cat_name'] ?></option>
                    <?php } ?>
                </select>
                <br><br>
                <input type="submit" name="submit" class="btn btn-success form-control" value="Update Product">
            </form>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>

<!-- SweetAlert CDN -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $code = mysqli_real_escape_string($con, $_POST['code']);
    $cat_id = mysqli_real_escape_string($con, $_POST['cat_id']);

    $photo = $_FILES['photo']['name'];
    $tmp_name = $_FILES['photo']['tmp_name'];
    $upload_path = 'img/products/';

    // if a new photo is uploaded
    if (!empty($photo)) {
        $photo_new = time() . "_" . basename($photo);
        $upload_check = move_uploaded_file($tmp_name, $upload_path . $photo_new);

        if (!$upload_check) {
            echo "<script>swal('Failed!', 'Failed to upload the photo!', 'error');</script>";
            exit();
        }
    } else {
        // keep old photo
        $photo_new = $data['photo'];
    }

    // If product code changed → check uniqueness
    if ($code != $data['code']) {
        $check_code_sql = "SELECT * FROM product WHERE code = '$code'";
        $check_code_result = mysqli_query($con, $check_code_sql);

        if (mysqli_num_rows($check_code_result) > 0) {
            echo "<script>swal('Error!', 'Product Code already exists!', 'error');</script>";
            exit();
        }
    }

    // Update query
    $sql_update = "UPDATE product 
                   SET name = '$name', code = '$code', cat_id = '$cat_id', photo = '$photo_new' 
                   WHERE id = '$idd'";
    $run_update = mysqli_query($con, $sql_update);

    if ($run_update) {
        echo '<script>
            swal({
                title: "Success!",
                text: "Product updated successfully",
                icon: "success"
            }).then(function() {
                window.location = "view_product.php?id=' . $idd . '";
            });
        </script>';
    } else {
        echo "<script>swal('Oops!', 'Failed to update product!', 'error');</script>";
    }
}
?>
