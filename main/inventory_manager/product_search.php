<?php
// product_search.php
include('dbcon.php');

$q = trim($_POST['query'] ?? '');
if($q === '') exit;

$q_safe = mysqli_real_escape_string($con, $q);
$sql = "SELECT id, name, code FROM product WHERE name LIKE '%$q_safe%' OR code LIKE '%$q_safe%' LIMIT 15";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)){
    $id = $row['id'];
    $name = htmlspecialchars($row['name']);
    $code = htmlspecialchars($row['code']);
    echo "<a href='#' class='list-group-item list-group-item-action product-item' data-id='$id' data-name='".htmlspecialchars($name)."'>$name &nbsp; <small class='text-muted'>[$code]</small></a>";
}
