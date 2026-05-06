<?php 
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 3) {
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
    <title>Inventory Management | Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.9/css/buttons.dataTables.min.css">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="js/sweetalert.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.colVis.min.js"></script>

    <style>
        /* Modern Sidebar Styling */
        .sidebar {
            background: #1a1c1e !important; /* Deep Dark Background */
            background-image: linear-gradient(180deg, #1a1c1e 10%, #0c3d21 100%) !important;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            background: rgba(0,0,0,0.2);
            padding: 1.5rem 1rem !important;
            height: auto !important;
        }

        .nav-item .nav-link {
            padding: 0.8rem 1.2rem !important;
            transition: all 0.3s ease;
            font-weight: 500;
            color: rgba(255,255,255,0.75) !important;
            border-left: 3px solid transparent;
        }

        .nav-item .nav-link i {
            margin-right: 10px;
            font-size: 0.9rem;
            color: #2ecc71; /* Emerald Green Icons */
        }

        .nav-item .nav-link:hover {
            background: rgba(46, 204, 113, 0.1);
            color: #fff !important;
            border-left: 3px solid #2ecc71;
        }

        .nav-item.active .nav-link {
            background: rgba(46, 204, 113, 0.15) !important;
            color: #fff !important;
            font-weight: 700 !important;
            border-left: 3px solid #2ecc71;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.08) !important;
            margin: 0.5rem 1rem !important;
        }

        .sidebar-heading {
            color: rgba(255,255,255,0.3) !important;
            font-size: 0.65rem !important;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding-top: 1rem;
        }

        /* Topbar Enhancements */
        .topbar {
            box-shadow: 0 4px 12px 0 rgba(0,0,0,0.05) !important;
        }

        .branch-badge {
            background: #e8f5e9;
            color: #1b5e20;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
            border: 1px solid #c8e6c9;
        }

        .img-profile {
            border: 2px solid #2ecc71;
            padding: 2px;
            object-fit: cover;
        }

        .logout-link {
            background: #fff0f0;
            color: #d32f2f;
            padding: 6px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            transition: 0.3s;
            text-decoration: none !important;
        }

        .logout-link:hover {
            background: #d32f2f;
            color: white;
        }

        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: #2ecc71; border-radius: 10px; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-boxes text-success"></i>
                </div>
                <div class="sidebar-brand-text mx-3">MTE ERP</div>
            </a>

            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard Overview</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Stock Management</div>

            <li class="nav-item">
                <a class="nav-link" href="view_stock.php?branch_id=<?php echo $data['branch_id']; ?>">
                    <i class="fas fa-warehouse"></i>
                    <span>Branch Stock</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="total_stock.php">
                    <i class="fas fa-globe"></i>
                    <span>All Branch Stock</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Customer Hub</div>

            <li class="nav-item">
                <a class="nav-link" href="add_customer.php">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Customer</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="view_customer.php">
                    <i class="fas fa-users-cog"></i>
                    <span>Customer List</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="add_corporate_customer.php">
                    <i class="fas fa-building"></i>
                    <span>Add Corporate</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="all_corporate.php">
                    <i class="fas fa-city"></i>
                    <span>Corporate List</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Sales & Approvals</div>

            <li class="nav-item">
                <a class="nav-link" href="direct_sales_approval.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-check-double"></i>
                    <span>Sales Approval</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_all_quotation.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Quotation Lists</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Logistics</div>

            <li class="nav-item">
                <a class="nav-link" href="request_product.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Product Request</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="transfer_recieve_pending.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-clock"></i>
                    <span>Receive Pending</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="from_branch_product_request.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-reply-all"></i>
                    <span>Branch Requests</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="transfer_report.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-chart-area"></i>
                    <span>Transfer Reports</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Financials</div>

            <li class="nav-item">
                <a class="nav-link" href="add_expenses.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-coins"></i>
                    <span>Add Expenses</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="expense_report.php?branch_id=<?php echo $data['branch_id'] ?>">
                    <i class="fas fa-file-medical-alt"></i>
                    <span>Expense Reports</span>
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
                        <i class="fa fa-bars text-success"></i>
                    </button>

                    <div class="ml-2 d-none d-sm-block">
                        <span class="branch-badge">
                            <i class="fas fa-store-alt mr-1"></i> <?php echo $data['branch_name'] ?> Branch
                        </span>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="d-flex flex-column text-right mr-2">
                                    <span class="text-gray-800 small font-weight-bold"><?php echo $data['s_name'] ?></span>
                                    <span class="text-gray-500" style="font-size: 0.65rem;">Manager ID: #<?php echo $id; ?></span>
                                </div>
                                <img class="img-profile rounded-circle" width="35" height="35"
                                    src="../../user_img/<?php echo $data['image'] ?>">
                            </a>
                        </li>
                        
                        <li class="nav-item d-flex align-items-center ml-3">
                             <a href="logout.php" class="logout-link">
                                <i class="fas fa-power-off mr-1"></i> Logout
                             </a>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">Hello, <?php echo $data['s_name']; ?></h1>
                            <p class="text-success small font-weight-bold mb-0">Authorized Branch Manager Account</p>
                        </div>
                    </div>