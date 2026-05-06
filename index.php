<?php 
// Start session at the very top to prevent "headers already sent" errors
session_start();
include('dbcon.php'); 

if (isset($_POST['submit'])) {
    // Sanitize inputs to prevent SQL injection and fix naming mismatch
    $mail = mysqli_real_escape_string($con, $_POST['email']); 
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Check if database connection is active
    if (!$con) {
        echo "<script>alert('Database Connection Failed');</script>";
    }

    $sql = "SELECT * FROM user WHERE email = '$mail' AND password = '$password'";
    $exe = mysqli_query($con, $sql);
    
    if($exe) {
        $result = mysqli_fetch_assoc($exe);
        $check = mysqli_num_rows($exe);

        if ($check == 0) {
            $error_trigger = "invalid";
        } else {
            $id = $result['id'];
            $status = $result['status'];
            $user_type = $result['user_type'];

            if ($status == 0) {
                $error_trigger = "unauthorized";
            } else {
                $_SESSION['email'] = $mail;
                $_SESSION['id'] = $id;
                $_SESSION['user_type'] = $user_type;

                // Redirect based on user_type
                if ($user_type == 1) {
                    header("Location: main/ceo/index.php?id=$id");
                } elseif ($user_type == 2) {
                    header("Location: main/inventory_manager/index.php?id=$id");
                } elseif ($user_type == 3) {
                    header("Location: main/branch_manager/index.php?id=$id");
                } elseif ($user_type == 4) {
                    header("Location: main/executive/index.php?id=$id");
                } elseif ($user_type == 5) {
                    header("Location: main/accounts/index.php?id=$id");
                }
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MTE ERP</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/sweetalert.min.js"></script>
    <style>
        :root { --primary-color: #2ecc71; --bg-dark: #0f172a; --accent-blue: #3b82f6; --accent-purple: #8b5cf6; }
        body, html { height: 100%; margin: 0; font-family: 'Poppins', sans-serif; background-color: var(--bg-dark); overflow: hidden; }
        .bg-animation { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; background: radial-gradient(circle at 50% 50%, #1e293b 0%, #0f172a 100%); }
        .blob { position: absolute; width: 500px; height: 500px; background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%); filter: blur(80px); border-radius: 50%; opacity: 0.15; animation: move 20s infinite alternate; }
        .blob-2 { right: -100px; bottom: -100px; background: var(--primary-color); animation-duration: 15s; animation-delay: -5s; }
        @keyframes move { from { transform: translate(0, 0) scale(1); } to { transform: translate(100px, 100px) scale(1.2); } }
        .login-container { height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; z-index: 1; }
        .login-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 50px 40px; width: 100%; max-width: 420px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); text-align: center; }
        .logo-section img { height: 70px; width: 70px; border-radius: 20px; background: white; padding: 10px; margin-bottom: 25px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); }
        h2 { color: white; font-weight: 600; font-size: 1.5rem; margin-bottom: 8px; }
        p.subtitle { color: #94a3b8; font-size: 0.9rem; margin-bottom: 35px; }
        .form-group { margin-bottom: 20px; position: relative; }
        .form-group i { position: absolute; left: 20px; top: 18px; color: #64748b; }
        .form-control { height: 56px; background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; color: white !important; padding-left: 55px; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); }
        .btn-login { height: 56px; border-radius: 16px; background: var(--primary-color); border: none; color: #052e16; font-weight: 600; width: 100%; margin-top: 15px; cursor: pointer; }
        .footer-links { margin-top: 30px; color: #94a3b8; font-size: 0.85rem; }
        .footer-links a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        .admin-note { margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="blob"></div>
        <div class="blob blob-2"></div>
    </div>
    <section class="login-container">
        <div class="login-card">
            <div class="logo-section"><img src="images/vector.png" alt="Logo"></div>
            <h2>MTE ERP</h2>
            <p class="subtitle">Enter your credentials to access MTE ERP</p>
            <form method="post" action="">
                <div class="form-group text-left">
                    <i class="fa fa-envelope"></i>
                    <input name="email" type="email" class="form-control" placeholder="Email Address" required>
                </div>
                <div class="form-group text-left">
                    <i class="fa fa-lock"></i>
                    <input name="password" type="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="submit" class="btn btn-login">Sign In</button>
            </form>
            <div class="footer-links">
                Don't have an account? <a href="reg.php">Register</a>
                <div class="admin-note">Need approval? <a href="https://www.facebook.com/omorfaruk.tanim/" target="_blank">Contact Admin</a></div>
            </div>
        </div>
    </section>

    <?php if(isset($error_trigger)): ?>
    <script>
        <?php if($error_trigger == "invalid"): ?>
            swal("Error!!", "User name and Password do not match.", "error");
        <?php elseif($error_trigger == "unauthorized"): ?>
            swal("Unauthorized!!", "Your registration is unauthorized. Contact Admin.", "warning");
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>