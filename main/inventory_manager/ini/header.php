<?php 
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 2) {
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
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Inventory Management</title>

    <!-- Bootstrap CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet" />

    <!-- Custom styles -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.9/css/buttons.bootstrap4.min.css" rel="stylesheet" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Font Awesome (old version) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

    <!-- SweetAlert -->
    <script src="js/sweetalert.min.js"></script>

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- DataTables JS -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.2.9/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.9/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar"
            style="background: linear-gradient(180deg, #006400, #00a000);">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3">Inventory Management</div>
            </a>

            <hr class="sidebar-divider my-0" />

            <!-- Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider" />

            <!-- Products -->
            <li class="nav-item">
                <a class="nav-link" href="all_product.php">
                    <i class="fas fa-box"></i>
                    <span>All Products</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="create_product.php">
                    <i class="fas fa-plus-square"></i>
                    <span>Create Product</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="add_category.php">
                    <i class="fas fa-tags"></i>
                    <span>Add Product Category</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="all_category.php">
                    <i class="fas fa-th-list"></i>
                    <span>Manage Product Category</span>
                </a>
            </li>

            <hr class="sidebar-divider" />

            <!-- Supplier -->
            <li class="nav-item">
                <a class="nav-link" href="sup_invoice.php">
                    <i class="fas fa-file-invoice"></i>
                    <span>Supplier Invoice</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="add_sup.php">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Supplier</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="view_sup.php">
                    <i class="fas fa-users-cog"></i>
                    <span>Manage Supplier</span>
                </a>
            </li>

            <hr class="sidebar-divider" />

            <!-- Stock Management -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#stockMenu"
                    aria-expanded="false" aria-controls="stockMenu">
                    <i class="fas fa-warehouse"></i>
                    <span>Stock Management</span>
                </a>

                <div id="stockMenu" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded">
                        <a class="collapse-item text-warning font-weight-bold" href="add_purchase_order.php">Purchase Order</a>
                        <a class="collapse-item text-warning font-weight-bold" href="purchase_receive.php">Purchase Receive</a>
                        <a class="collapse-item text-warning font-weight-bold" href="purchase_history.php">Purchase History</a>
                        <a class="collapse-item text-warning font-weight-bold" href="purchase_return_history.php">Purchase Return History</a>
                        <a class="collapse-item text-warning font-weight-bold" href="view_stock.php">View Stock</a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider d-none d-md-block" />

             <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#fstock"
                    aria-expanded="false" aria-controls="fstock">
                    <i class="fas fa-warehouse"></i>
                    <span>Faulty Stock</span>

                </a>


                <div id="fstock" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded">
                        <a class="collapse-item text-warning font-weight-bold" href="view_stock_faulty.php">All Faulty Stock</a>
                        <a class="collapse-item text-warning font-weight-bold" href="create_stock_faulty.php">Create</a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider d-none d-md-block" />
             <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#ptransfer"
                    aria-expanded="false" aria-controls="ptransfer">
                    <i class="fas fa-warehouse"></i>
                    <span>Inter Branch MRR</span>

                </a>


                <div id="ptransfer" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded">
                        <a class="collapse-item text-warning font-weight-bold" href="transfer_form.php">Transfer</a>
                        <a class="collapse-item text-warning font-weight-bold" href="from_branch_product_request.php?branch_id=<?php echo $data['branch_id'] ?>">Branch Product Request</a>
                        <a class="collapse-item text-warning font-weight-bold" href="transfer_approval_pending.php">Transfer Approval Pending</a>
                        <a class="collapse-item text-warning font-weight-bold" href="all_transfer.php">All Transfer</a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider">
              <li class="nav-item">
                    <a class="nav-link collapsed" href="supplier_due_pending.php">
                    <i class="fas fa-fw fa-cog"></i>

                    <span>Supplier Due Pending</span>
                </a>
                 </li>
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- JS fix for dropdown toggle -->
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const collapseLinks = document.querySelectorAll('[data-toggle="collapse"]');
            collapseLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const target = document.querySelector(link.getAttribute('data-target'));
                    if (target.classList.contains('show')) {
                        $(target).collapse('hide');
                    } else {
                        $(target).collapse('show');
                    }
                    e.preventDefault();
                });
            });

            // Highlight current page in sidebar
            const currentUrl = window.location.href;
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.href === currentUrl) {
                    link.classList.add('active');
                }
            });
        });
        </script>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <b><?php echo $data['branch_name']; ?> Branch</b>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo htmlspecialchars($data['s_name']); ?>
                                </span>
                                <img class="img-profile rounded-circle"
                                    src="../../user_img/<?php echo htmlspecialchars($data['image']); ?>" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>