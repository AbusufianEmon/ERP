<?php 
session_start();
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 1) {
    header('Location: ../../index.php');
    exit();
}

include('dbcon.php');
$id = intval($_SESSION['id']); 

$sql = "SELECT u.*, b.branch_name FROM user u LEFT JOIN branches b ON u.branch_id = b.branch_id WHERE u.id = $id";
$exe = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($exe);
$firstName = explode(' ', trim($data['s_name']))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MTE ERP | CEO Panel</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    
    <style>
        .sidebar { background: #2c3e50 !important; } /* Modern Navy Blue */
        .sidebar-brand { background: rgba(0,0,0,0.1); }
        .nav-item i { width: 1.5rem; text-align: center; margin-right: 5px; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-rocket"></i></div>
                <div class="sidebar-brand-text mx-3">MTE ERP</div>
            </a>

            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>CEO Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">User Management</div>
            <li class="nav-item">
                <a class="nav-link" href="all_user.php"><i class="fas fa-users"></i> <span>User List</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="u_request.php"><i class="fas fa-user-clock"></i> <span>Pending Requests</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Operations</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#branchMenu">
                    <i class="fas fa-store"></i> <span>Branches</span>
                </a>
                <div id="branchMenu" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="branch_list.php">View List</a>
                        <a class="collapse-item" href="add_branch.php">Add New Branch</a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Financial Reports</div>
            <li class="nav-item">
                <a class="nav-link" href="revenu_report.php"><i class="fas fa-chart-line text-success"></i> <span>Revenue Report</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profit_report.php"><i class="fas fa-hand-holding-usd"></i> <span>Sales Profit</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="expense_report_all.php"><i class="fas fa-file-invoice-dollar"></i> <span>Expense Report</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="loss_report.php"><i class="fas fa-exclamation-triangle text-warning"></i> <span>Loss Report</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Stock Transfers</div>
            <li class="nav-item">
                <a class="nav-link" href="transfer_approval_pending.php"><i class="fas fa-check-circle"></i> <span>Pending Approval</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="transfer_receive_pending.php"><i class="fas fa-truck-loading"></i> <span>Pending Receive</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h1 class="h4 mb-0 text-gray-800 ml-2">Welcome, <span class="text-primary font-weight-bold"><?php echo $firstName ?></span></h1>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $data['s_name'] ?> (CEO)</span>
                                <img class="img-profile rounded-circle" src="../../user_img/<?php echo $data['image'] ?>">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>