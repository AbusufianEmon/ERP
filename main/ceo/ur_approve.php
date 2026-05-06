<?php 
require 'PHPmailer/Exception.php';
require 'PHPmailer/PHPMailer.php';
require 'PHPmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

// Ensure user is logged in
if(!isset($_SESSION['email'])){
    header('location: ../login.php');
    exit();
}

if(isset($_GET['id'])) {
    include('dbcon.php');
    $id = intval($_GET['id']); 

    // 1. Fetch User Data
    $sql = "SELECT * FROM user WHERE id=$id";
    $run = mysqli_query($con, $sql);
    $data = mysqli_fetch_assoc($run);

    if($data) {
        // 2. Update Status to Approved
        $sql2 = "UPDATE user SET status = 1 WHERE id=$id";
        $run2 = mysqli_query($con, $sql2);

        if($run2){
            $mail = new PHPMailer(true);

            try {
                // --- SERVER SETTINGS ---
                $mail->isSMTP();                                            
                $mail->Host       = 'smtp.gmail.com';                     
                $mail->SMTPAuth   = true;                                   
                $mail->Username   = 'oftanimerp@gmail.com'; // Updated Email
                
                // IMPORTANT: Replace 'your-new-app-password' with the 16-character code from Google
                $mail->Password   = 'xrqb jvpq dexr xixb'; 
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
                $mail->Port       = 587;                                    

                // --- SSL FIX (Necessary for Localhost/XAMPP) ---
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                // --- RECIPIENTS ---
                $mail->setFrom('oftanimerp@gmail.com', 'Inventory System 2.0');
                $mail->addAddress($data['email'], $data['s_name']);     

                // --- CONTENT ---
                $mail->isHTML(true);                                  
                $mail->Subject = 'IS 2.0 Registration Approval Report';
                
                $u_email = htmlspecialchars($data['email']);
                $u_pass  = htmlspecialchars($data['password']);

                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #28a745;'>Registration Approved!</h2>
                        <p>Hello <strong>".htmlspecialchars($data['s_name'])."</strong>,</p>
                        <p>Your registration request has been approved. You can now access your account using the credentials below:</p>
                        <div style='background: #f8f9fa; padding: 15px; border-left: 5px solid #28a745;'>
                            <p><strong>Login ID:</strong> $u_email</p>
                            <p><strong>Password:</strong> $u_pass</p>
                        </div>
                        <p style='margin-top: 20px;'>Best Regards,<br><strong>MRE ERP</strong><br>Support: 01756569753</p>
                    </div>";

                $mail->send();
                
                echo "<script>
                        alert('User Approved & Confirmation Mail sent successfully');
                        window.location.href = 'u_request.php';
                      </script>";
            } catch (Exception $e) {
                echo "Status updated in database, but Email failed. <br>";
                echo "Mailer Error: {$mail->ErrorInfo} <br>";
                echo "<strong>Action:</strong> Check if you used the 16-character App Password for oftanimerp@gmail.com.<br>";
                echo "<a href='u_request.php'>Return to Request List</a>";
            }
        } else {
            echo "Database Error: Could not update user status.";
        }
    } else {
        echo "Error: User ID not found in database.";
    }
} else {
    echo "Error: No ID provided.";
}
?>