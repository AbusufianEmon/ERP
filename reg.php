<?php 
$con = mysqli_connect('127.0.0.1','root','','inventory');
if(!$con){
    die("Database connection failed!");
}

$sql = "SELECT * FROM branches";
$run = mysqli_query($con,$sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | MTE ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="text/javascript" src="assets/js/sweetalert.min.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .registration-card {
            background: #ffffff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header {
            background: #28a745;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .section-title {
            border-left: 5px solid #28a745;
            padding-left: 15px;
            margin: 25px 0 20px;
            color: #2c3e50;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
            border-color: #28a745;
        }
        .btn-submit {
            background: #28a745;
            border: none;
            padding: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        marquee {
            background: #fff3f3;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="registration-card">
                <div class="form-header">
                    <h3><i class="fas fa-user-plus me-2"></i> User Registration</h3>
                    <p class="mb-0">MTE ERP | <a href="index.php" class="text-white text-decoration-underline">Login here</a></p>
                </div>

                <div class="p-4 p-md-5">
                    <marquee class="text-danger font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i> PLEASE PROVIDE ACCURATE INFORMATION. INACCURATE DATA WILL LEAD TO AUTOMATIC DECLINE.
                    </marquee>

                    <form action="" method="post" enctype="multipart/form-data">

                        <h5 class="section-title">Personal Details</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="s_name" class="form-control" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Father's Name</label>
                                <input type="text" name="f_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mother's Name</label>
                                <input type="text" name="m_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dod" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Blood Group</label>
                                <select name="blood" class="form-select" required>
                                    <option value="">Select Group</option>
                                    <option>A+</option><option>AB+</option><option>B+</option>
                                    <option>A-</option><option>AB-</option><option>O+</option><option>O-</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option>Male</option><option>Female</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Phone Number (11 Digits)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="phone" class="form-control" placeholder="01712345678" 
                                           pattern="[0-9]{11}" maxlength="11" minlength="11" 
                                           title="Please enter exactly 11 digits" required>
                                </div>
                            </div>
                        </div>

                        <h5 class="section-title">Permanent Address</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Care Of</label>
                                <input type="text" name="per_careof" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Village/Town/Road</label>
                                <input type="text" name="per_village" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Division</label>
                                <input type="text" name="pdivi" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">District</label>
                                <input type="text" name="pdist" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PS/Upazila</label>
                                <input type="text" name="p_posto" class="form-control" required>
                            </div>
                        </div>

                        <h5 class="section-title">Role & Assignment</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">User Type</label>
                                <select name="user_type" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <option value="1">CEO</option>
                                    <option value="2">Inventory Manager</option>
                                    <option value="3">Branch Manager</option>
                                    <option value="4">Executive</option>
                                    <option value="5">Accounts</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assign Branch</label>
                                <select name="branch_id" class="form-select" required>
                                    <option value="">Select Branch</option>
                                    <?php 
                                    while($data = mysqli_fetch_assoc($run)) {
                                        echo "<option value='{$data['branch_id']}'>{$data['branch_name']}</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Photo</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Max size 2MB (JPG, PNG)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NID Scan Copy</label>
                                <input type="file" name="nid" class="form-control" required>
                                <small class="text-muted">Clear image of NID</small>
                            </div>
                        </div>

                        <h5 class="section-title">Credentials</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Create Password (Min 6 Characters)</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit" class="btn btn-submit btn-lg text-white">
                                <i class="fas fa-paper-plane me-2"></i> Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php 
if (isset($_POST['submit'])) {
    include('dbcon.php');

    $s_name = mysqli_real_escape_string($con, $_POST['s_name']);
    $f_name = mysqli_real_escape_string($con, $_POST['f_name']);
    $m_name = mysqli_real_escape_string($con, $_POST['m_name']);
    $dod = $_POST['dod'];
    $blood = $_POST['blood'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $per_careof = $_POST['per_careof'];
    $per_village = $_POST['per_village'];
    $pdivi = $_POST['pdivi'];
    $pdist = $_POST['pdist'];
    $p_posto = $_POST['p_posto'];
    $user_type = $_POST['user_type'];
    $branch_id = $_POST['branch_id'];
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    // PHP SERVER SIDE VALIDATION
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         echo "<script>swal('Invalid Email','Please provide a real email address','error');</script>";
    } elseif(strlen($password) < 6) {
         echo "<script>swal('Weak Password','Password must be at least 6 characters long','error');</script>";
    } elseif(!preg_match('/^[0-9]{11}$/', $phone)) {
         echo "<script>swal('Invalid Phone','Phone number must be exactly 11 digits','error');</script>";
    } else {
        // File Uploads
        $image = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $upload_path = 'user_img/';
        $upload_ok = move_uploaded_file($tmp_name, $upload_path . $image);

        $nid = $_FILES['nid']['name'];
        $nid_tmp = $_FILES['nid']['tmp_name'];
        $nid_path = 'user_img/nid/';
        $nid_ok = move_uploaded_file($nid_tmp, $nid_path . $nid);

        if ($upload_ok && $nid_ok) {
            $chk_email = "SELECT * FROM user WHERE email = '$email'";
            $chk_run = mysqli_query($con, $chk_email);

            if (mysqli_num_rows($chk_run) > 0) {
                echo "<script>swal('Email Already Registered','This email is already in our system.','error');</script>";
            } else {
                $sql = "INSERT INTO `user`(`s_name`, `f_name`, `m_name`, `dod`, `blood`, `gender`, `phone`, `per_careof`, `per_village`, `pdivi`, `pdist`, `p_posto`, `user_type`, `branch_id`, `image`, `nid`, `email`, `password`, `status`)
                        VALUES ('$s_name','$f_name','$m_name','$dod','$blood','$gender','$phone','$per_careof','$per_village','$pdivi','$pdist','$p_posto','$user_type','$branch_id','$image','$nid','$email','$password', 0)";

                $exe = mysqli_query($con, $sql);

                if ($exe) {
                    echo "<script>swal('Registration Success','Your application has been submitted for approval.','success').then(() => { window.location.href='index.php'; });</script>";
                } else {
                    echo "<script>swal('Error','Failed to save data.','error');</script>";
                }
            }
        } else {
            echo "<script>swal('Upload Failed','Could not upload your photo or NID.','error');</script>";
        }
    }
}
?>