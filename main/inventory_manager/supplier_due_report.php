<?php
include('ini/header.php');
include('dbcon.php');

// Get selected supplier ID from the form submission
$selected_supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';

// Calculate the total due amount
$sql_due = "SELECT SUM(due_amount) AS total_due FROM sup_invoice";
if (!empty($selected_supplier_id)) {
    $sql_due .= " WHERE supp_id = '$selected_supplier_id' AND due_amount > 0";
} else {
    $sql_due .= " WHERE due_amount > 0";
}
$run_due = mysqli_query($con, $sql_due);
$total_due = mysqli_fetch_assoc($run_due)['total_due'];

// Fetch supplier details along with due amount where due amount is greater than 0
$sql = "SELECT sup_invoice.invoice_id AS invoice_id, sup_invoice.product_name, product.code, sup_invoice.paid_amount, sup_invoice.due_amount, sup_invoice.qty, supplier.sup_name
        FROM sup_invoice
        LEFT JOIN product ON sup_invoice.product_id = product.id
        LEFT JOIN supplier ON sup_invoice.supp_id = supplier.id
        WHERE sup_invoice.due_amount > 0";
if (!empty($selected_supplier_id)) {
    $sql .= " AND supplier.id = '$selected_supplier_id'";
}
$run = mysqli_query($con, $sql);

// Fetch all suppliers for the select dropdown
$sql_suppliers = "SELECT * FROM supplier";
$run_suppliers = mysqli_query($con, $sql_suppliers);
?>
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Supplier Invoice</h6>
        </div>
        <div class="card-body">
            <!-- Supplier filter form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="supplier_id">Select Supplier:</label>
                    <select name="supplier_id" id="supplier_id" class="form-control">
                        <option value="">All Suppliers</option>
                        <?php while ($supplier = mysqli_fetch_assoc($run_suppliers)) { ?>
                            <option value="<?php echo $supplier['id']; ?>" <?php if ($selected_supplier_id == $supplier['id']) echo 'selected'; ?>>
                                <?php echo $supplier['sup_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>

            <div class="table-responsive mt-3">
                <table id="example" class="display nowrap" style="width:100%">
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
                                <td><?php echo $data['invoice_id'] ?></td>
                                <td><?php echo $data['product_name'] ?></td>
                                <td><?php echo $data['code'] ?></td>
                                <td><?php echo $data['paid_amount'] ?></td>
                                <td><?php echo $data['due_amount'] ?></td>
                                <td><?php echo $data['qty'] ?></td>
                                <td><?php echo $data['sup_name'] ?></td>
                                <td>
                                    <a href="supplier_invoice.php?invoice_id=<?php echo $data['invoice_id']; ?>" class="btn btn-info btn-sm">PDF</a><br><br>
                                    <a href="update_sup_inv.php?invoice_id=<?php echo $data['invoice_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="alert alert-info mt-3">
                    <strong>Total Due Amount: </strong> <span class="text-danger font-weight-bold">$<?php echo number_format($total_due, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>
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
            buttons: [
                'copy', 'csv', 'print'
            ]
        });
    });
</script>
