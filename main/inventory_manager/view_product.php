<?php 
$idd = $_GET['id'];
include('ini/header.php');
include('dbcon.php');

$sql = "SELECT p.name, p.code, p.photo, c.cat_name 
        FROM product p
        LEFT JOIN category c ON p.cat_id = c.cat_id 
        WHERE p.id = '$idd'";
$run = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($run);
?>
<h1 class="text-center">View Product :</h1>
<div class="row mb-5 mt-2">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">
            <table class="table table-hover table-striped" width="50%">
                <tr>
                    <td class="font-weight-bold" style="width: 150px;">Product Name :</td>
                    <td style="width: 400px;"><?php echo $data['name']; ?></td>
                </tr>
                <tr>
                    <td class="font-weight-bold" style="width: 150px;">Product Code :</td>
                    <td style="width: 400px;"><?php echo $data['code']; ?></td>
                </tr>
                <tr>
                    <td class="font-weight-bold" style="width: 150px;">Category :</td>
                    <td style="width: 400px;"><?php echo $data['cat_name']; ?></td>
                </tr>
                <tr>
                    <td class="font-weight-bold" style="width: 150px;">Photo :</td>
                    <td>
                        <?php if (!empty($data['photo'])) { ?>
                            <img src="img/products/<?php echo $data['photo']; ?>" 
                                 style="height: 140px; width: 150px; border-radius: 20%;">
                        <?php } else { ?>
                            <span class="text-muted">No Photo</span>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php include('ini/footer.php'); ?>
