<?php 
session_start();

if (isset($_SESSION['email'])) {
    include('dbcon.php');

    if (isset($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        
        // Fetch invoice info (added adjusted_with)
        $sql_info = "
            SELECT sup_invoice.invoice_no, sup_invoice.adjusted_with, supplier.sup_name, supplier.sup_email, supplier.sup_phone, sup_invoice.datee
            FROM sup_invoice
            LEFT JOIN supplier ON sup_invoice.supp_id = supplier.id
            WHERE sup_invoice.invoice_no = '$invoice_no'
            LIMIT 1
        ";
        $res_info = mysqli_query($con, $sql_info);
        $info = mysqli_fetch_assoc($res_info);

        // Fetch invoice items
        $sql_items = "
            SELECT si.product_name, p.code, si.qty, si.paid_amount, si.due_amount, po.buy_price, po.status
            FROM sup_invoice si
            LEFT JOIN product p ON si.product_id = p.id
            LEFT JOIN purchase_order po ON po.invoice_no = si.invoice_no AND po.product_id = si.product_id
            WHERE si.invoice_no = '$invoice_no'
        ";
        $res_items = mysqli_query($con, $sql_items);
    } else {
        $info = null;
        $res_items = null;
    }
} else {
    header('location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supplier Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f4f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .invoice-container {
            max-width: 1200px;
            background: #fff;
            margin: 40px auto;
            padding: 50px 70px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .invoice-header {
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .invoice-header h2 {
            font-weight: 700;
        }

        .company-details p {
            margin: 0;
            color: #555;
        }

        .invoice-info h5 {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .table th, .table td {
            vertical-align: middle !important;
        }

        .table th {
            background-color: #333;
            color: white;
            text-align: center;
        }

        .table td {
            text-align: center;
        }

        .returned-row {
            background-color: #ffecec !important;
            color: #b10000 !important;
            font-weight: 600;
        }

        tfoot td {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 40%;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            margin-top: 70px;
        }

        @media print {
            body {
                background: none;
            }
            .invoice-container {
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                height: auto;
                padding: 20px 40px;
            }
            .d-print-none {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <!-- Header -->
    <div class="invoice-header d-flex justify-content-between align-items-center">
        <div>
            <h2>ETM.ERP</h2>
            <div class="company-details">
                <p>3184 Spruce Drive, Pittsburgh, PA 15201</p>
                <p>Phone: 012-345-6789</p>
                <p>Email: info@etmerp.com</p>
            </div>
        </div>
        <div class="text-end">
            <h4>
                Invoice #<?php echo htmlspecialchars($info['invoice_no'] ?? ''); ?>
                
            </h4>
            <p>Date: <?php echo htmlspecialchars($info['datee'] ?? ''); ?></p>
        </div>
    </div>

    <!-- Supplier Info -->
    <div class="row invoice-info mb-4">
        <div class="col-sm-6">
            <h5>Billed To:</h5>
            <?php if ($info): ?>
                <p><strong><?php echo htmlspecialchars($info['sup_name']); ?></strong></p>
                <p>Email: <?php echo htmlspecialchars($info['sup_email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($info['sup_phone']); ?></p>
            <?php else: ?>
                <p>No supplier data found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Product Name</th>
                    <th>Code</th>
                    <th>Quantity</th>
                    <th>Net Price</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_paid = 0;
                $total_due = 0;
                $total_price = 0;
                $i = 1;

                if ($res_items && mysqli_num_rows($res_items) > 0) {
                    while ($row = mysqli_fetch_assoc($res_items)) {
                        $row_class = "";
                        $status_msg = "";
                        $paid = 0;
                        $due = 0;

                        $net_price = (float)$row['buy_price'] * (int)$row['qty'];

                        if (isset($row['status']) && $row['status'] == 2) {
                            $paid = 0;
                            $due = 0;
                            $status_msg = "<span class='text-danger fw-bold'>Returned</span>";
                            $row_class = "returned-row";
                        } elseif (isset($row['status']) && $row['status'] == 1) {
                            $paid = (float)$row['paid_amount'];
                            $due = (float)$row['due_amount'];
                            $status_msg = "Received";
                        } else {
                            $paid = (float)$row['paid_amount'];
                            $due = (float)$row['due_amount'];
                            $status_msg = "Pending";
                        }

                        $total_paid += $paid;
                        $total_due += $due;
                        $total_price += $net_price;

                        echo "<tr class='$row_class'>
                                <td>TK i</td>
                                <td>" . htmlspecialchars($row['product_name']) . "</td>
                                <td>" . htmlspecialchars($row['code']) . "</td>
                                <td>" . htmlspecialchars($row['qty']) . "</td>
                                <td>TK " . number_format($net_price, 2) . "</td>
                                <td>TK " . number_format($paid, 2) . "</td>
                                <td>TK " . number_format($due, 2) . "</td>
                                <td>$status_msg</td>
                              </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No items found for this invoice.</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">Total</td>
                    <td><?php echo number_format($total_price, 2); ?></td>
                    <td><?php echo number_format($total_paid, 2); ?></td>
                    <td><?php echo number_format($total_due, 2); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Adjusted With (bottom section) -->
    <?php if (!empty($info['adjusted_with']) && strtolower($info['adjusted_with']) !== 'not'): ?>
        <div class="mt-4">
            <h3 class="text-info"><strong>Adjusted With:</strong> <?php echo htmlspecialchars($info['adjusted_with']); ?></h3>
        </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="line"></div>
            <p>Authorized Signature</p>
        </div>
        <div class="signature-box">
            <div class="line"></div>
            <p>Supplier Signature</p>
        </div>
    </div>

    <!-- Buttons -->
    <div class="d-print-none mt-5 text-end">
        <button onclick="window.print()" class="btn btn-success btn-lg">Print Invoice</button>
        <a href="update_sup_inv.php?invoice_no=<?php echo htmlspecialchars($info['invoice_no'] ?? ''); ?>" class="btn btn-primary btn-lg">Edit Invoice</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
