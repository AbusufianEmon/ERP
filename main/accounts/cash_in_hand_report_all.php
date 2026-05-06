<?php
include('dbcon.php');
include('ini/header.php');

/* ==========================================================
    1. Fetch All Branches
========================================================== */
$branches_query = "SELECT * FROM branches ORDER BY branch_name ASC";
$branches_res = mysqli_query($con, $branches_query);

$report_data = [];

while ($branch = mysqli_fetch_assoc($branches_res)) {
    $b_id = $branch['branch_id'];
    $b_name = $branch['branch_name'];

    // A. Sum Direct Sales (Using the Executive logic: Group by invoice to get unique paid amounts)
    $ds_sql = "SELECT SUM(paid_per_invoice) as total_ds FROM (
                SELECT MAX(paid_amount) as paid_per_invoice 
                FROM direct_sales 
                WHERE branch_id = $b_id AND status = 1 
                GROUP BY invoice_no
               ) as unique_sales";
    $ds_res = mysqli_query($con, $ds_sql);
    $ds_row = mysqli_fetch_assoc($ds_res);
    $total_direct = $ds_row['total_ds'] ?? 0;

    // B. Sum Corporate Sales (All collected items)
    $cs_sql = "SELECT SUM(selling_price * qty) as total_cs 
               FROM corporate_sales 
               WHERE branch_id = $b_id AND bill_collection_status = 1";
    $cs_res = mysqli_query($con, $cs_sql);
    $cs_row = mysqli_fetch_assoc($cs_res);
    $total_corporate = $cs_row['total_cs'] ?? 0;

    // C. Sum Deposits
    $dep_sql = "SELECT SUM(amount) as total_dep 
                FROM cash_deposits 
                WHERE branch_id = $b_id";
    $dep_res = mysqli_query($con, $dep_sql);
    $dep_row = mysqli_fetch_assoc($dep_res);
    $total_deposited = $dep_row['total_dep'] ?? 0;

    // D. Sum Expenses (New Addition to match Executive Report)
    $exp_sql = "SELECT SUM(amount) as total_exp 
                FROM expenses 
                WHERE branch_id = $b_id";
    $exp_res = mysqli_query($con, $exp_sql);
    $exp_row = mysqli_fetch_assoc($exp_res);
    $total_expenses = $exp_row['total_exp'] ?? 0;

    // E. Final Calculation (Sales - Deposits - Expenses)
    $gross_collected = $total_direct + $total_corporate;
    $cash_in_hand = $gross_collected - ($total_deposited + $total_expenses);

    $report_data[] = [
        'id' => $b_id,
        'name' => $b_name,
        'direct' => $total_direct,
        'corporate' => $total_corporate,
        'expenses' => $total_expenses,
        'deposited' => $total_deposited,
        'balance' => $cash_in_hand
    ];
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Branch-wise Cash In Hand Report (Executive View)</h1>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm no-print">
            <i class="fas fa-print fa-sm text-white-50"></i> Print Report
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-dark text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Consolidated Cash Summary (All Branches)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cashTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>Branch Name</th>
                            <th class="text-right">Direct Sales</th>
                            <th class="text-right">Corp. Sales</th>
                            <th class="text-right text-warning">Expenses</th>
                            <th class="text-right text-danger">Bank Deposits</th>
                            <th class="text-right font-weight-bold text-primary">Cash In Hand</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_cash = 0;
                        foreach ($report_data as $row): 
                            $grand_total_cash += $row['balance'];
                        ?>
                        <tr>
                            <td class="font-weight-bold">
                                <a href="cash_report_executive.php?branch_id=<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['name']) ?>
                                </a>
                            </td>
                            <td class="text-right"><?= number_format($row['direct'], 2) ?></td>
                            <td class="text-right"><?= number_format($row['corporate'], 2) ?></td>
                            <td class="text-right text-warning"><?= number_format($row['expenses'], 2) ?></td>
                            <td class="text-right text-danger"><?= number_format($row['deposited'], 2) ?></td>
                            <td class="text-right font-weight-bold <?= $row['balance'] < 0 ? 'text-danger' : 'text-primary' ?>">
                                <?= number_format($row['balance'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-primary text-white">
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold text-uppercase">Total Network Cash In Hand</td>
                            <td class="text-right font-weight-bold" style="font-size: 1.2rem;">
                                <?= number_format($grand_total_cash, 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#cashTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn-sm btn-success' },
            { extend: 'pdf', className: 'btn-sm btn-danger' },
            { extend: 'print', className: 'btn-sm btn-info' }
        ],
        paging: false,
        ordering: true,
        info: false
    });
});
</script>

<style>
    @media print {
        .no-print, .sidebar, .navbar, .footer, #sidebarToggleTop, .dt-buttons { display: none !important; }
        #content-wrapper { margin: 0 !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; }
        .table-bordered th, .table-bordered td { border: 1px solid #e3e6f0 !important; }
    }
    a { text-decoration: none !important; }
</style>

<?php include('ini/footer.php'); ?>