<?php
include('dbcon.php');

$supplier_id = intval($_POST['supplier_id'] ?? 0);

if (!$supplier_id) {
    echo "<p>No supplier selected.</p>";
    exit;
}

/*
   Fetch all faulty groups for the supplier
   that are ACTIVE (status = 1)
*/
$sql = "
    SELECT stock_faulty_id,
           product_id,
           SUM(adjustable_amount) AS total_adj
    FROM stock_faulty_items
    WHERE supplier_id = $supplier_id
      AND adjustable_amount > 0
      AND status = 1
    GROUP BY stock_faulty_id, product_id
    ORDER BY stock_faulty_id
";

$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    echo "<p>No active faulty pools available.</p>";
    exit;
}

echo "<ul class='list-group'>";

while ($row = mysqli_fetch_assoc($res)) {
    $sfid      = htmlspecialchars($row['stock_faulty_id']);
    $pid       = intval($row['product_id']);
    $available = floatval($row['total_adj']);

    // product name
    $p = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM product WHERE id = $pid"));
    $pname = htmlspecialchars($p['name'] ?? 'Unknown Product');

    echo "
        <li class='list-group-item'>
            <b>$sfid</b> — $pname  
            <span class='badge bg-success float-end'>
                Available: $available
            </span>
        </li>
    ";
}

echo "</ul>";
