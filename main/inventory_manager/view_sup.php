<?php
include('ini/header.php');
include('dbcon.php');
$sql = "SELECT * FROM supplier";
$run = mysqli_query($con,$sql);
?>
	<div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">All Supplier</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-center" id="example" width="100%">
                                    <thead>
                                        <tr>
                                            <td>Supplier Name</td>
                                            <td>Supplier Address</td>
                                            <td>Supplier Phone</td>
                                            <td>Supplier Email</td>
                                            <td>Supplier Address</td>
											<td>Supplier Photo</td>
											<td>Action</td>
										</tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <td>Supplier Name</td>
                                            <td>Supplier Address</td>
                                            <td>Supplier Phone</td>
                                            <td>Supplier Email</td>
                                            <td>Supplier Address</td>
                                            <td>Supplier Photo</td>
                                            <td>Action</td>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php while ($data = mysqli_fetch_assoc($run)) { ?>
								            <tr>
                                                <td><?php echo $data['sup_name'] ?></td>
                                                <td><?php echo $data['sup_add'] ?></td>
                                                <td><?php echo $data['sup_phone'] ?></td>
                                                <td><?php echo $data['sup_email'] ?></td>
                                                <td><?php echo $data['sup_add'] ?></td>
                                                 <td><img src="img/supplier/<?php echo $data['sup_photo'] ?>" height="100" width="100"></td>
								                <td>
								                	<a href="update_sup.php?id=<?php echo $data['id']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>

								                	<a href="delete_sup.php?id=<?php echo $data['id']?>" class="btn btn-danger btn-sm"><i class="fa fa-close"></i></a>
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


