<?php
include('ini/header.php');
include('dbcon.php');
?>

<h1 class="text-center">Add Branch</h1>

<div class="row">
    <div class="col-md-6 offset-3 card text-success" style="background: white; font-weight: bold;">
        <div class="card-body">

            <form method="post" action="" class="container">

                <label class="form-group">Branch Name :</label>
                <input type="text" name="branch_name" required placeholder="Branch Name" class="form-control"><br>

                <label class="form-group">Branch Address :</label>
                <input type="text" name="address" required placeholder="Branch Address" class="form-control"><br>

                <input type="submit" name="submit" value="Add Branch" class="btn btn-success form-control">

            </form>

        </div>
    </div>
</div>

<?php
include('ini/footer.php');

if (isset($_POST['submit'])) {

    $branch_name = mysqli_real_escape_string($con, $_POST['branch_name']);
    $address     = mysqli_real_escape_string($con, $_POST['address']);

    $sql = "INSERT INTO branches (branch_name, address) 
            VALUES ('$branch_name', '$address')";

    $run = mysqli_query($con, $sql);

    if ($run) {
        echo "<script>
            swal('Success!', 'Branch Added Successfully', 'success');
        </script>";
    } else {
        echo "<script>
            swal('Error!', 'Failed To Add Branch', 'error');
        </script>";
    }
}
?>
