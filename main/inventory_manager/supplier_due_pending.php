<?php
include('ini/header.php'); 
include('dbcon.php');

// Safely get branch_id from the session data
$branch_id = isset($data['branch_id']) ? intval($data['branch_id']) : 0;
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Supplier Due Pending</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pending Dues List (Excluding Returns)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="dueTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Supplier</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Total Price</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        /* SQL Fix: Added po.qty to ensures it is available for calculation.
                           The WHERE clause now filters for records where (Price * Qty) > Paid.
                        */
                        $sql = "SELECT po.*, s.sup_name, p.name as product_name 
                                FROM purchase_order po
                                LEFT JOIN supplier s ON po.supplier_id = s.id 
                                LEFT JOIN product p ON po.product_id = p.id
                                WHERE po.status != 2 
                                AND ((po.buy_price * po.qty) - po.paid_amount) > 0
                                ";
                        
                        $result = mysqli_query($con, $sql);

                        if($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Logic: (Unit Price * Quantity) - Total Paid = Due
                                $total_price = $row['buy_price'] * $row['qty'];
                                $due = $total_price - $row['paid_amount'];
                                
                                $status_badge = '';
                                if ($row['status'] == 0) {
                                    $status_badge = '<span class="badge badge-warning">Receive Pending</span>';
                                } else if ($row['status'] == 1) {
                                    $status_badge = '<span class="badge badge-success">Received</span>';
                                }
                                ?>
                                <tr>
                                    <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                                    <td><b><?php echo $row['invoice_no']; ?></b></td>
                                    <td><?php echo htmlspecialchars($row['sup_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo $row['qty']; ?></td>
                                    <td><?php echo $status_badge; ?></td>
                                    <td><?php echo number_format($total_price, 2); ?></td>
                                    <td class="text-success"><?php echo number_format($row['paid_amount'], 2); ?></td>
                                    <td class="text-danger font-weight-bold"><?php echo number_format($due, 2); ?></td>
                                <?php 
                            }
                        } else {
                            echo "<tr><td colspan='9'>No pending dues found.</td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <th colspan="8" class="text-right">Total Outstanding Due:</th>
                            <th id="totalDue" class="text-danger"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#dueTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm mb-3',
                title: 'Supplier Pending Dues Report',
                exportOptions: { columns: [0, 1, 2, 3, 4, 6, 7, 8] } // Adjusted to match visible columns
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-primary btn-sm mb-3',
                exportOptions: { columns: [0, 1, 2, 3, 4, 6, 7, 8] }
            }
        ],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();
            
            var intVal = function ( i ) {
                return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
            };
 
            // Summing the Due Amount column (index 8)
            var pageTotal = api
                .column( 8, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            $( api.column( 8 ).footer() ).html(
                pageTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
            );
        }
    });
});
</script>

<?php include('ini/footer.php'); ?>