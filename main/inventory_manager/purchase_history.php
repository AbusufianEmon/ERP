<?php
include('ini/header.php');
include('dbcon.php');

// Quick stats for the top bar
$stats_sql = "SELECT 
    SUM(CASE WHEN status != 2 THEN qty * buy_price ELSE 0 END) as total_purchase,
    SUM(CASE WHEN status = 2 THEN qty * buy_price ELSE 0 END) as total_returned
    FROM purchase_order";
$stats_res = mysqli_query($con, $stats_sql);
$stats = mysqli_fetch_assoc($stats_res);
?>

<div class="container-fluid mt-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="text-dark fw-bold mb-0">All Purchase Orders </h2>
            <p class="text-muted">A comprehensive audit log of every item ordered.</p>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm py-2">
                <div class="card-body py-1">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Net Purchase</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($stats['total_purchase'], 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow-sm py-2">
                <div class="card-body py-1">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Returned</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($stats['total_returned'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-list me-2"></i> Transaction History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="detailedTable" width="100%">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="border-0">Date</th>
                            <th class="border-0">Reference</th>
                            <th class="border-0">Product Details</th>
                            <th class="border-0">Supplier</th>
                            <th class="border-0 text-center">Qty</th>
                            <th class="border-0 text-end">Buy Price</th>
                            <th class="border-0 text-end">Total</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT po.*, p.name AS product_name, p.code AS p_code, s.sup_name 
                                FROM purchase_order po
                                LEFT JOIN product p ON po.product_id = p.id
                                LEFT JOIN supplier s ON po.supplier_id = s.id
                                ORDER BY po.created_at DESC";

                        $res = mysqli_query($con, $sql);

                        if ($res && mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_assoc($res)) {
                                $subtotal = $row['qty'] * $row['buy_price'];
                                
                                // Beautiful Status Badge Logic
                                if ($row['status'] == 2) {
                                    $status_badge = '<span class="badge rounded-pill bg-light text-danger border border-danger"><i class="fa fa-undo me-1"></i> Returned</span>';
                                    $tr_style = 'style="background-color: #fff5f5;"'; 
                                } elseif ($row['status'] == 1) {
                                    $status_badge = '<span class="badge rounded-pill bg-success shadow-sm"><i class="fa fa-check-circle me-1"></i> Received</span>';
                                    $tr_style = '';
                                } else {
                                    $status_badge = '<span class="badge rounded-pill bg-warning text-dark shadow-sm"><i class="fa fa-clock me-1"></i> Pending</span>';
                                    $tr_style = '';
                                }
                                ?>
                                <tr <?php echo $tr_style; ?>>
                                    <td class="small text-muted"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="fw-bold text-dark">Inv: <?php echo htmlspecialchars($row['invoice_no']); ?></div>
                                        <small class="text-primary">Lot: #<?php echo htmlspecialchars($row['lot_no']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                        <code class="small"><?php echo htmlspecialchars($row['p_code']); ?></code>
                                    </td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($row['sup_name']); ?></td>
                                    <td class="text-center fw-bold"><?php echo $row['qty']; ?></td>
                                    <td class="text-end"><?php echo number_format($row['buy_price'], 2); ?></td>
                                    <td class="text-end fw-bold text-dark"><?php echo number_format($subtotal, 2); ?></td>
                                    <td class="text-center"><?php echo $status_badge; ?></td>
                                    <td class="text-center">
                                        <a href="view_received_lot.php?invoice_no=<?php echo urlencode($row['invoice_no']); ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-success { border-left: 4px solid #1cc88a !important; }
    .border-left-danger { border-left: 4px solid #e74a3b !important; }
    #detailedTable thead th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
    .badge { padding: 0.5em 0.8em; }
    .table-hover tbody tr:hover { background-color: #f8f9fc; transition: 0.3s; }
    .btn-outline-primary:hover { color: white; }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#detailedTable').DataTable({
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-sm btn-success rounded-pill px-3 me-2', text: '<i class="fa fa-file-excel"></i> Excel' },
            { extend: 'print', className: 'btn btn-sm btn-primary rounded-pill px-3', text: '<i class="fa fa-print"></i> Print' }
        ],
        "pageLength": 15,
        "language": { "search": "", "searchPlaceholder": "Search ledger..." }
    });
});
</script>

<?php include('ini/footer.php'); ?>