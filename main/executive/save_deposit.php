<?php
session_start();
include('dbcon.php');

if(isset($_POST['btn_deposit'])){
    $branch_id = intval($_POST['branch_id']);
    $amount = floatval($_POST['amount']);
    $date = mysqli_real_escape_string($con, $_POST['deposit_date']);
    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);
    
    $file_name = "";
    if($_FILES['slip_photo']['name']){
        $file_name = time() . "_" . $_FILES['slip_photo']['name'];
        move_uploaded_file($_FILES['slip_photo']['tmp_name'], "uploads/" . $file_name);
    }

    $sql = "INSERT INTO cash_deposits (branch_id, amount, deposit_date, slip_photo, remarks) 
            VALUES ($branch_id, $amount, '$date', '$file_name', '$remarks')";

    if(mysqli_query($con, $sql)){
        header("Location: cash_in_hand_report.php?branch_id=$branch_id&msg=success");
    } else {
        echo "Error: " . mysqli_error($con);
    }
}
?>