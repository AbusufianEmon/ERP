<?php 
include('ini/header.php');
$idd = $_GET['id'];

// Fetch User Data
$get_user = "SELECT * FROM user WHERE id= $idd";
$run = mysqli_query($con, $get_user);
$data = mysqli_fetch_assoc($run);

// Fetch branches for dropdown
$branch_sql = "SELECT * FROM branches";
$branch_run = mysqli_query($con, $branch_sql);
?>

<h6 class="text-center">Edit <span class="text-danger"><?php echo htmlspecialchars($data['s_name']) ?>'s</span> Information</h6>
<div class="main-content container bg-success mb-5">
    <div class="row">
        <div class="col-md-6">
            <p style="color:white;font-size:24px; font-weight: bolder; width: 300px;" class="text-center offset-10">Personal Information : </p>
        </div>
    </div>
    <form class="pb-3" action="" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">User Name:</i></label>
                <input type="text" required="" name="s_name" value="<?php echo htmlspecialchars($data['s_name']) ?>" class="form-control">
            </div>
            <div class="col-md-4 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">Father's Name:</i></label>
                <input type="text" required="" name="f_name" value="<?php echo htmlspecialchars($data['f_name']) ?>" class="form-control">
            </div>
            <div class="col-md-4 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">Mother's Name:</i></label>
                <input type="text" name="m_name" value="<?php echo htmlspecialchars($data['m_name']) ?>" class="form-control">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">Date Of Birth</i></label>
                <input type="date" name="dod" value="<?php echo htmlspecialchars($data['dod']) ?>" required="" class="form-control">
            </div>
            <div class="col-md-4 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">Blood Group</i></label>
                <select class="form-control" name="blood" required="">
                    <option value="<?php echo htmlspecialchars($data['blood']) ?>"><?php echo htmlspecialchars($data['blood']) ?></option>
                    <option value="A+">A+</option>
                    <option value="AB+">AB+</option>
                    <option value="B+">B+</option>
                    <option value="A-">A-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">Gender</i></label>
                <select class="form-control" name="gender" required="">
                    <option value="<?php echo htmlspecialchars($data['gender']) ?>"><?php echo htmlspecialchars($data['gender']) ?></option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="text-light"><i style="font-weight: bolder; font-size: 18px;">Contact Phone</i></label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($data['phone']) ?>" required="" class="form-control">
            </div>
        </div>

        <p style="color:white;font-size:28px; font-weight: bolder;" class="ml-2 mt-5">Address:</p>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><h4>Permanent Address</h4></div>
                    <div class="card-body">
                        <label>Care of</label>
                        <input type="text" name="per_careof" required="" value="<?php echo htmlspecialchars($data['per_careof']) ?>" class="form-control mb-2">
                        <label>Village /Town /Road:</label>
                        <input type="text" name="per_village" required="" value="<?php echo htmlspecialchars($data['per_village']) ?>" class="form-control mb-2">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Division</label>
                                <input type="text" name="pdivi" value="<?php echo htmlspecialchars($data['pdivi']) ?>" required="" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>District</label>
                                <input type="text" name="pdist" value="<?php echo htmlspecialchars($data['pdist']) ?>" required="" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>PS/Upzila</label>
                                <input type="text" name="p_posto" required="" value="<?php echo htmlspecialchars($data['p_posto']) ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 form-group">
                <label class="text-light">User Type</label>
                <select name="user_type" class="form-control" required>
                    <option value="<?php echo $data['user_type']; ?>">
                        <?php 
                        $roles = [1=>"CEO", 2=>"Inventory Manager", 3=>"HR", 4=>"Branch Manager", 5=>"Employee"];
                        echo $roles[$data['user_type']] ?? "Select User Type";
                        ?>
                    </option>
                    <option value="1">CEO</option>
                    <option value="2">Inventory Manager</option>
                    <option value="3">HR</option>
                    <option value="4">Branch Manager</option>
                    <option value="5">Employee</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label class="text-light">Assign Branch</label>
                <select name="branch_id" class="form-control" required>
                    <?php 
                    mysqli_data_seek($branch_run, 0);
                    while($branch = mysqli_fetch_assoc($branch_run)) {
                        $selected = ($branch['branch_id'] == $data['branch_id']) ? "selected" : "";
                        echo "<option value='{$branch['branch_id']}' $selected>{$branch['branch_name']}</option>";
                    } 
                    ?>
                </select>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <label class="text-light">User Photo</label>
                <input type="file" class="form-control" name="image">
                <small class="text-light">Current: <?php echo $data['image']; ?></small>
            </div>
            <div class="col-md-6">
                <label class="text-light">NID Photo</label>
                <input type="file" class="form-control" name="nid">
                <small class="text-light">Current: <?php echo $data['nid']; ?></small>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <label class="text-light">Password</label>
                <input type="text" value="<?php echo htmlspecialchars($data['password']) ?>" name="password" class="form-control" required>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary btn-block" name="submit">Update Information</button>
            </div>
            <div class="col-md-6">
                <a href="view_user.php" class="btn btn-danger btn-block">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php 
if(isset($_POST['submit'])){
    $s_name = mysqli_real_escape_string($con, $_POST['s_name']);
    $f_name = mysqli_real_escape_string($con, $_POST['f_name']);
    $m_name = mysqli_real_escape_string($con, $_POST['m_name']);
    $dod = $_POST['dod'];
    $blood = $_POST['blood'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $per_careof = mysqli_real_escape_string($con, $_POST['per_careof']);
    $per_village = mysqli_real_escape_string($con, $_POST['per_village']);
    $pdivi = $_POST['pdivi'];
    $pdist = $_POST['pdist'];
    $p_posto = $_POST['p_posto'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    $branch_id = $_POST['branch_id'];

    $base_upload_path = "../../user_img/"; 
    $nid_upload_path = "../../user_img/nid/";

    // Process Profile Image
    if(!empty($_FILES['image']['name'])){
        $image = time().'_'.str_replace(' ', '_', $_FILES['image']['name']); 
        $tmp_name = $_FILES['image']['tmp_name'];
        $upload_check = move_uploaded_file($tmp_name, $base_upload_path . $image);
    } else {
        $image = $data['image'];
        $upload_check = true;
    }

    // Process NID Image
    if(!empty($_FILES['nid']['name'])){
        $nid = time().'_'.str_replace(' ', '_', $_FILES['nid']['name']);
        $nid_tmp = $_FILES['nid']['tmp_name'];
        $nid_check = move_uploaded_file($nid_tmp, $nid_upload_path . $nid);
    } else {
        $nid = $data['nid'];
        $nid_check = true;
    }

    if($upload_check && $nid_check) {
        $sql = "UPDATE user SET 
                s_name = '$s_name', f_name = '$f_name', m_name = '$m_name', dod = '$dod', 
                blood = '$blood', gender = '$gender', phone = '$phone', per_careof = '$per_careof', 
                per_village = '$per_village', pdivi = '$pdivi', pdist = '$pdist', p_posto = '$p_posto', 
                password = '$password', image = '$image', nid = '$nid', user_type = '$user_type', 
                branch_id = '$branch_id' WHERE id = '$idd'";

        if (mysqli_query($con, $sql)) {
            // Success Alert with proper redirection logic
            echo '<script type="text/javascript">
                swal({
                    title: "Updated!",
                    text: "User information has been updated successfully.",
                    icon: "success",
                    button: "OK",
                }).then(function() {
                    window.location.href = "all_user.php";
                });
            </script>';
        } else {
            $err = mysqli_error($con);
            echo "<script>swal('Database Error', '$err', 'error');</script>";
        }
    } else {
        echo "<script>swal('Upload Error', 'Failed to move files. Check folder permissions.', 'error');</script>";
    }
}
include('ini/footer.php');
?>