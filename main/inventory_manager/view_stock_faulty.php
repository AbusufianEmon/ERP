<?php
include('ini/header.php');
include('dbcon.php');

// Fetch stock faulty data with related info - Including loss_amount
$sql = "
    SELECT sfi.*, 
           p.name AS product_name, 
           c.cat_name, 
           sup.sup_name
    FROM stock_faulty_items sfi
    LEFT JOIN product p ON sfi.product_id = p.id
    LEFT JOIN category c ON sfi.cat_id = c.cat_id
    LEFT JOIN supplier sup ON sfi.supplier_id = sup.id
    ORDER BY sfi.id DESC
";
$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Stock Faulty Records</h6>
            <a href="create_stock_faulty.php" class="btn btn-sm btn-success">+ Add New Faulty</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" id="example" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Stock Faulty ID</th>
                            <th>Product Name</th>
                            <th>Supplier Name</th> <th>Lot No</th>
                            <th>Qty</th>
                            <th>Buy Price</th>
                            <th>Adjustable</th>
                            <th>Loss Amount</th> 
                            <th>Faulty Photo</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($data = mysqli_fetch_assoc($run)) { ?>
                            <tr>
                                <td class="font-weight-bold"><?php echo $data['stock_faulty_id']; ?></td>
                                <td><?php echo $data['product_name']; ?></td>
                                <td><?php echo $data['sup_name']; ?></td> <td><?php echo $data['lot_no']; ?></td>
                                <td><?php echo $data['qty']; ?></td>
                                <td><?php echo number_format($data['buy_price'], 2); ?></td>
                                <td><?php echo number_format($data['adjustable_amount'], 2); ?></td>
                                <td class="text-danger font-weight-bold">
                                    <?php echo number_format($data['loss_amount'], 2); ?>
                                </td>
                                <td>
                                    <?php if (!empty($data['faulty_photo'])) { ?>
                                        <img src="img/faulty/<?php echo $data['faulty_photo']; ?>" 
                                             alt="Faulty Photo" width="50" height="50" 
                                             style="object-fit:cover; border-radius:5px; border:1px solid #ddd;">
                                    <?php } else { ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">No Photo</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($data['status'] == 0) { ?>
                                        <span class="badge badge-danger">Pending</span>
                                    <?php } else { ?>
                                        <span class="badge badge-success">Returned</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($data['created_at'])); ?></td>
                                <td>
                                    <a href="view_faulty_details.php?id=<?php echo $data['stock_faulty_id']; ?>" 
                                       class="btn btn-info btn-sm">
                                       <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-right">Total Loss:</th>
                            <th id="total_loss_display" class="text-danger"></th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.dataTables.min.css">

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'print'],
            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api();
                // Remove formatting to get integer data for summation
                var intVal = function ( i ) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
                };
 
                // Total over all pages - Loss amount is now column index 7 (starting from 0)
                totalLoss = api.column( 7 ).data().reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
 
                // Update footer
                $( api.column( 7 ).footer() ).html(totalLoss.toFixed(2));
            }
        });
    });
</script>

<?php include('ini/footer.php'); ?>