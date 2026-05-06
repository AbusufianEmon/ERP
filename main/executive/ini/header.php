<?php 
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 4) {
    // Not logged in or not inventory manager
    header('Location: ../../index.php');
    exit();
}

include('dbcon.php');

$id = intval($_SESSION['id']); // Secure the ID

$sql = "SELECT u.*, b.branch_name 
        FROM user u 
        LEFT JOIN branches b ON u.branch_id = b.branch_id 
        WHERE u.id = $id";

$exe = mysqli_query($con, $sql);

if (!$exe || mysqli_num_rows($exe) == 0) {
    // User not found
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
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Stock | Executive</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script type="text/javascript" src="js/sweetalert.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.9/css/buttons.dataTables.min.css">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.colVis.min.js"></script>

    <style>
        /* UNIQUE SIDEBAR DESIGN */
        #accordionSidebar {
            background: #2c3e50 !important; 
            background-image: linear-gradient(180deg, #2c3e50 0%, #000000 100%) !important;
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand {
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 1rem;
            letter-spacing: 2px;
        }

        .nav-item .nav-link {
            position: relative;
            margin: 4px 15px;
            border-radius: 8px;
            padding: 12px 15px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: rgba(255,255,255,0.7) !important;
        }

        .nav-item .nav-link i {
            color: #3498db; 
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-item.active .nav-link, 
        .nav-item .nav-link:hover {
            background: rgba(52, 152, 219, 0.15) !important;
            color: #fff !important;
            transform: translateX(5px);
        }

        .nav-item.active .nav-link::before {
            content: "";
            position: absolute;
            left: -15px;
            top: 10%;
            height: 80%;
            width: 4px;
            background: #3498db;
            border-radius: 0 4px 4px 0;
            box-shadow: 2px 0 10px rgba(52, 152, 219, 0.5);
        }

        .sidebar-heading {
            color: rgba(255,255,255,0.3) !important;
            font-weight: 800;
            font-size: 0.7rem !important;
            margin-top: 1.5rem;
            padding-left: 25px !important;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.05) !important;
            margin: 1rem 0 !important;
        }

        /* TOPBAR DESIGN */
        .topbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e3e6f0;
        }

        .executive-badge {
            background: #ebf5fb;
            color: #2980b9;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid #d6eaf8;
        }

        /* NEW BRANCH BADGE STYLE */
        .branch-pill {
            background: #27ae60;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            box-shadow: 0 2px 4px rgba(39, 174, 96, 0.2);
        }

        .img-profile {
            border: 2px solid #3498db;
            padding: 2px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-bolt text-info"></i>
                </div>
                <div class="sidebar-brand-text mx-3">MTE ERP</div>
            </a>

            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-layer-group"></i>
                    <span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Retail Operations</div>

            <li class="nav-item">
                <a class="nav-link" href="direct_sales.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Create Direct Sale</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="direct_sales_list.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-list-ul"></i>
                    <span>Sales History</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_customer.php">
                    <i class="fas fa-fw fa-user-friends"></i>
                    <span>Customer LIST</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Corporate Desk</div>

            <li class="nav-item">
                <a class="nav-link" href="all_corporate.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Corporate Clients</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="corporate_quotation.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-file-contract"></i>
                    <span>New Quotation</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_all_quotation.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-file-invoice"></i>
                    <span>Quotation Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="corporate_sales.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Corporate Sales</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="all_corporate_bill.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-receipt"></i>
                    <span>Invoice Records</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="corporate_bill_pending.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-hourglass-half"></i>
                    <span>Pending Bills</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Inventory</div>

            <li class="nav-item">
                <a class="nav-link" href="view_stock.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Branch Stock</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="total_stock.php">
                    <i class="fas fa-fw fa-globe-americas"></i>
                    <span>Global Stock</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Reporting</div>

            <li class="nav-item">
                <a class="nav-link" href="direct_sales_report_view.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>Direct Sales Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="corporate_sales_report.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Corporate Sales Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cash_in_hand_report.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-fw fa-wallet"></i>
                    <span>Cash In Hand</span>
                </a>
            </li>

            <div class="text-center d-none d-md-inline mt-4">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars text-info"></i>
                    </button>

                    <div class="d-flex align-items-center">
                        <?php $firstName = explode(' ', trim($data['s_name']))[0]; ?>
                        <h1 class="h5 mb-0 text-gray-800 font-weight-bold mr-3">Welcome, <?php echo $firstName ?></h1>
                        <span class="executive-badge d-none d-md-inline-block">
                            <i class="fas fa-shield-alt mr-1"></i> Executive Portal
                        </span>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center mr-2">
                            <span class="branch-pill">
                                <i class="fas fa-map-marker-alt mr-1"></i> <?php echo $data['branch_name']; ?>
                            </span>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-3 d-none d-lg-inline text-gray-700 font-weight-bold"><?php echo $data['s_name'] ?></span>
                                <img class="img-profile rounded-circle"
                                    src="../../user_img/<?php echo $data['image'] ?>" width="35" height="35">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="userDropdown">
                                <div class="dropdown-header text-center font-weight-bold text-info">Account Actions</div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                                    Logout Session
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">
                    