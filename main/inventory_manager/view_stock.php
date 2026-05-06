<?php
include('ini/header.php');
include('dbcon.php');

// Fetch product stock
$sql = "
SELECT 
    ps.product_stock_id, ps.stock_id, ps.product_id, ps.supplier_id, ps.cat_id, ps.branch_id,
    ps.qty, ps.lot_no, po.invoice_no, ps.buy_price, ps.sell_price, ps.created_at,
    p.name AS product_name, p.code AS product_code, p.photo,
    s.sup_name, c.cat_name, b.branch_name
FROM product_stock ps
LEFT JOIN product p ON ps.product_id = p.id
LEFT JOIN supplier s ON ps.supplier_id = s.id
LEFT JOIN category c ON ps.cat_id = c.cat_id
LEFT JOIN branches b ON ps.branch_id = b.branch_id
LEFT JOIN purchase_order po ON ps.stock_id = po.stock_id
ORDER BY p.name ASC
";
$result = mysqli_query($con, $sql);

// Pre-calculate totals
$totals = [];
$total_sql = "SELECT product_id, SUM(qty) AS total_qty FROM product_stock GROUP BY product_id";
$total_res = mysqli_query($con, $total_sql);
while ($row = mysqli_fetch_assoc($total_res)) {
    $totals[$row['product_id']] = $row['total_qty'];
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.5.5/css/colReorder.bootstrap4.min.css">

<style>
    body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
    
    /* Layout Fixes */
    .table-card { border-radius: 15px; border: none; overflow: hidden; }
    
    /* Force Single Line */
    #example td, #example th { 
        white-space: nowrap; 
        vertical-align: middle !important;
        padding: 10px 15px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Resizable Header Handle styling */
    .JRCnt { height: 40px !important; } 

    /* Table styling */
    .table thead th {
        background-color: #f8f9fc;
        color: #4e73df;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e3e6f0;
    }
    
    .table-hover tbody tr:hover { background-color: #f1f3f9 !important; }

    /* Badge & Alert Styles */
    .badge-pill { font-weight: 500; padding: 5px 12px; }
    .branch-alert { background-color: #fff5f5 !important; color: #e74a3b !important; }
    .prod-img { border-radius: 6px; border: 1px solid #eaecf0; object-fit: cover; }

    /* Action Buttons */
    .btn-circle { width: 30px; height: 30px; padding: 6px 0; border-radius: 15px; text-align: center; font-size: 12px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Live Inventory Stream</h1>
        <a href="transfer_form.php" class="btn btn-primary btn-sm px-4 shadow-sm" style="border-radius: 8px;">
            <i class="fas fa-sync-alt mr-2"></i> Stock Transfer
        </a>
    </div>

    <div class="card shadow-sm table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="example" width="100%">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Product Name</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Branch</th>
                            <th>Lot No</th>
                            <th>Invoice</th>
                            <th>Qty</th>
                            <th>Buy Price</th>
                            <th>Sell Price</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['product_id'];
                            $sid = $row['product_stock_id'];
                            $bid = (int)$row['branch_id'];
                            $qty = (float)$row['qty'];
                            
                            $row_class = ($bid === 4 && $qty < 10) ? "branch-alert" : "";
                            $overall_qty = $totals[$product_id] ?? 0;
                            $qty_class = ($overall_qty < 10) ? "text-danger font-weight-bold" : "text-success";
                        ?>
                            <tr class="<?php echo $row_class; ?>" id="stock_row_<?php echo $sid; ?>">
                                <td>
                                    <img src="<?php echo !empty($row['photo']) ? 'img/products/'.$row['photo'] : 'img/no-image.png'; ?>" width="35" height="35" class="prod-img">
                                </td>
                                <td class="font-weight-bold"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><code class="text-primary small"><?php echo htmlspecialchars($row['product_code']); ?></code></td>
                                <td><span class="badge badge-light border"><?php echo htmlspecialchars($row['cat_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['sup_name']); ?></td>
                                <td><span class="text-info"><?php echo htmlspecialchars($row['branch_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['lot_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['invoice_no'] ?? '-'); ?></td>
                                <td class="<?php echo $qty_class; ?>"><?php echo $row['qty']; ?></td>
                                <td>$<?php echo number_format($row['buy_price'], 2); ?></td>
                                <td class="font-weight-bold">$<?php echo number_format($row['sell_price'], 2); ?></td>
                                <td class="small"><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if ($bid === 4 && $qty == 0) { ?>
                                        <button class="btn btn-outline-danger btn-circle delete-stock" data-id="<?php echo $sid; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php } else { echo '<i class="fas fa-lock text-muted small"></i>'; } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/colreorder/1.5.5/js/dataTables.colReorder.min.js"></script>
<script src="https://rawgit.com/alvaro-prieto/colResizable/master/source/colResizable-1.6.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#example').DataTable({
        colReorder: true,
        "pageLength": 15,
        dom: "<'row px-3 pt-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row px-3 pb-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            { extend: 'copy', className: 'btn btn-outline-secondary btn-sm' },
            { extend: 'csv', className: 'btn btn-outline-info btn-sm' },
            { extend: 'print', className: 'btn btn-outline-primary btn-sm' }
        ],
        "fnDrawCallback": function() {
            // Apply Resizable after table is drawn
            $("#example").colResizable({
                liveDrag: true,
                gripInnerHtml: "<div class='grip'></div>",
                draggingClass: "dragging",
                resizeMode: 'fit'
            });
        }
    });

    // Delete Logic
    $(document).on('click', '.delete-stock', function() {
        var stockId = $(this).data('id');
        var rowElement = $(this).closest('tr');

        swal({
            title: "Delete item?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.post('delete_stock_item.php', { id: stockId }, function(response) {
                    if (response.trim() == 'success') {
                        table.row(rowElement).remove().draw(false);
                        swal("Deleted!", { icon: "success" });
                    }
                });
            }
        });
    });
});
</script>

<?php include('ini/footer.php'); ?>