<?php 
include('dbcon.php');
$idd = $_GET['id'];
$sql= "DELETE FROM supplier WHERE id = $idd";
$run = mysqli_query($con,$sql);
if ($run) {
	 echo "<script>
    		   window.alert('Deleted & Clicked Supplier is deleted');
	           window.open('view_sup.php','_self');
	        </script>";
}else{
	echo "error";
}
?>