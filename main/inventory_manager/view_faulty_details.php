<?php
include('ini/header.php');
include('dbcon.php');

// 1. Get the Stock Faulty ID from URL
if (!isset($_GET['id'])) {
    echo "<script>window.location.href='view_stock_faulty.php';</script>";
    exit;
}

$sf_id = mysqli_real_escape_string($con, $_GET['id']);

// 2. Handle Status Update (Mark as Returned)
if (isset($_POST['update_status'])) {
    $update_query = "UPDATE stock_faulty_items SET status = 1 WHERE stock_faulty_id = '$sf_id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>
                swal('Success!', 'All items under this ID marked as Returned!', 'success')
                .then(() => { window.location.href='view_faulty_details.php?id=$sf_id'; });
              </script>";
    }
}

// 3. Fetch all items under this specific Stock Faulty ID
$sql = "
    SELECT sfi.*, 
           p.name AS product_name, p.code AS product_code,
           c.cat_name, 
           sup.sup_name
    FROM stock_faulty_items sfi
    LEFT JOIN product p ON sfi.product_id = p.id
    LEFT JOIN category c ON sfi.cat_id = c.cat_id
    LEFT JOIN supplier sup ON sfi.supplier_id = sup.id
    WHERE sfi.stock_faulty_id = '$sf_id'
";
$run = mysqli_query($con, $sql);

// Fetch master info (Supplier Name, Date, Status) from the first joined row
$master_check = mysqli_query($con, "
    SELECT sfi.status, sfi.created_at, sup.sup_name 
    FROM stock_faulty_items sfi 
    LEFT JOIN supplier sup ON sfi.supplier_id = sup.id 
    WHERE sfi.stock_faulty_id = '$sf_id' 
    LIMIT 1
");
$master_data = mysqli_fetch_assoc($master_check);

if (!$master_data) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Record not found for ID: $sf_id</div></div>";
    include('ini/footer.php');
    exit;
}
?>

<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-white py-4 border-bottom-0">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="text-primary font-weight-bold">FAULTY STOCK REPORT</h3>
                    <p class="mb-0"><strong>Batch ID:</strong> #<?php echo $sf_id; ?></p>
                    <p class="mb-0"><strong>Supplier:</strong> <?php echo !empty($master_data['sup_name']) ? $master_data['sup_name'] : '<span class="text-muted">N/A</span>'; ?></p>
                    <p><strong>Created Date:</strong> <?php echo date('d M Y, h:i A', strtotime($master_data['created_at'])); ?></p>
                </div>
                <div class="col-md-6 text-md-right">
                    <h5>Status: 
                        <?php if ($master_data['status'] == 0): ?>
                            <span class="badge badge-danger px-3 py-2">PENDING RETURN</span>
                        <?php else: ?>
                            <span class="badge badge-success px-3 py-2">RETURNED</span>
                        <?php endif; ?>
                    </h5>
                    
                    <?php if ($master_data['status'] == 0): ?>
                        <form method="POST" class="mt-2 d-inline">
                            <button type="submit" name="update_status" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-check-circle"></i> Mark as Returned
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <button onclick="window.print()" class="btn btn-secondary btn-sm mt-2 shadow-sm">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-dark text-white text-center">
                        <tr>
                            <th>#</th>
                            <th>Product Details</th>
                            <th>Lot No</th>
                            <th>Qty</th>
                            <th>Buy Price</th>
                            <th>Adjustable</th>
                            <th>Loss Amount</th>
                            <th>Remarks</th>
                            <th>Evidence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $total_loss = 0;
                        while ($item = mysqli_fetch_assoc($run)) { 
                            $total_loss += $item['loss_amount'];
                        ?>
                            <tr class="text-center">
                                <td><?php echo $i++; ?></td>
                                <td class="text-left">
                                    <strong><?php echo $item['product_name']; ?></strong><br>
                                    <small class="text-muted">Code: <?php echo $item['product_code']; ?></small>
                                </td>
                                <td><span class="badge badge-light border"><?php echo $item['lot_no']; ?></span></td>
                                <td><?php echo $item['qty']; ?></td>
                                <td><?php echo number_format($item['buy_price'], 2); ?></td>
                                <td><?php echo number_format($item['adjustable_amount'], 2); ?></td>
                                <td class="text-danger font-weight-bold"><?php echo number_format($item['loss_amount'], 2); ?></td>
                                <td><small><?php echo !empty($item['remarks']) ? $item['remarks'] : '-'; ?></small></td>
                                <td>
                                    <?php if (!empty($item['faulty_photo'])): ?>
                                        <a href="img/faulty/<?php echo $item['faulty_photo']; ?>" target="_blank">
                                            <img src="img/faulty/<?php echo $item['faulty_photo']; ?>" width="45" height="45" style="object-fit:cover; border-radius:4px; border: 1px solid #ddd;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No Photo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="6" class="text-right py-3">GRAND TOTAL LOSS:</th>
                            <th class="text-center text-danger h5 mb-0 py-3"><?php echo number_format($total_loss, 2); ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white py-3 border-top-0">
            <a href="view_stock_faulty.php" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Faulty List
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .card-footer, form, .main-footer, .navbar, .sidebar { display: none !important; }
        .card { border: none !important; }
        .shadow { box-shadow: none !important; }
        .container { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        table { font-size: 12px; }
        .text-danger { color: #dc3545 !important; }
    }
</style>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php include('ini/footer.php'); ?>