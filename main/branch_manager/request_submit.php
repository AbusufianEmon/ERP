<?php
include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $from_branch = intval($_POST['from_branch']); // requester
    $to_branch   = intval($_POST['to_branch']);   // source
    $transfer_id = mysqli_real_escape_string($con, $_POST['request_id']);

    $products = $_POST['product_id'] ?? [];
    $lots     = $_POST['lot_no'] ?? [];
    $qtys     = $_POST['qty'] ?? [];

    if (empty($products)) {
        die("No products selected.");
    }

    mysqli_begin_transaction($con);

    try {
        foreach ($products as $i => $pid) {

            $pid    = intval($pid);
            $lot_no = mysqli_real_escape_string($con, $lots[$i]);
            $qty    = intval($qtys[$i]);

            // -------------------------------
            // ✅ FETCH STOCK FROM SOURCE BRANCH
            // -------------------------------
            $price_sql = "
                SELECT product_stock_id, buy_price, sell_price 
                FROM product_stock 
                WHERE product_id = $pid
                  AND lot_no = '$lot_no'
                  AND branch_id = $to_branch
                LIMIT 1
            ";

            $price_res = mysqli_query($con, $price_sql);

            if (!$price_res) {
                throw new Exception(mysqli_error($con));
            }

            if (mysqli_num_rows($price_res) == 0) {
                throw new Exception("Stock not found for Product ID $pid in Branch $to_branch");
            }

            $price_row        = mysqli_fetch_assoc($price_res);
            $product_stock_id = (int)$price_row['product_stock_id'];
            $buy_price        = (float)$price_row['buy_price'];
            $sell_price       = (float)$price_row['sell_price'];

            // -------------------------------
            // ✅ INSERT INTO product_transfer
            // -------------------------------
            // FIXED: Added missing comma after status value '1'
            $sql = "
                INSERT INTO product_transfer
                (transfer_id, product_id, product_stock_id, from_branch, to_branch, qty, lot_no, buy_price, sell_price, branch_to_branch_status, created_at)
                VALUES
                ('$transfer_id', $pid, $product_stock_id, $from_branch, $to_branch, $qty, '$lot_no', $buy_price, $sell_price, 1, NOW())
            ";

            if (!mysqli_query($con, $sql)) {
                throw new Exception(mysqli_error($con));
            }
        }

        mysqli_commit($con);
        ?>

        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Product request submitted successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>
        </body>
        </html>

        <?php

    } catch (Exception $e) {
        mysqli_rollback($con);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo addslashes($e->getMessage()); ?>'
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>
        <?php
    }
}
?>