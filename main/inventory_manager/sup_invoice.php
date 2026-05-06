<?php
include('ini/header.php');
include('dbcon.php');

$sql = "SELECT sup_invoice.invoice_no, sup_invoice.product_name, product.code, sup_invoice.paid_amount, sup_invoice.due_amount, sup_invoice.qty, supplier.sup_name
        FROM sup_invoice
        LEFT JOIN product ON sup_invoice.product_id = product.id
        LEFT JOIN supplier ON sup_invoice.supp_id = supplier.id";
$run = mysqli_query($con, $sql);
?>
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Supplier Invoice</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>QTY</th>
                            <th>Supplier Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Invoice No</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>QTY</th>
                            <th>Supplier Name</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php while ($data = mysqli_fetch_assoc($run)) { ?>
                            <tr>
                                <td><?php echo $data['invoice_no']; ?></td>
                                <td><?php echo $data['product_name']; ?></td>
                                <td><?php echo $data['code']; ?></td>
                                <td><?php echo $data['paid_amount']; ?></td>
                                <td><?php echo $data['due_amount']; ?></td>
                                <td><?php echo $data['qty']; ?></td>
                                <td><?php echo $data['sup_name']; ?></td>
                                <td>
                                    <a href="supplier_invoice.php?invoice_no=<?php echo $data['invoice_no']; ?>" class="btn btn-info btn-sm">PDF</a><br><br>
                                    <a href="update_sup_inv.php?invoice_no=<?php echo $data['invoice_no']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>
                                    <a href="delete_sup_invoice.php?invoice_no=<?php echo $data['invoice_no']; ?>" class="btn btn-danger btn-sm"><i class="fa fa-close"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JS & CSS -->
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
