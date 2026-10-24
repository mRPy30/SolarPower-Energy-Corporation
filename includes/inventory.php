<?php
// product.stockQuantity is available stock. Variants share their parent stock pool.
function inventory_quantity($value, bool $allowZero = false): int {
    if (!is_scalar($value) || !preg_match('/^\d+$/D', (string)$value) || (float)$value > 2147483647 || (int)$value < ($allowZero ? 0 : 1)) {
        throw new RuntimeException('Enter a valid whole-number quantity.');
    }
    return (int)$value;
}
function inventory_price($value): float {
    if (!is_scalar($value) || !preg_match('/^\d+(?:\.\d{1,2})?$/D', (string)$value) || (float)$value <= 0 || (float)$value > 99999999.99) {
        throw new RuntimeException('Enter a positive price with at most two decimal places.');
    }
    return round((float)$value, 2);
}
function inventory_totals(array $items): array {
    $totals=[];
    foreach ($items as $item) {
        $id=inventory_quantity($item['product_id'] ?? $item['id'] ?? null);
        $qty=inventory_quantity($item['quantity'] ?? null);
        $totals[$id]=inventory_quantity(($totals[$id] ?? 0)+$qty);
    }
    ksort($totals, SORT_NUMERIC);
    return $totals;
}
// Caller owns the transaction. Product locks are always acquired in ID order.
function inventory_allocate(mysqli $conn, string $reference, array $items): void {
    foreach (inventory_totals($items) as $id=>$qty) {
        $stmt=$conn->prepare("UPDATE product SET stockQuantity=stockQuantity-? WHERE id=? AND status='Active' AND stockQuantity>=?");
        $stmt->bind_param('iii',$qty,$id,$qty); $stmt->execute();
        if ($stmt->affected_rows!==1) throw new RuntimeException('A product is unavailable or has insufficient stock. Refresh your cart.');
        $stmt=$conn->prepare("INSERT INTO inventory_allocations(order_reference,product_id,quantity,state) VALUES(?,?,?,'held')");
        $stmt->bind_param('sii',$reference,$id,$qty); $stmt->execute();
    }
}
function inventory_consume(mysqli $conn, string $reference): void {
    $stmt=$conn->prepare("SELECT product_id,state FROM inventory_allocations WHERE order_reference=? ORDER BY product_id FOR UPDATE");
    $stmt->bind_param('s',$reference);$stmt->execute();$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (!$rows) throw new RuntimeException('Checkout has no inventory reservation. Contact support for reconciliation.');
    foreach ($rows as $row) if (!in_array($row['state'],['held','consumed'],true)) throw new RuntimeException('This inventory reservation was released. Contact support.');
    foreach ($rows as $row) {
        $check=$conn->prepare("SELECT id FROM product WHERE id=? AND status='Active' AND stockQuantity>=0 FOR UPDATE");
        $check->bind_param('i',$row['product_id']);$check->execute();
        if (!$check->get_result()->fetch_assoc()) throw new RuntimeException('A reserved product is no longer available. Contact support for payment reconciliation.');
    }
    $stmt=$conn->prepare("UPDATE inventory_allocations SET state='consumed' WHERE order_reference=? AND state='held'");
    $stmt->bind_param('s',$reference);$stmt->execute();
}
function inventory_assert_deletable(mysqli $conn,int $id): void {
    $stmt=$conn->prepare("SELECT product_id FROM inventory_allocations WHERE product_id=? AND state IN ('held','consumed') LIMIT 1");
    $stmt->bind_param('i',$id);$stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) throw new RuntimeException('This product has reserved or sold inventory. Hide it instead of deleting it.');
}
// Idempotent release, called only after verified failure or an authorized staff cancellation.
function inventory_release(mysqli $conn, string $reference): void {
    $stmt=$conn->prepare('SELECT product_id,quantity,state FROM inventory_allocations WHERE order_reference=? ORDER BY product_id FOR UPDATE');
    $stmt->bind_param('s',$reference);$stmt->execute();$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $row) {
        if ($row['state']==='released') continue;
        $stmt=$conn->prepare('UPDATE product SET stockQuantity=stockQuantity+? WHERE id=?');
        $stmt->bind_param('ii',$row['quantity'],$row['product_id']);$stmt->execute();
        if ($stmt->affected_rows!==1) throw new RuntimeException('Cannot restore stock for a deleted product. Restore the product before cancelling.');
        $stmt=$conn->prepare("UPDATE inventory_allocations SET state='released' WHERE order_reference=? AND product_id=?");
        $stmt->bind_param('si',$reference,$row['product_id']);$stmt->execute();
    }
}
function inventory_assert_schema(mysqli $conn): void {
    foreach (['product','orders','order_items','inventory_allocations','maya_pending_checkouts'] as $table) {
        $stmt=$conn->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $stmt->bind_param('s',$table);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
        if (!$row || strtoupper($row['ENGINE'])!=='INNODB') throw new RuntimeException('Inventory setup requires the inventory migration and InnoDB tables. Contact support.');
    }
}
