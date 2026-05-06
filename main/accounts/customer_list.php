<?php 
include('ini/header.php');
include('dbcon.php');

// Logic for Top Summary Card
$sum_query = mysqli_query($con, "SELECT SUM(due_amount) as total_dues, COUNT(cus_id) as total_customers FROM customer");
$summary = mysqli_fetch_assoc($sum_query);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<style>
    /* Styling to match Master Sales Design */
    .table thead th {
        background-color: #f8f9fc;
        color: #4e73df;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .table-hover tbody tr:hover { background-color: #f8f9fc; transition: 0.3s; }
    .dt-buttons { margin-bottom: 15px; }
    .dt-buttons .btn { margin-right: 5px; border-radius: 5px; font-weight: bold; }
    .badge { padding: 0.5em 0.75em; }
    
    /* Pagination Styling to match Bootstrap 4 */
    .dataTables_paginate .pagination .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    .dataTables_info, .dataTables_paginate {
        padding-top: 15px;
        font-size: 0.85rem;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer Management</h1>
        <div>
            <a href="#" class="btn btn-sm btn-success shadow-sm" onclick="window.print()">
                <i class="fas fa-print fa-sm text-white-50"></i> Print View
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $summary['total_customers']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Due</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($summary['total_dues'], 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Registered Customers Directory</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center" id="masterSalesTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer Details</th>
                            <th>Contact Info</th>
                            <th>Credit Limit</th>
                            <th>Due</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query = "SELECT * FROM customer ORDER BY cus_id DESC";
                        $run = mysqli_query($con, $query);
                        while($row = mysqli_fetch_assoc($run)) {
                            $isDue = ($row['due_amount'] > 0);
                        ?>
                            <tr>
                                <td><?php echo $row['cus_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                    <small class="text-primary"><?php echo htmlspecialchars($row['customer_code']); ?></small>
                                </td>
                                <td>
                                    <div class="small"><i class="fas fa-phone fa-xs"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                                    <div class="small text-muted"><i class="fas fa-envelope fa-xs"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($row['ledger']); ?></td>
                                <td class="<?php echo $isDue ? 'text-danger font-weight-bold' : 'text-success'; ?>">
                                    <?php echo number_format($row['due_amount'], 2); ?>
                                </td>
                                <td class="small text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_customer.php?id=<?php echo $row['cus_id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="delete_customer.php?id=<?php echo $row['cus_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete customer?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="4" class="text-right">Total Due:</th>
                            <th class="text-danger"><?php echo number_format($summary['total_dues'], 2); ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#masterSalesTable').DataTable({
            // 'l' = Length changing, 'B' = Buttons, 'f' = filtering, 'r' = processing, 't' = table, 'i' = info, 'p' = pagination
            dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "order": [[ 0, 'desc' ]],
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            buttons: [
                { 
                    extend: 'excelHtml5', 
                    footer: true, 
                    title: 'Customer_Management_Report',
                    className: 'btn btn-success btn-sm'
                },
                { 
                    extend: 'pdfHtml5', 
                    footer: true, 
                    title: 'Customer_Management_Report', 
                    orientation: 'landscape',
                    className: 'btn btn-danger btn-sm'
                },
                { 
                    extend: 'print', 
                    footer: true,
                    className: 'btn btn-info btn-sm'
                }
            ],
            language: {
                paginate: {
                    previous: "<i class='fas fa-angle-left'>",
                    next: "<i class='fas fa-angle-right'>"
                }
            }
        });
    });
</script>

<?php include('ini/footer.php'); ?>