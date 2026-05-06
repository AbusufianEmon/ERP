<?php
include('ini/header.php');
include('dbcon.php');

// Get CEO info
$user_sql = "SELECT u.*, b.branch_name 
            FROM user u 
            LEFT JOIN branches b ON u.branch_id = b.branch_id 
            WHERE u.id = $id";
$exe = mysqli_query($con, $user_sql);
$user_data = mysqli_fetch_assoc($exe);
?>


</div>

<?php include('ini/footer.php'); ?>
