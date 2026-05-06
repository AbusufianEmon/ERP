<?php 
include('dbcon.php');

// 1. Get the invoice number and sanitize it
if (isset($_GET['invoice_no'])) {
    $invoice_no = mysqli_real_escape_string($con, $_GET['invoice_no']);

    // 2. Wrap the variable in single quotes '$invoice_no' so SQL treats it as a string
    $sql = "DELETE FROM sup_invoice WHERE invoice_no = '$invoice_no'";
    
    $run = mysqli_query($con, $sql);

    if ($run) {
        echo "<script>
                alert('Invoice $invoice_no has been deleted successfully.');
                window.open('all_sup_invoice.php', '_self');
              </script>";
    } else {
        // Display actual error if it fails
        echo "Error deleting record: " . mysqli_error($con);
    }
} else {
    header('location:sup_invoice.php');
}
?>