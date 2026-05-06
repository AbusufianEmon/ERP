<?php
include('dbcon.php');

$supplier_id = intval($_POST['supplier_id'] ?? 0);
$product_id  = intval($_POST['product_id'] ?? 0);

if (!$supplier_id || !$product_id) {
    echo "<option value=''>-- None --</option>";
    exit;
}

/*
    Fetch only stock_faulty_items where:
    - supplier = selected supplier
    - product  = selected product
    - adjustable_amount > 0
    - status = 1 (active)
*/
$sql = "
    SELECT stock_faulty_id,
           SUM(adjustable_amount) AS total_adj
    FROM stock_faulty_items
    WHERE supplier_id = $supplier_id
      AND product_id  = $product_id
      AND adjustable_amount > 0
      AND status = 1
    GROUP BY stock_faulty_id
    ORDER BY total_adj DESC
";

$res = mysqli_query($con, $sql);

$options = "<option value=''>-- None --</option>";

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $sfid      = htmlspecialchars($row["stock_faulty_id"]);
        $available = floatval($row["total_adj"]);

        $options .= "<option value='{$sfid}' data-available='{$available}'>
                        {$sfid} (available: {$available})
                     </option>";
    }
} 

echo $options;
