<?php
/**
 * One-time repair tool:
 * Backfills previously paid Maya checkout rows into orders/order_items/tracking.
 *
 * Preview:
 *   /repair_paid_maya_orders.php
 *
 * Run:
 *   /repair_paid_maya_orders.php?run=1&key=repair-paid-maya-orders-20260708
 *
 * Optional specific references:
 *   /repair_paid_maya_orders.php?refs=SP-REF-1,SP-REF-2
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config/dbconn.php';
require_once __DIR__ . '/includes/checkout-service.php';

$repairKey = getenv('MAYA_REPAIR_KEY') ?: 'repair-paid-maya-orders-20260708';
$run = (string) ($_GET['run'] ?? '') === '1';
$key = (string) ($_GET['key'] ?? '');

if ($run && !hash_equals($repairKey, $key)) {
    http_response_code(403);
    exit("Invalid repair key.\n");
}

function repair_refs_from_request(): array
{
    $raw = trim((string) ($_GET['refs'] ?? ''));
    if ($raw === '') {
        return [];
    }

    $refs = array_filter(array_map('trim', explode(',', $raw)));
    return array_values(array_unique($refs));
}

function repair_tracking_exists(mysqli $conn, int $orderId, string $status): bool
{
    $stmt = $conn->prepare('SELECT id FROM order_tracking_history WHERE order_id = ? AND status = ? LIMIT 1');
    $stmt->bind_param('is', $orderId, $status);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $exists;
}

function repair_insert_tracking(mysqli $conn, int $orderId, string $status, string $description, string $createdAt): void
{
    if (repair_tracking_exists($conn, $orderId, $status)) {
        $stmt = $conn->prepare('UPDATE order_tracking_history SET created_at = ? WHERE order_id = ? AND status = ?');
        $stmt->bind_param('sis', $createdAt, $orderId, $status);
        $stmt->execute();
        $stmt->close();
        return;
    }

    $stmt = $conn->prepare('INSERT INTO order_tracking_history (order_id, status, description, created_at) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $orderId, $status, $description, $createdAt);
    $stmt->execute();
    $stmt->close();
}

function repair_paid_pending_rows(mysqli $conn, array $refs): array
{
    checkout_ensure_pending_maya_table($conn);

    $sql = "SELECT * FROM maya_pending_checkouts WHERE status = 'paid' AND order_id IS NULL";
    $types = '';
    $params = [];

    if ($refs) {
        $placeholders = implode(', ', array_fill(0, count($refs), '?'));
        $sql .= " AND order_reference IN ({$placeholders})";
        $types = str_repeat('s', count($refs));
        $params = $refs;
    }

    $sql .= ' ORDER BY id ASC';
    $stmt = $conn->prepare($sql);

    if ($params) {
        $bind = [$types];
        foreach ($params as $idx => $value) {
            $bind[] = &$params[$idx];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind);
    }

    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

$refs = repair_refs_from_request();
$rows = repair_paid_pending_rows($conn, $refs);

echo $run ? "RUN MODE\n" : "PREVIEW MODE\n";
echo "Rows found: " . count($rows) . "\n\n";

foreach ($rows as $pending) {
    $orderRef = checkout_clean_text($pending['order_reference'] ?? '');
    $paidAt = $pending['paid_at'] ?: ($pending['created_at'] ?: date('Y-m-d H:i:s'));

    echo "Reference: {$orderRef}\n";

    $existing = checkout_find_order_by_reference($conn, $orderRef);
    if ($existing) {
        echo " - Existing order found: #{$existing['id']}\n";

        if ($run) {
            $stmt = $conn->prepare("UPDATE maya_pending_checkouts SET order_id = ?, paid_at = COALESCE(paid_at, created_at, NOW()) WHERE id = ?");
            $existingId = (int) $existing['id'];
            $pendingId = (int) $pending['id'];
            $stmt->bind_param('ii', $existingId, $pendingId);
            $stmt->execute();
            $stmt->close();
        }

        echo "\n";
        continue;
    }

    $payload = json_decode((string) ($pending['payload_json'] ?? ''), true);
    if (!is_array($payload) || !isset($payload['customer'], $payload['validated'], $payload['deliveryRate'])) {
        echo " - SKIPPED: invalid payload_json.\n\n";
        continue;
    }

    $customer = $payload['customer'];
    $validated = $payload['validated'];
    $deliveryRate = $payload['deliveryRate'];

    $itemsSubtotal = (float) ($validated['subtotal'] ?? 0);
    $deliveryFee = (float) ($deliveryRate['price'] ?? 0);
    $computedTotal = round($itemsSubtotal + $deliveryFee, 2);
    $storedTotal = (float) ($pending['total_amount'] ?? 0);

    echo " - Customer: " . ($customer['name'] ?? 'Unknown') . "\n";
    echo " - Items: " . count($validated['items'] ?? []) . "\n";
    echo " - Computed total from payload: PHP " . number_format($computedTotal, 2) . "\n";
    echo " - Stored pending total: PHP " . number_format($storedTotal, 2) . "\n";

    if (abs($computedTotal - $storedTotal) > 0.01) {
        echo " - NOTE: pending total differs from payload total; using payload total to keep items/subtotal/delivery consistent.\n";
    }

    if (!$run) {
        echo " - Preview only. Add ?run=1&key={$repairKey} to backfill.\n\n";
        continue;
    }

    try {
        $order = checkout_create_order($conn, $customer, $validated, $deliveryRate, $orderRef, 'paid', 'confirmed');
        $orderId = (int) $order['id'];

        $stmt = $conn->prepare("UPDATE orders SET created_at = ? WHERE id = ?");
        $stmt->bind_param('si', $paidAt, $orderId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE maya_pending_checkouts SET order_id = ?, paid_at = COALESCE(paid_at, created_at, NOW()) WHERE id = ?");
        $pendingId = (int) $pending['id'];
        $stmt->bind_param('ii', $orderId, $pendingId);
        $stmt->execute();
        $stmt->close();

        repair_insert_tracking($conn, $orderId, 'pending', 'Order has been placed and is awaiting confirmation', $paidAt);
        repair_insert_tracking($conn, $orderId, 'confirmed', 'Payment verified via Maya. Order confirmed.', $paidAt);

        echo " - CREATED order #{$orderId}\n\n";
    } catch (Throwable $e) {
        echo " - ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo $run
    ? "Done. Delete this repair file from production after confirming the orders.\n"
    : "No changes were made.\n";
