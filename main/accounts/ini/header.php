<?php 
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 5) {
    header('Location: ../../index.php');
    exit();
}

include('dbcon.php');

$id = intval($_SESSION['id']); 
$sql = "SELECT u.*, b.branch_name 
        FROM user u 
        LEFT JOIN branches b ON u.branch_id = b.branch_id 
        WHERE u.id = $id";

$exe = mysqli_query($con, $sql);

if (!$exe || mysqli_num_rows($exe) == 0) {
    session_destroy();
    header('Location: ../../index.php');
    exit();
}

$data = mysqli_fetch_assoc($exe);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MTE ERP | Management</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    
                <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <style>
        /* Modern Sidebar Design */
        .sidebar-premium {
            background: linear-gradient(180deg, #0f5132 0%, #0a3622 100%) !important;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        .sidebar-premium .sidebar-brand {
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 1rem;
        }
        .sidebar-premium .nav-item .nav-link {
            padding: 0.85rem 1.5rem;
            margin: 0.2rem 0.8rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .sidebar-premium .nav-item .nav-link i {
            font-size: 1rem;
            width: 1.5rem;
            margin-right: 0.75rem;
            opacity: 0.8;
        }
        .sidebar-premium .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateX(5px);
        }
        .sidebar-premium .nav-item.active .nav-link {
            background: #198754 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .sidebar-heading {
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 800;
            font-size: 0.65rem !important;
            color: rgba(255,255,255,0.4) !important;
            margin-left: 1.5rem;
            margin-top: 1rem;
        }
        #content-wrapper { background-color: #f8f9fc; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav sidebar sidebar-dark accordion sidebar-premium" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-rocket"></i></div>
                <div class="sidebar-brand-text mx-3">MTE ERP</div>
            </a>

            <div class="sidebar-heading">Core</div>
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Management</div>
            <li class="nav-item">
                <a class="nav-link" href="all_corporate.php">
                    <i class="fas fa-fw fa-building"></i>
                    <span>All Corporate</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="corporate_head_approval_pending.php">
                    <i class="fas fa-fw fa-hourglass-half"></i>
                    <span>Corporate Head Approvals</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customer_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Customer List</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Reports</div>
            <li class="nav-item">
                <a class="nav-link" href="cash_in_hand_report_all.php">
                    <i class="fas fa-fw fa-wallet"></i>
                    <span>Cash In Hand</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cash_deposit_report_all.php">
                    <i class="fas fa-fw fa-piggy-bank"></i>
                    <span>Cash Deposits</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Expenses</div>
            <li class="nav-item">
                <a class="nav-link" href="expense_report_all.php">
                    <i class="fas fa-fw fa-file-invoice-dollar"></i>
                    <span>Master Report</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_expense_category.php">
                    <i class="fas fa-fw fa-tags"></i>
                    <span>Expense Categories</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
             <li class="nav-item">
                <a class="nav-link" href="corporate_collection_pending_all.php">
                    <i class="fas fa-fw fa-tags"></i>
                    <span>Corporate Collection Pending All</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customer_due_report_all.php">
                    <i class="fas fa-fw fa-tags"></i>
                    <span>Customer Due Report All</span>
                </a>
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
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <?php $firstName = explode(' ', trim($data['s_name']))[0]; ?>
                        <h1 class="h3 mb-0 text-gray-800">Hello, <?php echo $firstName ?> <span style="font-size: 14px; color: #198754;">(Accounts & CRM)</span></h1>
                    </div>
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $data['s_name'] ?></span>
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
                <div class="container-fluid">


