<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>DataTable CSV Export Test</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" />

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<!-- JSZip for Excel/CSV export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

</head>
<body>

<div class="container mt-4">
    <h2>Product Table</h2>
    <table id="example" class="display nowrap table table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Code</th>
                <th>Category</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
                <th>Total Qty</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Product A</td>
                <td>P001</td>
                <td>Category 1</td>
                <td>100</td>
                <td>150</td>
                <td>15</td>
                <td>Supplier 1</td>
            </tr>
            <tr>
                <td>Product B</td>
                <td>P002</td>
                <td>Category 2</td>
                <td>200</td>
                <td>250</td>
                <td>5</td>
                <td>Supplier 2</td>
            </tr>
            <tr>
                <td>Product C</td>
                <td>P003</td>
                <td>Category 3</td>
                <td>300</td>
                <td>350</td>
                <td>20</td>
                <td>Supplier 3</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#example').DataTable({
        dom: 'Bfrtip', // show buttons, filter, etc
        buttons: [
            {
                extend: 'csvHtml5',
                text: 'Export CSV',
                title: 'ProductsExport'
            }
        ],
        scrollX: true
    });
});
</script>

</body>
</html>
