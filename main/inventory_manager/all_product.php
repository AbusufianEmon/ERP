<?php
include('ini/header.php');
include('dbcon.php');

// Fetch product with category name
$sql = "SELECT p.*, c.cat_name 
        FROM product p
        LEFT JOIN category c ON p.cat_id = c.cat_id";
$run = mysqli_query($con, $sql);
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Products</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" id="example" width="100%">
                    <thead>
                        <tr>
                            <td>Product Name</td>
                            <td>Product Code</td>
                            <td>Category</td>
                            <td>Photo</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td>Product Name</td>
                            <td>Product Code</td>
                            <td>Category</td>
                            <td>Photo</td>
                            <td>Action</td>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php while ($data = mysqli_fetch_assoc($run)) { ?>
                            <tr>
                                <td><?php echo $data['name']; ?></td>
                                <td><?php echo $data['code']; ?></td>
                                <td><?php echo $data['cat_name']; ?></td>
                                <td>
                                    <?php if (!empty($data['photo'])) { ?>
                                        <img src="img/products/<?php echo $data['photo']; ?>" 
                                             alt="Product Photo" 
                                             width="60" height="60">
                                    <?php } else { ?>
                                        <span class="text-muted">No Photo</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="view_product.php?id=<?php echo $data['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                                    <a href="edit_product.php?id=<?php echo $data['id']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                    <a href="delete_product.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-sm"><i class="fa fa-close"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.5/css/buttons.dataTables.min.css">

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'print'
            ]
        });
    });
</script>

<?php include('ini/footer.php'); ?>
