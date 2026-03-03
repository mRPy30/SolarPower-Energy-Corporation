<?php
/**
 * Solar Builder API — Get Peripherals / Add-on Services
 *
 * GET (no parameters needed)
 * Returns all active peripherals sorted by display_order.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/db_pdo.php';

// Table solar_builder_peripherals was removed - return empty array
echo json_encode(['success' => true, 'peripherals' => []]);
