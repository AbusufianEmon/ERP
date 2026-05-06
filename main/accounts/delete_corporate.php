<?php 
session_start();
include('dbcon.php');

// Security Check: Only allow logged-in inventory managers
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 5) {
    header('Location: ../../index.php');
    exit();
}

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $delete = "DELETE FROM corporate_customer WHERE corporate_id = $id";
    
    if(mysqli_query($con, $delete)) {
        echo "<script>alert('Customer Deleted Successfully'); window.location.href='all_corporate.php';</script>";
    } else {
        echo "<script>alert('Error Deleting Customer'); window.location.href='all_corporate.php';</script>";
    }
} else {
    header('Location: all_corporate.php');
}
?>