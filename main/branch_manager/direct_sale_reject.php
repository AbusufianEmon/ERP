<?php
session_start();
include('dbcon.php');

// 1. Check if invoice_id is provided
if (isset($_GET['invoice_id'])) {
    
    // 2. Sanitize the input to prevent SQL Injection
    $invoice_no = mysqli_real_escape_string($con, $_GET['invoice_id']);

    // 3. Construct the delete query
    // This will delete all rows associated with this invoice number
    $query = "DELETE FROM direct_sales WHERE invoice_no = '$invoice_no'";
    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        // Optional: Set a success message using session
        $_SESSION['message'] = "Invoice $invoice_no has been successfully rejected and deleted.";
        
        // 4. Redirect back to a listing page or dashboard
        header("Location: total_stock.php"); 
        exit(0);
    } else {
        // Handle database errors
        die("Error deleting record: " . mysqli_error($con));
    }

} else {
    // Redirect if someone tries to access the file directly without an ID
    header("Location: total_stock.php");
    exit(0);
}
?>