<?php
session_start();
include('dbcon.php');

/* ===============================
   ENABLE ERROR REPORTING
================================ */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ===============================
   VALIDATE INPUT
================================ */
if (!isset($_GET['corporate_quotation_invoice_id']) || empty($_GET['corporate_quotation_invoice_id'])) {
    die("Invalid Quotation Invoice");
}

$invoice_id = mysqli_real_escape_string($con, $_GET['corporate_quotation_invoice_id']);

try {

    /* ===============================
       UPDATE APPROVAL STATUS
    ================================ */
    $updateSql = "
        UPDATE corporate_quotation 
        SET manager_approvel_status = 1 
        WHERE corporate_quotation_invoice_id = '$invoice_id'
    ";

    mysqli_query($con, $updateSql);

    /* ===============================
       SUCCESS SWEETALERT
    ================================ */
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Approved</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: "success",
                title: "Approved!",
                text: "Corporate quotation has been approved successfully.",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "total_stock.php";
            });
        </script>
    </body>
    </html>
    ';
    exit;

} catch (Exception $e) {

    /* ===============================
       ERROR SWEETALERT
    ================================ */
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Error</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "'.$e->getMessage().'",
                confirmButtonText: "OK"
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>
    ';
    exit;
}
?>
