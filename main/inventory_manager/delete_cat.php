<?php 
include('dbcon.php');
$idd = $_GET['id'];
$sql= "DELETE FROM category WHERE cat_id=$idd";
$run = mysqli_query($con,$sql);
if ($run) {
	 echo "<script>
    		   window.alert('Deleted & Clicked category is deleted');
	           window.open('all_category.php','_self');
	        </script>";
}else{
	echo "error";
}
?>