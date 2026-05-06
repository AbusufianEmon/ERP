<?php
// fetch_category_products_purchashe.php
include 'dbcon.php';

// 1. Check if cat_id is sent
if (!isset($_POST['cat_id']) || empty($_POST['cat_id'])) {
    echo "<tr><td colspan='3' class='text-center text-danger'>No category selected.</td></tr>";
    exit;
}

$cat = (int)$_POST['cat_id'];

// 2. Simple, clean query on the 'product' table
// We do not use 'ps' or 'product_stock' here because we are purchasing NEW items.
$sql = "SELECT id, name, code FROM product WHERE cat_id = $cat";

$res = mysqli_query($con, $sql);

if (!$res) {
    // This will help you see the exact SQL error if it fails
    echo "<tr><td colspan='3' class='text-center text-danger'>Error: " . mysqli_error($con) . "</td></tr>";
    exit;
}

if (mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><?php echo htmlspecialchars($r['code']); ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-success add-product" 
                    data-id="<?php echo $r['id']; ?>"
                    data-name="<?php echo htmlspecialchars($r['name']); ?>">
                    <i class="fas fa-plus-circle"></i> Add
                </button>
            </td>
        </tr>
    <?php }
} else {
    echo "<tr><td colspan='3' class='text-center'>No products found in this category.</td></tr>";
}
?>