<?php 

// Include the header from your ini folder
include('ini/header.php'); 


include('dbcon.php');

$id = intval($_SESSION['id']); 

// Fetch User Data for Header
$user_sql = "SELECT u.*, b.branch_name 
             FROM user u 
             LEFT JOIN branches b ON u.branch_id = b.branch_id 
             WHERE u.id = $id";
$user_exe = mysqli_query($con, $user_sql);
$data = mysqli_fetch_assoc($user_exe);

// --- PROFIT & LOSS CALCULATIONS ---

// 1. Calculate Total Raw Loss
$loss_query = "SELECT SUM(qty * buy_price) as total_raw_loss FROM stock_faulty_items";
$loss_res = mysqli_query($con, $loss_query);
$loss_row = mysqli_fetch_assoc($loss_res);
$raw_loss = $loss_row['total_raw_loss'] ?? 0;

// 2. Calculate Recovered Amount from purchase_order
$recovery_query = "SELECT SUM(paid_amount) as total_recovered FROM purchase_order WHERE adjusted_with > 0";
$recovery_res = mysqli_query($con, $recovery_query);
$recovery_row = mysqli_fetch_assoc($recovery_res);
$recovered = $recovery_row['total_recovered'] ?? 0;

$final_loss = $raw_loss - $recovered;

// 3. Gross Profit
$profit_query = "SELECT SUM(sell_price - buy_price) as gross_profit FROM purchase_order WHERE status = 'sold'"; 
$profit_res = mysqli_query($con, $profit_query);
$profit_row = mysqli_fetch_assoc($profit_res);
$gross_profit = $profit_row['gross_profit'] ?? 0;

$net_profit = $gross_profit - $final_loss;

?>

<body id="page-top">
    <div id="wrapper">

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Financial Report: Profit & Loss</h1>
                    </div>

                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Gross Profit (Sales)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($gross_profit, 2); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inventory Loss (Net)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($final_loss, 2); ?></div>
                                    <small class="text-muted">Initial Loss: <?php echo number_format($raw_loss, 2); ?> | Recovered: <?php echo number_format($recovered, 2); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Net Profit</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($net_profit, 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Detailed Faulty Item History</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Faulty ID</th>
                                            <th>Product ID</th>
                                            <th>Qty</th>
                                            <th>Original Cost</th>
                                            <th>Balance to Adjust</th>
                                            <th>Date Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $list_sql = "SELECT * FROM stock_faulty_items ORDER BY created_at DESC";
                                        $list_exe = mysqli_query($con, $list_sql);
                                        while($row = mysqli_fetch_assoc($list_exe)) {
                                            $total_item_loss = $row['qty'] * $row['buy_price'];
                                            ?>
                                            <tr>
                                                <td><?php echo $row['stock_faulty_id']; ?></td>
                                                <td><?php echo $row['product_id']; ?></td>
                                                <td><?php echo $row['qty']; ?></td>
                                                <td><?php echo number_format($total_item_loss, 2); ?></td>
                                                <td>
                                                    <?php 
                                                        if($row['adjustable_amount'] == 0) {
                                                            echo '<span class="badge badge-success">Recovered</span>';
                                                        } else {
                                                            echo '<span class="text-danger">' . number_format($row['adjustable_amount'], 2) . '</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('ini/footer.php'); ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "order": [[ 5, "desc" ]]
            });
        });
    </script>