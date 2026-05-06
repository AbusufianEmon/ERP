<?php 
include('ini/header.php'); 
include('dbcon.php');

if(isset($_GET['id'])){
    $id = intval($_GET['id']);

    // SQL with JOIN to get branch name
    $sql = "SELECT u.*, b.branch_name 
            FROM user u 
            LEFT JOIN branches b ON u.branch_id = b.branch_id 
            WHERE u.id = $id";
    
    $exe = mysqli_query($con, $sql);
    $data = mysqli_fetch_assoc($exe);

    if(!$data) { echo "User not found"; exit; }

    // User type mapping
    $user_types = [
        1 => "CEO", 2 => "Inventory Manager", 3 => "HR", 
        4 => "Branch Manager", 5 => "Employee"
    ];
    $user_type_name = $user_types[$data['user_type']] ?? "Unknown";
} else {
    header('location: all_user.php');
    exit();
}
?>

<style>
    /* Styling for the Profile Card */
    .profile-card {
        border-radius: 15px;
        overflow: hidden;
    }
    .profile-header-bg {
        background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
        height: 120px;
    }
    .profile-img-container {
        margin-top: -60px;
        text-align: center;
    }
    .profile-img {
        width: 130px;
        height: 130px;
        border: 5px solid #fff;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .nid-img {
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: 0.3s;
    }
    .nid-img:hover { transform: scale(1.02); }

    /* Print Specific Styling */
    @media print {
        .sidebar, .navbar, .btn, .no-print {
            display: none !important;
        }
        .container-fluid {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .profile-header-bg {
            background: #eee !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<div class="container-fluid">
    <div class="row no-print">
        <div class="col-12 mb-3">
            <a href="all_user.php" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm float-right">
                <i class="fas fa-print"></i> Print as PDF
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4 profile-card">
                <div class="profile-header-bg"></div>
                <div class="profile-img-container">
                    <img src="../../user_img/<?php echo $data['image']; ?>" class="profile-img">
                    <h4 class="mt-2 font-weight-bold text-gray-800"><?php echo $data['s_name']; ?></h4>
                    <span class="badge badge-primary p-2"><?php echo $user_type_name; ?></span>
                </div>
                <div class="card-body">
                    <hr>
                    <div class="text-center mb-3">
                        <small class="text-uppercase font-weight-bold text-muted">Identity Document (NID)</small>
                        <img src="../../user_img/nid/<?php echo $data['nid']; ?>" class="img-fluid nid-img mt-2">
                    </div>
                    <a href="edit_user.php?id=<?php echo $data['id']?>" class="btn btn-outline-info btn-block no-print">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle mr-2"></i>Personal Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="font-weight-bold text-muted small">Father's Name</label>
                            <p class="text-dark"><?php echo $data['f_name']; ?></p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="font-weight-bold text-muted small">Mother's Name</label>
                            <p class="text-dark"><?php echo $data['m_name']; ?></p>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="font-weight-bold text-muted small">Date of Birth</label>
                            <p class="text-dark"><?php echo $data['dod']; ?></p>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="font-weight-bold text-muted small">Blood Group</label>
                            <p class="text-danger font-weight-bold"><?php echo $data['blood']; ?></p>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="font-weight-bold text-muted small">Gender</label>
                            <p class="text-dark"><?php echo $data['gender']; ?></p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="font-weight-bold text-muted small">Phone Number</label>
                            <p class="text-dark"><?php echo $data['phone']; ?></p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="font-weight-bold text-muted small">Email Address</label>
                            <p class="text-dark"><?php echo $data['email']; ?></p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="font-weight-bold text-muted small">Assigned Branch</label>
                            <p class="text-primary font-weight-bold"><?php echo $data['branch_name']; ?></p>
                        </div>
                    </div>

                    <h6 class="mt-4 font-weight-bold text-primary"><i class="fas fa-map-marker-alt mr-2"></i>Permanent Address</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1 small"><b>Care Of:</b> <?php echo $data['per_careof']; ?></p>
                            <p class="mb-1 small"><b>Village:</b> <?php echo $data['per_village']; ?></p>
                            <p class="mb-1 small"><b>P.O:</b> <?php echo $data['p_posto']; ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 small"><b>District:</b> <?php echo $data['pdist']; ?></p>
                            <p class="mb-1 small"><b>Division:</b> <?php echo $data['pdivi']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>