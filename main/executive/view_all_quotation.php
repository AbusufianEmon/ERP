<?php
include('ini/header.php');
include('dbcon.php');

/* ===============================
    Get and Sanitize Branch ID
================================ */
if (!isset($_GET['branch_id'])) {
    die("<div class='alert alert-danger'>Error: Branch ID is missing in the URL.</div>");
}
$branch_id = intval($_GET['branch_id']);

/* ===============================
    Fetch Branch Name for Header
================================ */
$branch_query = mysqli_query($con, "SELECT branch_name FROM branches WHERE branch_id = $branch_id");
$branch_info = mysqli_fetch_assoc($branch_query);
$current_branch_name = $branch_info['branch_name'] ?? "Unknown Branch";

/* ===============================
    Fetch Quotation Rows for Specific Branch
================================ */
$sql = "
SELECT 
    cq.corporate_quotation_id,
    cq.corporate_quotation_invoice_id,
    cq.product_name,
    cq.qty,
    cq.offer_price,
    cq.manager_approvel_status,
    cc.corporate_name,
    cc.corporate_code,
    b.branch_name,

    (
        SELECT SUM(q.qty * q.offer_price)
        FROM corporate_quotation q
        WHERE q.corporate_quotation_invoice_id = cq.corporate_quotation_invoice_id
    ) AS total_offer_amount

FROM corporate_quotation cq
LEFT JOIN corporate_customer cc 
    ON cq.corporate_id = cc.corporate_id
LEFT JOIN branches b 
    ON cq.branch_id = b.branch_id

WHERE cq.branch_id = $branch_id

ORDER BY 
    cq.corporate_quotation_invoice_id DESC, 
    cq.corporate_quotation_id ASC
";

$run = mysqli_query($con, $sql);

if (!$run) {
    die("Query Failed: " . mysqli_error($con));
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quotations for: <span class="text-primary"><?= htmlspecialchars($current_branch_name); ?></span></h1>
        <a href="corporate_quotation_add.php?branch_id=<?= $branch_id; ?>" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Quotation
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                Corporate Quotation List (Filtered by Branch)
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center" id="example" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Branch</th>
                            <th>Corporate</th>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Offer Price</th>
                            <th>Line Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($run)) { 
                            $line_total = $row['qty'] * $row['offer_price'];
                        ?>
                        <tr>
                            <td><small class="font-weight-bold"><?= $row['corporate_quotation_invoice_id']; ?></small></td>
                            
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($row['branch_name'] ?? 'N/A'); ?></span></td>

                            <td><?= htmlspecialchars($row['corporate_name']); ?></td>
                            <td><?= htmlspecialchars($row['corporate_code']); ?></td>

                            <td><?= htmlspecialchars($row['product_name']); ?></td>

                            <td><?= (int)$row['qty']; ?></td>

                            <td><?= number_format($row['offer_price'], 2); ?></td>

                            <td class="font-weight-bold"><?= number_format($line_total, 2); ?></td>

                            <td>
                                <?php if ($row['manager_approvel_status'] == 1) { ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php } else { ?>
                                    <span class="badge badge-warning text-dark">Pending</span>
                                <?php } ?>
                            </td>

                            <td>
                                <a href="corporate_quotation_view.php?invoice_id=<?= $row['corporate_quotation_invoice_id']; ?>&branch_id=<?= $branch_id; ?>"
                                   class="btn btn-info btn-sm">
                                   <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Check if DataTable is already initialized to prevent errors
    if ( ! $.fn.DataTable.isDataTable( '#example' ) ) {
        $('#example').DataTable({
            pageLength: 25,
            order: [], // Keeps the SQL sort order
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', className: 'btn btn-sm btn-dark' },
                { extend: 'csv', className: 'btn btn-sm btn-dark' },
                { extend: 'print', className: 'btn btn-sm btn-dark' }
            ]
        });
    }
});
</script>

<?php include('ini/footer.php'); ?>