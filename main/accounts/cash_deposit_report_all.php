<?php
include('dbcon.php');
include('ini/header.php');

// Fetch all deposits joined with branch names
$sql = "SELECT d.*, b.branch_name 
        FROM cash_deposits d 
        LEFT JOIN branches b ON d.branch_id = b.branch_id 
        ORDER BY d.deposit_date DESC";
$result = mysqli_query($con, $sql);
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">All Branch Cash Deposits</h1>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm no-print">
            <i class="fas fa-print fa-sm text-white-50"></i> Print Full Report
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Deposit Transaction Log</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="depositTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Deposit Date</th>
                            <th>Branch Name</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                            <th class="no-print">Slip</th>
                            <th class="no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_all_deposits = 0;
                        if(mysqli_num_rows($result) > 0):
                            while($row = mysqli_fetch_assoc($result)): 
                                $total_all_deposits += $row['amount'];
                        ?>
                        <tr>
                            <td><?= $row['deposit_id'] ?></td>
                            <td><?= date('d-M-Y', strtotime($row['deposit_date'])) ?></td>
                            <td><span class="badge badge-info p-2"><?= htmlspecialchars($row['branch_name']) ?></span></td>
                            <td class="font-weight-bold text-dark"><?= number_format($row['amount'], 2) ?></td>
                            <td><small><?= htmlspecialchars($row['remarks']) ?></small></td>
                            <td class="no-print text-center">
                                <?php if(!empty($row['slip_photo'])): ?>
                                    <a href="../executive/uploads/<?= $row['slip_photo'] ?>" target="_blank" class="btn btn-sm btn-circle btn-success">
                                        <i class="fas fa-image"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No Photo</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print">
                                <button class="btn btn-sm btn-outline-primary view-details" 
                                        data-id="<?= $row['deposit_id'] ?>"
                                        data-branch="<?= $row['branch_name'] ?>"
                                        data-amount="<?= number_format($row['amount'], 2) ?>"
                                        data-date="<?= date('d-M-Y', strtotime($row['deposit_date'])) ?>"
                                        data-remarks="<?= htmlspecialchars($row['remarks']) ?>"
                                        data-img="<?= $row['slip_photo'] ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="3" class="text-right font-weight-bold">Grand Total Deposited:</th>
                            <th class="text-primary font-weight-bold" style="font-size: 1.1rem;"><?= number_format($total_all_deposits, 2) ?></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="depositModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Deposit Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="printableModalBody">
                <div class="row">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Branch:</th>
                                <td id="m_branch"></td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td id="m_amount" class="text-danger font-weight-bold"></td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td id="m_date"></td>
                            </tr>
                            <tr>
                                <th>Remarks:</th>
                                <td id="m_remarks"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-7 text-center border-left">
                        <h6 class="font-weight-bold">Deposit Slip / Receipt</h6>
                        <hr>
                        <img src="" id="m_img" class="img-fluid rounded shadow-sm" style="max-height: 450px; width: auto; display: none; border: 1px solid #ddd;">
                        <div id="m_no_img" class="alert alert-secondary py-5" style="display: none;">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>No Slip Uploaded
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printModal()">
                    <i class="fas fa-print mr-1"></i> Print Slip
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#depositTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf'],
        order: [[0, 'desc']]
    });

    // View Modal Logic
    $('.view-details').on('click', function() {
        const data = $(this).data();
        
        // Fill Text Data
        $('#m_branch').text(data.branch);
        $('#m_amount').text('Tk ' + data.amount);
        $('#m_date').text(data.date);
        $('#m_remarks').text(data.remarks || 'No remarks provided.');
        
        // Handle Image Path Correctly
        if(data.img && data.img !== "") {
            // Path is outside: ../executive/uploads/
            $('#m_img').attr('src', '../executive/uploads/' + data.img).show();
            $('#m_no_img').hide();
        } else {
            $('#m_img').hide();
            $('#m_no_img').show();
        }
        
        $('#depositModal').modal('show');
    });
});

function printModal() {
    var content = document.getElementById('printableModalBody').innerHTML;
    var win = window.open('', '', 'height=700,width=900');
    win.document.write('<html><head><title>Deposit Slip Detail</title>');
    win.document.write('<link rel="stylesheet" href="css/sb-admin-2.min.css">');
    win.document.write('<style>.border-left { border-left: 1px solid #ddd !important; } img { max-width: 100%; height: auto; }</style>');
    win.document.write('</head><body>');
    win.document.write('<div class="container mt-5">' + content + '</div>');
    win.document.write('</body></html>');
    
    // Give image time to load before printing
    setTimeout(function() {
        win.print();
        win.close();
    }, 700);
}
</script>

<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar, .navbar, .footer, .btn, #sidebarToggleTop { display: none !important; }
        #content-wrapper { margin: 0 !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .badge-info { border: 1px solid #000; color: #000; background: transparent !important; }
    }
    #m_img {
        cursor: zoom-in;
    }
</style>

<?php include('ini/footer.php'); ?>