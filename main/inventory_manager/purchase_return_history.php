<?php
include('ini/header.php');
include('dbcon.php');

// Fetch purchase return data with product and supplier info
$sql = "
    SELECT pr.lot_no, pr.invoice_no, pr.qty, pr.buy_price, pr.total, pr.reason, pr.return_date,
           p.name AS product_name, p.code AS product_code,
           s.sup_name AS supplier_name
    FROM purchase_return pr
    LEFT JOIN product p ON pr.product_id = p.id
    LEFT JOIN supplier s ON pr.supplier_id = s.id
    ORDER BY pr.return_date DESC
";
$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Purchase Return History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Lot No</th>
                            <th>Invoice No</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Supplier Name</th>
                            <th>Qty</th>
                            <th>Buy Price</th>
                            <th>Total</th>
                            <th>Reason</th>
                            <th>Return Date</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Lot No</th>
                            <th>Invoice No</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Supplier Name</th>
                            <th>Qty</th>
                            <th>Buy Price</th>
                            <th>Total</th>
                            <th>Reason</th>
                            <th>Return Date</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($run) > 0) {
                            while ($data = mysqli_fetch_assoc($run)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($data['lot_no']); ?></td>
                                    <td><?php echo htmlspecialchars($data['invoice_no']); ?></td>
                                    <td><?php echo htmlspecialchars($data['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($data['product_code']); ?></td>
                                    <td><?php echo htmlspecialchars($data['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars($data['qty']); ?></td>
                                    <td><?php echo number_format($data['buy_price'], 2); ?></td>
                                    <td><?php echo number_format($data['total'], 2); ?></td>
                                    <td class="text-danger fw-bold"><?php echo htmlspecialchars($data['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($data['return_date']); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="10" class="text-muted text-center">No purchase return records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables and Buttons -->
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
            buttons: ['copy', 'csv', 'print']
        });
    });
</script>

<?php include('ini/footer.php'); ?>
