<?php 
include('dbcon.php');
$id = $_GET['id'];
$sql= "DELETE FROM product WHERE id=$id";
$run = mysqli_query($con,$sql);
if ($run) {
	 echo "<script>
    		   window.alert('Deleted & Clicked product is deleted');
	           window.open('all_product.php','_self');
	        </script>";
}else{
	echo "error";
}
?>