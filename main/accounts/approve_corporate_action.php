<?php
session_start();
include('dbcon.php');

/* ============================================================
   1. SECURITY CHECK
   ============================================================ */
if (!isset($_SESSION['email'])) {
    header('Location: ../../index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status_type = "error";
$message = "";

/* ============================================================
   2. PROCESS APPROVAL
   ============================================================ */
if ($id > 0) {
    // Prepare the update statement
    $sql = "UPDATE corporate_customer SET accounts_approvel_status = 1 WHERE corporate_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $status_type = "success";
            $message = "Corporate customer has been successfully approved.";
        } else {
            $message = "Error updating record: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Failed to prepare the database statement.";
    }
} else {
    $message = "Invalid ID. No customer selected for approval.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Approval...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #f8f9fc;">

<script>
    // Trigger SweetAlert feedback
    Swal.fire({
        icon: '<?php echo $status_type; ?>',
        title: '<?php echo ($status_type == "success") ? "Approved!" : "Failed!"; ?>',
        text: '<?php echo $message; ?>',
        confirmButtonColor: '#4e73df',
    }).then((result) => {
        // Redirect back to the pending approvals page
        window.location.href = 'corporate_head_approval_pending.php';
    });
</script>

</body>
</html>