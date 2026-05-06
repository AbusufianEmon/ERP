<?php
include('ini/header.php');
include('dbcon.php');

// 1. Total Supplier Due Pending 
$due_sql = "SELECT SUM((buy_price * qty) - paid_amount) as total_due 
            FROM purchase_order 
            WHERE status != 2";
$due_res = mysqli_query($con, $due_sql);
$due_data = mysqli_fetch_assoc($due_res);
$total_due = $due_data['total_due'] ?? 0;

// 2. All Faulty Item Count (From stock_faulty_items)
$faulty_count_sql = "SELECT COUNT(*) as total_faulty FROM stock_faulty_items"; 
$faulty_count_res = mysqli_query($con, $faulty_count_sql);
$faulty_count_data = mysqli_fetch_assoc($faulty_count_res);
$total_faulty_count = $faulty_count_data['total_faulty'] ?? 0;

// 3. All Return Item Count (From purchase_return)
$return_count_sql = "SELECT COUNT(*) as total_returns FROM purchase_return";
$return_count_res = mysqli_query($con, $return_count_sql);
$return_count_data = mysqli_fetch_assoc($return_count_res);
$total_returns_count = $return_count_data['total_returns'] ?? 0;

// 4. Low Stock Products Count (Under 10 Qty)
$low_stock_sql = "SELECT p.name, ps.qty 
                  FROM product_stock ps
                  LEFT JOIN product p ON ps.product_id = p.id
                  WHERE ps.qty < 10 
                  ORDER BY ps.qty ASC";
$low_stock_res = mysqli_query($con, $low_stock_sql);
$low_stock_count = mysqli_num_rows($low_stock_res);
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Inventory Dashboard</h1>
        
    </div>

    <div class="row">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Supplier Dues</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_due, 2); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Faulty Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_faulty_count; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Returned Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_returns_count; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-undo fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Low Stock Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $low_stock_count; ?> Alerts</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Critical Stock Inventory (Below 10 Qty)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Current Stock Qty</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php if($low_stock_count > 0): ?>
                                    <?php while($item = mysqli_fetch_assoc($low_stock_res)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name'] ?? 'Unknown Product'); ?></td>
                                        <td><span class="badge badge-danger p-2"><?php echo $item['qty']; ?></span></td>
                                        <td><span class="text-danger font-weight-bold">Low Stock</span></td>
                                        <td><a href="add_purchase_order.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Reorder</a></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">All products have sufficient stock.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>