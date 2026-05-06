<?php
include('dbcon.php');

/* ===============================
   Search Quotation Invoice
================================ */
if(isset($_POST['search'])) {
    $key = mysqli_real_escape_string($con, $_POST['search']);
    
    // Updated query to check for manager approval
    $sql = "SELECT DISTINCT corporate_quotation_invoice_id 
            FROM corporate_quotation 
            WHERE corporate_quotation_invoice_id LIKE '%$key%'
            AND bill_status = 0 
            AND manager_approvel_status = 1
            ORDER BY corporate_quotation_invoice_id DESC
            LIMIT 10";
            
    $res = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($res) == 0){
        echo "<div class='list-group-item'>No approved quotation found</div>";
        exit;
    }
    
    while($row = mysqli_fetch_assoc($res)){
        echo "<a href='javascript:void(0)' class='list-group-item list-group-item-action' 
              onclick=\"selectQuotation('{$row['corporate_quotation_invoice_id']}')\">{$row['corporate_quotation_invoice_id']}</a>";
    }
    exit;
}

/* ===============================
   Fetch Invoice Details
================================ */
if(isset($_POST['invoice'])) {
    $invoice = mysqli_real_escape_string($con, $_POST['invoice']);
    
    // Updated query to ensure manager_approvel_status = 1
    $sql = "SELECT cq.corporate_quotation_id, cq.corporate_id, cq.product_id, cq.product_stock_id,
                   cq.corporate_code, cq.product_name, cq.qty, cq.offer_price,
                   cc.corporate_name, p.code AS product_code
            FROM corporate_quotation cq
            LEFT JOIN corporate_customer cc ON cq.corporate_id = cc.corporate_id
            LEFT JOIN product p ON cq.product_id = p.id
            WHERE cq.corporate_quotation_invoice_id = '$invoice'
            AND cq.bill_status = 0
            AND cq.manager_approvel_status = 1";
            
    $res = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($res) == 0){
        echo "<div class='alert alert-danger'>No data found or quotation not approved by manager.</div>";
        exit;
    }

    $grand_total = 0;
    echo "<table class='table table-bordered text-center'>
            <thead class='table-dark'>
                <tr>
                    <th>Product</th>
                    <th>Code</th>
                    <th>Qty</th>
                    <th>Offer Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>";
            
    while($row = mysqli_fetch_assoc($res)){
        $total = $row['qty'] * $row['offer_price'];
        $grand_total += $total;
        
        echo "<tr>
                <td>{$row['product_name']}</td>
                <td>{$row['product_code']}</td>
                <td>{$row['qty']}</td>
                <td>".number_format($row['offer_price'], 2)."</td>
                <td>".number_format($total, 2)."</td>
              </tr>";

        // Hidden fields for form submission
        echo "<input type='hidden' name='corporate_quotation_id[]' value='{$row['corporate_quotation_id']}'>
              <input type='hidden' name='corporate_id[]' value='{$row['corporate_id']}'>
              <input type='hidden' name='product_id[]' value='{$row['product_id']}'>
              <input type='hidden' name='product_stock_id[]' value='{$row['product_stock_id']}'>
              <input type='hidden' name='corporate_code[]' value='{$row['corporate_code']}'>
              <input type='hidden' name='corporate_name[]' value='{$row['corporate_name']}'>
              <input type='hidden' name='product_code[]' value='{$row['product_code']}'>
              <input type='hidden' name='product_name[]' value='{$row['product_name']}'>
              <input type='hidden' name='qty[]' value='{$row['qty']}'>";
    }
    
    echo "</tbody></table>";
    echo "<input type='hidden' name='grand_total' value='".number_format($grand_total, 2, '.', '')."'>";
    exit;
}
?>