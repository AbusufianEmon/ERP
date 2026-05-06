<?php
include('../dbcon.php');

$term = mysqli_real_escape_string($con, $_GET['term']);

$q = mysqli_query($con, "
SELECT cus_id, customer_code, name
FROM customer
WHERE customer_code LIKE '%$term%'
OR name LIKE '%$term%'
LIMIT 10
");

while ($c = mysqli_fetch_assoc($q)) {
    echo "
    <div class='list-group-item customer-item'
        data-id='{$c['cus_id']}'
        data-code='{$c['customer_code']}'
        data-name='{$c['name']}'>
        {$c['name']} ({$c['customer_code']})
    </div>";
}
