<?php 
require 'PHPmailer/Exception.php';
require 'PHPmailer/PHPMailer.php';
require 'PHPmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

// 1. Check if Admin is logged in and ID is provided
if (isset($_SESSION['email']) && isset($_GET['id'])) {
    
    include('dbcon.php');
    $id = mysqli_real_escape_string($con, $_GET['id']);

    // 2. Fetch user data before deleting so we have the email/name for the mailer
    $sql = "SELECT * FROM user WHERE id=$id";
    $run = mysqli_query($con, $sql);
    $data = mysqli_fetch_assoc($run);

    if ($data) {
        // 3. Delete the user from the database
        $sql2 = "DELETE FROM user WHERE id=$id";
        $run2 = mysqli_query($con, $sql2);

        if ($run2) {
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();                                            
                $mail->Host       = 'smtp.gmail.com';                     
                $mail->SMTPAuth   = true;                                   
                $mail->Username   = 'oftanimerp@gmail.com'; 
                $mail->Password   = 'xrqb jvpq dexr xixb'; // 16-character App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
                $mail->Port       = 587;                                    

                // SSL FIX for Localhost
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                // Recipients
                $mail->setFrom('oftanimerp@gmail.com', 'Inventory System 2.0');
                $mail->addAddress($data['email'], $data['s_name']);     

                // Content
                $mail->isHTML(true);                                  
                $mail->Subject = 'Registration Request Declined - IS 2.0';
                
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #dc3545;'>Registration Declined</h2>
                        <p>Hello <strong>".htmlspecialchars($data['s_name'])."</strong>,</p>
                        <p>We regret to inform you that your registration request for the Inventory System has been <strong>declined</strong>.</p>
                        <p>If you believe this is a mistake, please contact the authority at the number below and try again.</p>
                        
                        <p style='margin-top: 20px;'>Best Regards,<br><strong>MRE ERP Team</strong><br>Support: 01796592345</p>
                    </div>";

                $mail->send();
                
                echo "<script>
                        alert('Request Declined & Notification Email sent.');
                        window.location.href = 'u_request.php';
                      </script>";

            } catch (Exception $e) {
                echo "User deleted from database, but Email failed to send. <br>";
                echo "Mailer Error: {$mail->ErrorInfo} <br>";
                echo "<a href='u_request.php'>Return to Request List</a>";
            }
        } else {
            echo "Database Error: Could not delete user.";
        }
    } else {
        echo "Error: User not found.";
    }
} else {
    echo "Error: Unauthorized access or missing ID.";
}
?>