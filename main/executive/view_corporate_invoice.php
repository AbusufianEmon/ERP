<?php
include('dbcon.php');
include('ini/header.php');

/* ===============================
    Get Parameters
================================ */
if (!isset($_GET['invoice_id'])) {
    die("<div class='alert alert-danger m-4'>Error: Invoice ID is missing.</div>");
}

$invoice_id = mysqli_real_escape_string($con, $_GET['invoice_id']);

// Fix: Safely get branch_id from GET or fallback to the session data if available
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : (isset($data['branch_id']) ? $data['branch_id'] : 0);

/* ===============================
    Fetch Invoice & Branch Details
================================ */
$sql = "SELECT 
            cs.*, 
            b.branch_name as b_name, 
            b.address as b_address
        FROM corporate_sales cs
        LEFT JOIN branches b ON cs.branch_id = b.branch_id
        WHERE cs.corporate_sales_invoice_id = '$invoice_id'
        LIMIT 1";

$res = mysqli_query($con, $sql);

// Fallback if the join fails
if (!$res || mysqli_num_rows($res) == 0) {
    $sql_basic = "SELECT * FROM corporate_sales WHERE corporate_sales_invoice_id = '$invoice_id' LIMIT 1";
    $res = mysqli_query($con, $sql_basic);
}

$inv = mysqli_fetch_assoc($res);

if (!$inv) {
    die("<div class='alert alert-danger m-4'>Invoice not found.</div>");
}

/* ===============================
    Fetch All Items in this Invoice
================================ */
$items_sql = "SELECT * FROM corporate_sales WHERE corporate_sales_invoice_id = '$invoice_id'";
$items_run = mysqli_query($con, $items_sql);
?>

<style>
    @media print {
        .no-print, .sidebar, .navbar, .footer, #sidebarToggleTop, .scroll-to-top { display: none !important; }
        #content-wrapper { margin-left: 0 !important; margin-top: 0 !important; padding: 0 !important; }
        .container-fluid { width: 100%; padding: 0; margin: 0; }
        .card { border: none !important; box-shadow: none !important; }
        body { background-color: white !important; color: black !important; }
    }
    .invoice-title { font-weight: bold; font-size: 2.5rem; color: #1cc88a; }
    .invoice-header { border-bottom: 3px solid #f8f9fc; margin-bottom: 30px; padding-bottom: 20px; }
    .table-invoice thead th { background-color: #4e73df !important; color: white !important; border: none; }
</style>

<div class="container-fluid">

    <div class="mb-4 no-print d-flex justify-content-between">
        <a href="all_corporate_bill.php?branch_id=<?= $branch_id ?>" class="btn btn-secondary btn-icon-split shadow-sm">
            <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
            <span class="text">Back to List</span>
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-icon-split shadow-sm">
            <span class="icon text-white-50"><i class="fas fa-print"></i></span>
            <span class="text">Print Invoice</span>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-5">
            
            <div class="row invoice-header align-items-center">
                <div class="col-md-6">
                    <h1 class="invoice-title">INVOICE</h1>
                    <p class="text-muted">Invoice ID: <span class="text-dark font-weight-bold">#<?= htmlspecialchars($inv['corporate_sales_invoice_id']) ?></span></p>
                </div>
                <div class="col-md-6 text-md-right">
                    <h3 class="font-weight-bold text-primary">MTE ERP</h3>
                    <p class="text-muted small mb-0">
                        <?= htmlspecialchars($inv['b_name']) ?> Branch
                    </p>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <p class="text-uppercase text-muted font-weight-bold small mb-1">Billed To:</p>
                    <h5 class="font-weight-bold mb-1"><?= htmlspecialchars($inv['corporate_name']) ?></h5>
                    <p class="mb-0 text-dark">Customer Code: <?= htmlspecialchars($inv['corporate_code']) ?></p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-1"><strong>Invoice Date:</strong> <?= date('d-M-Y', strtotime($inv['created_at'])) ?></p>
                    <p class="mb-1"><strong>Status:</strong> 
                        <?php if($inv['bill_collection_status'] == 1): ?>
                            <span class="badge badge-success">PAID</span>
                        <?php else: ?>
                            <span class="badge badge-warning text-dark">UNPAID / PENDING</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-invoice" width="100%">
                    <thead>
                        <tr class="text-center text-white">
                            <th style="width: 50px;">#</th>
                            <th class="text-left">Product Details</th>
                            <th>Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        $grand_total = 0;
                        while($item = mysqli_fetch_assoc($items_run)){ 
                            $line_total = $item['qty'] * $item['selling_price'];
                            $grand_total += $line_total;
                        ?>
                        <tr class="text-center">
                            <td><?= $count++ ?></td>
                            <td class="text-left">
                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($item['product_name']) ?></span><br>
                                <small class="text-muted">Code: <?= htmlspecialchars($item['product_code']) ?></small>
                            </td>
                            <td><?= (int)$item['qty'] ?></td>
                            <td class="text-right"><?= number_format($item['selling_price'], 2) ?></td>
                            <td class="text-right font-weight-bold"><?= number_format($line_total, 2) ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold text-uppercase">Grand Total</td>
                            <td class="text-right font-weight-bold text-primary" style="font-size: 1.25rem;">
                                <?= number_format($grand_total, 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if(!empty($inv['remarks'])): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="p-3 bg-light border-left-primary">
                        <strong>Remarks:</strong><br>
                        <span class="small"><?= nl2br(htmlspecialchars($inv['remarks'])) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row" style="margin-top: 80px;">
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #ccc; width: 80%; margin: 0 auto;"></div>
                    <p class="small mt-2">Prepared By</p>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #ccc; width: 80%; margin: 0 auto;"></div>
                    <p class="small mt-2">Received By</p>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #ccc; width: 80%; margin: 0 auto;"></div>
                    <p class="small mt-2">Authorized Signatory</p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>