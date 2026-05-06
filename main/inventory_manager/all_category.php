<?php
include('ini/header.php');
include('dbcon.php');
$sql = "SELECT * FROM category";
$run = mysqli_query($con,$sql);
?>
	<div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">All Category</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-center" id="dataTable" width="100%">
                                    <thead>
                                        <tr>
											<td>Category Name</td>
											<td>Action</td>
										</tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
											<td>Category Name</td>
											<td>Action</td>
										</tr>
                                    </tfoot>
                                    <tbody>
                                        <?php while ($data = mysqli_fetch_assoc($run)) { ?>
								            <tr>
								                <td><?php echo $data['cat_name'] ?></td>
								                <td>
								                	<a href="update_cat.php?id=<?php echo $data['cat_id']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>

								                	<a href="delete_cat.php?id=<?php echo $data['cat_id']?>" class="btn btn-danger btn-sm"><i class="fa fa-close"></i></a>
								                </td>
								        	</tr>
								        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>


<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "lengthChange": true,
        "pageLength": 10,
        "language": {
            "search": "Search in table:"
        }
    });
});
</script>

<?php include('ini/footer.php'); ?>


