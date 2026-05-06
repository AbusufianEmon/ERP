<?php
include('ini/header.php');
include('dbcon.php');
?>

<h1 class="text-center text-primary mb-4">Add Product :</h1>

<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="container">

                <label class="form-group">Product Name :</label>
                <input type="text" name="name" required placeholder="Product Name (Letters only)" 
                       pattern="^[a-zA-Z\s\-]+$" title="Numbers are not allowed in the product name" class="form-control"><br>

                <label class="form-group">Product Code :</label>
                <input type="text" name="code" required placeholder="Product Code" class="form-control"><br>

                <label class="form-group">Select Category :</label>
                <select name="cat_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    $cat_sql = "SELECT * FROM category";
                    $cat_run = mysqli_query($con, $cat_sql);
                    while ($cat = mysqli_fetch_assoc($cat_run)) {
                        echo '<option value="'.$cat['cat_id'].'">'.$cat['cat_name'].'</option>';
                    }
                    ?>
                </select><br>

                <label class="form-group">Product Photo :</label>
                <input type="file" name="photo" class="form-control"><br>

                <input type="submit" name="submit" value="Add Product" class="btn btn-success form-control">
            </form>
        </div>
    </div>
</div>

<?php
if (isset($_POST['submit'])) {
    // Sanitize inputs
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $code = mysqli_real_escape_string($con, $_POST['code']);
    $cat_id = mysqli_real_escape_string($con, $_POST['cat_id']);

    // --- 1. PRODUCT NAME VALIDATION (NO NUMBERS) ---
    // preg_match checks if the string contains any digits (0-9)
    if (preg_match('/[0-9]/', $name)) {
        echo "<script>
            swal('Invalid Name!', 'Product name cannot contain numbers.', 'error');
        </script>";
    } 
    else {
        // --- 2. DUPLICATE CHECK LOGIC ---
        $check_code = mysqli_query($con, "SELECT code FROM product WHERE code = '$code'");
        
        if (mysqli_num_rows($check_code) > 0) {
            echo "<script>
                swal('Duplicate Code!', 'Product code $code already exists. Please use a unique code.', 'warning');
            </script>";
        } else {
            // --- 3. FILE UPLOAD LOGIC ---
            $photo = $_FILES['photo']['name'];
            $tmp_name = $_FILES['photo']['tmp_name'];
            $upload_path = 'img/products/';
            $final_photo = "";

            if (!empty($photo)) {
                $final_photo = time() . "_" . $photo;
                $upload_check = move_uploaded_file($tmp_name, $upload_path . $final_photo);
                
                if (!$upload_check) {
                    echo "<script>swal('Error!', 'Failed to upload photo.', 'error');</script>";
                    $final_photo = '';
                }
            }

            // --- 4. INSERT LOGIC ---
            $sql = "INSERT INTO product (name, code, cat_id, photo) 
                    VALUES ('$name', '$code', '$cat_id', '$final_photo')";
            $run = mysqli_query($con, $sql);

            if ($run) {
                echo "<script>
                    swal('Success!', 'Product added successfully!', 'success')
                    .then(() => { window.location='all_product.php'; });
                </script>";
            } else {
                echo "<script>swal('Error!', 'Database Error: " . mysqli_error($con) . "', 'error');</script>";
            }
        }
    }
}
?>

<?php include('ini/footer.php'); ?>