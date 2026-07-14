<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/dbconn.php';

try {
    // Join to order_items + products to get the category_id of the first item
    $sql = "SELECT o.id, o.order_reference, o.customer_name, o.customer_email,
                   o.customer_phone, o.total_amount, o.order_status,
                   o.payment_status, o.tracking_number, o.current_location,
                   o.estimated_delivery, o.delivered_at, o.created_at, o.sales_channel,
                   COALESCE(p.category, '') AS product_category
            FROM orders o
            LEFT JOIN (
                SELECT oi.order_id, MIN(oi.id) AS min_item_id
                FROM order_items oi
                GROUP BY oi.order_id
            ) first_item ON first_item.order_id = o.id
            LEFT JOIN order_items oi ON oi.id = first_item.min_item_id
            LEFT JOIN product p ON p.id = oi.product_id
            WHERE o.payment_status = 'paid'
              AND (
                  o.sales_channel IS NULL
                  OR o.sales_channel = ''
                  OR LOWER(o.sales_channel) = 'website'
              )
            ORDER BY o.created_at DESC";

    $result = $conn->query($sql);
    $tracking = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tracking[] = $row;
        }
        echo json_encode(['success' => true, 'tracking' => $tracking]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
