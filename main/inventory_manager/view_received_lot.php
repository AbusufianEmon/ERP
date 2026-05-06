<?php
include('ini/header.php');
include('dbcon.php');

// Get Invoice Number from URL
if (!isset($_GET['invoice_no'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>No Invoice Number Provided.</div></div>";
    include('ini/footer.php');
    exit;
}

$invoice_no = mysqli_real_escape_string($con, $_GET['invoice_no']);

// Fetch Invoice/Supplier Header Info
$header_sql = "SELECT po.invoice_no, po.created_at, po.lot_no, s.sup_name, s.sup_phone, s.sup_add
               FROM purchase_order po
               LEFT JOIN supplier s ON po.supplier_id = s.id
               WHERE po.invoice_no = '$invoice_no'
               LIMIT 1";
$header_res = mysqli_query($con, $header_sql);
$header = mysqli_fetch_assoc($header_res);

if (!$header) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Invoice not found.</div></div>";
    include('ini/footer.php');
    exit;
}
?>

<div class="container mt-4 mb-5">
    <div class="d-print-none mb-3 text-end">
        <a href="purchase_history.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fa fa-print"></i> Print Invoice</button>
    </div>

    <div class="card shadow border-0" id="invoice-card">
        <div class="card-body p-5">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h1 class="text-success fw-bold">INVOICE</h1>
                    <p class="mb-0"><strong>MTE ERP Solutions</strong></p>
                    <p class="text-muted">Sylhet, Bangladesh<br>Phone: +880 123456789</p>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <h4 class="mb-1 text-dark">#<?php echo htmlspecialchars($header['invoice_no']); ?></h4>
                    <p class="mb-0 text-muted">Date: <?php echo date('M d, Y', strtotime($header['created_at'])); ?></p>
                    <p class="mb-0 text-muted">Lot No: <?php echo htmlspecialchars($header['lot_no']); ?></p>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="text-muted text-uppercase small font-weight-bold">Supplier Info:</h6>
                    <h5 class="mb-1 text-primary"><?php echo htmlspecialchars($header['sup_name']); ?></h5>
                    <p class="mb-0 text-muted"><?php echo htmlspecialchars($header['sup_add']); ?></p>
                    <p class="mb-0 text-muted">Phone: <?php echo htmlspecialchars($header['sup_phone']); ?></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 80px;">Item</th>
                            <th>Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Buy Price</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total = 0;
                        $total_paid = 0;

                        $items_sql = "SELECT po.*, p.name AS product_name, p.code AS product_code, p.photo
                                      FROM purchase_order po
                                      LEFT JOIN product p ON po.product_id = p.id
                                      WHERE po.invoice_no = '$invoice_no'";
                        $items_res = mysqli_query($con, $items_sql);

                        while ($item = mysqli_fetch_assoc($items_res)):
                            $is_returned = ($item['status'] == 2);
                            $line_total = $item['qty'] * $item['buy_price'];
                            
                            // Only add to totals if NOT status 2 (Returned)
                            if (!$is_returned) {
                                $grand_total += $line_total;
                                $total_paid += $item['paid_amount'];
                            }
                        ?>
                        <tr class="<?php echo $is_returned ? 'text-muted bg-light' : ''; ?>">
                            <td>
                                <img src="img/products/<?php echo htmlspecialchars($item['photo']); ?>" 
                                     class="rounded border" style="width:50px; height:50px; object-fit:cover; <?php echo $is_returned ? 'filter: grayscale(1); opacity: 0.5;' : ''; ?>">
                            </td>
                            <td>
                                <span class="fw-bold d-block <?php echo $is_returned ? 'text-decoration-line-through' : 'text-dark'; ?>">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </span>
                                <small class="text-muted">Code: <?php echo htmlspecialchars($item['product_code']); ?></small>
                                <?php if($is_returned): ?>
                                    <span class="badge bg-danger ms-2">RETURNED</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center <?php echo $is_returned ? 'text-decoration-line-through' : ''; ?>">
                                <?php echo $item['qty']; ?>
                            </td>
                            <td class="text-end <?php echo $is_returned ? 'text-decoration-line-through' : ''; ?>">
                                <?php echo number_format($item['buy_price'], 2); ?>
                            </td>
                            <td class="text-end fw-bold <?php echo $is_returned ? 'text-decoration-line-through text-muted' : 'text-dark'; ?>">
                                <?php echo number_format($line_total, 2); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-md-5 col-lg-4">
                    <table class="table table-sm table-borderless border-bottom">
                        <tr>
                            <th class="text-muted">Active Grand Total:</th>
                            <td class="text-end h5 text-dark"><?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Total Paid:</th>
                            <td class="text-end text-success fw-bold"><?php echo number_format($total_paid, 2); ?></td>
                        </tr>
                        <tr class="border-top">
                            <th class="text-dark h5">Total Amount Due:</th>
                            <td class="text-end h5 text-danger font-weight-bold">
                                <?php echo number_format(max($grand_total - $total_paid, 0), 2); ?>
                            </td>
                        </tr>
                    </table>
                    <p class="text-muted small text-end">* Returned items are excluded from totals.</p>
                </div>
            </div>

            <div class="row mt-5 pt-5 pb-3">
                <div class="col-4 text-center">
                    <div class="signature-line"></div>
                    <p class="small fw-bold mt-2">Authorized Signature</p>
                </div>
                <div class="col-4"></div>
                <div class="col-4 text-center">
                    <div class="signature-line"></div>
                    <p class="small fw-bold mt-2">Supplier Signature</p>
                </div>
            </div>

            <div class="mt-5 border-top pt-3 text-center">
                <p class="small text-muted">This is a system-generated invoice. No signature is required for electronic use.</p>
                <p class="fw-bold text-success">Thank you for your business!</p>
            </div>
        </div>
    </div>
</div>

<style>
.signature-line {
    border-top: 1px solid #444; 
    width: 80%; 
    margin: 0 auto;
}

@media print {
    body { background: white !important; }
    .card { box-shadow: none !important; border: none !important; }
    .container { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important;}
    .ini-header, .ini-footer, .d-print-none, #sidebarToggle, .navbar { display: none !important; }
    .card-body { padding: 0 !important; }
    .text-decoration-line-through { text-decoration: line-through !important; }
}
</style>

<?php include('ini/footer.php'); ?>