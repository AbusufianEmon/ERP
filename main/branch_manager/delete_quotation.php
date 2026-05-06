<?php
session_start();
include('dbcon.php');

if (!isset($_GET['invoice_id'])) {
    die("Invalid Invoice");
}

$invoice_id = mysqli_real_escape_string($con, $_GET['invoice_id']);

/* ===============================
   Delete Quotation
================================ */
$delete = mysqli_query($con, "
    DELETE FROM corporate_quotation 
    WHERE corporate_quotation_invoice_id = '$invoice_id'
");

if ($delete) {
    $status = "success";
    $msg = "Quotation deleted successfully!";
} else {
    $status = "error";
    $msg = "Failed to delete quotation!";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Delete Quotation</title>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<script>
Swal.fire({
    icon: "<?= $status ?>",
    title: "<?= $msg ?>",
    timer: 2000,
    showConfirmButton: false
}).then(() => {
    window.location.href = "total_stock.php";
});
</script>

</body>
</html>
