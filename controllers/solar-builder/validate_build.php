<?php
/**
 * Solar Builder API — Validate Build Compatibility
 *
 * POST JSON body:
 * {
 *   "panels":     { "id": 95,  "qty": 10 },
 *   "inverter":   { "id": 135, "qty": 1  },
 *   "battery":    { "id": 130, "qty": 2  },
 *   "mounting":   { "id": 500, "qty": 1  },
 *   "wiring":     { "id": 511, "qty": 1  },
 *   "monitoring": { "id": 520, "qty": 1  }
 * }
 *
 * Returns: compatibility warnings/errors + performance calculations.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../../config/db_pdo.php';

// ── Constants for Philippines ────────────────────────────────────────────────
const PEAK_SUN_HOURS     = 4.5;   // Average for Philippines
const ELECTRICITY_RATE   = 12.50;  // ₱/kWh (MERALCO average 2025-2026)
const CO2_PER_KWH        = 0.70;   // kg CO₂ per kWh (Philippine grid factor)
const SYSTEM_LOSS_FACTOR = 0.82;   // Typical losses (wiring, temperature, soiling)

// ── Parse request ────────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

try {
    $pdo = getPDO();

    // ── Gather product IDs and fetch data ────────────────────────────────────
    $categories = ['panels', 'inverter', 'battery', 'mounting', 'wiring', 'monitoring'];
    $productData = [];
    $allSpecs = [];
    $totalCost = 0;

    foreach ($categories as $cat) {
        if (empty($input[$cat]['id'])) continue;

        $pid = (int)$input[$cat]['id'];
        $qty = isset($input[$cat]['qty']) ? max(1, (int)$input[$cat]['qty']) : 1;

        // Fetch product
        $stmt = $pdo->prepare("SELECT id, displayName, price, stockQuantity FROM product WHERE id = ?");
        $stmt->execute([$pid]);
        $product = $stmt->fetch();
        if (!$product) continue;

        // Specs table was removed - specs will be empty
        $specs = [];

        $productData[$cat] = [
            'id'    => $pid,
            'name'  => $product['displayName'],
            'price' => (float)$product['price'],
            'stock' => (int)$product['stockQuantity'],
            'qty'   => $qty,
            'specs' => $specs,
        ];

        $totalCost += (float)$product['price'] * $qty;
    }

    // ── Compatibility Validation ─────────────────────────────────────────────
    $warnings = [];
    $errors   = [];

    $panelData   = $productData['panels']   ?? null;
    $inverterData = $productData['inverter'] ?? null;
    $batteryData = $productData['battery']  ?? null;
    $mountData   = $productData['mounting'] ?? null;
    $wiringData  = $productData['wiring']   ?? null;

    // -- Calculate total panel wattage --
    $totalPanelWatts = 0;
    $panelQty = 0;
    if ($panelData) {
        $panelWatts = (float)($panelData['specs']['wattage'] ?? 0);
        $panelQty   = $panelData['qty'];
        $totalPanelWatts = $panelWatts * $panelQty;
    }
    $totalPanelKw = $totalPanelWatts / 1000;

    // 1. INVERTER vs PANELS: Inverter must handle panel wattage
    if ($panelData && $inverterData) {
        $inverterKw = (float)($inverterData['specs']['rated_power_kw'] ?? 0);
        $maxPvInput = (float)($inverterData['specs']['max_pv_input_kw'] ?? $inverterKw * 1.5);

        if ($inverterKw > 0) {
            // Inverter output should be at least 80% of panel wattage for efficiency
            if ($inverterKw < $totalPanelKw * 0.7) {
                $errors[] = [
                    'type'    => 'inverter_undersized',
                    'message' => "Inverter ({$inverterKw}kW) is significantly undersized for your {$totalPanelKw}kW panel array. Recommended: at least " . ceil($totalPanelKw * 0.8) . "kW inverter.",
                    'severity' => 'error',
                ];
            } elseif ($inverterKw < $totalPanelKw * 0.85) {
                $warnings[] = [
                    'type'    => 'inverter_small',
                    'message' => "Inverter ({$inverterKw}kW) is slightly undersized for your {$totalPanelKw}kW panel array. Consider upgrading for optimal efficiency.",
                    'severity' => 'warning',
                ];
            }

            // Check max PV input
            if ($totalPanelKw > $maxPvInput * 1.1) {
                $errors[] = [
                    'type'    => 'pv_input_exceeded',
                    'message' => "Total panel wattage ({$totalPanelKw}kW) exceeds inverter's max PV input ({$maxPvInput}kW). Reduce panels or upgrade inverter.",
                    'severity' => 'error',
                ];
            }
        }
    }

    // 2. BATTERY vs INVERTER: Battery voltage must be compatible
    if ($batteryData && $inverterData) {
        $battVoltage  = (float)($batteryData['specs']['voltage'] ?? 0);
        $invVoltMin   = (float)($inverterData['specs']['battery_voltage_min'] ?? 0);
        $invVoltMax   = (float)($inverterData['specs']['battery_voltage_max'] ?? 0);
        $inverterType = $inverterData['specs']['inverter_type'] ?? '';

        // Grid-tie inverters typically don't support batteries
        if ($inverterType === 'Grid-Tie') {
            $warnings[] = [
                'type'    => 'grid_tie_no_battery',
                'message' => "Your inverter is Grid-Tie only and may not support battery storage. Consider a Hybrid inverter for battery backup.",
                'severity' => 'warning',
            ];
        }

        // Voltage compatibility check
        if ($battVoltage > 0 && $invVoltMin > 0 && $invVoltMax > 0) {
            if ($battVoltage < $invVoltMin || $battVoltage > $invVoltMax) {
                $errors[] = [
                    'type'    => 'battery_voltage_mismatch',
                    'message' => "Battery voltage ({$battVoltage}V) is outside inverter's compatible range ({$invVoltMin}V – {$invVoltMax}V).",
                    'severity' => 'error',
                ];
            }
        }
    }

    // 3. MOUNTING vs PANELS: Mounting must fit all panels
    if ($mountData && $panelData) {
        $maxPanels = (int)($mountData['specs']['max_panels'] ?? 0);
        if ($maxPanels > 0 && $panelQty > $maxPanels) {
            $errors[] = [
                'type'    => 'mounting_capacity',
                'message' => "Mounting system supports up to {$maxPanels} panels, but you selected {$panelQty} panels. Upgrade the mounting system or reduce panels.",
                'severity' => 'error',
            ];
        }
    }

    // 4. WIRING vs SYSTEM SIZE: Wiring kit must match system size
    if ($wiringData && $panelData) {
        $maxSystemKw = (float)($wiringData['specs']['max_system_kw'] ?? 0);
        if ($maxSystemKw > 0 && $totalPanelKw > $maxSystemKw * 1.1) {
            $warnings[] = [
                'type'    => 'wiring_undersized',
                'message' => "Wiring kit rated for {$maxSystemKw}kW, but your panel array is {$totalPanelKw}kW. Consider upgrading to a higher-rated wiring kit.",
                'severity' => 'warning',
            ];
        }
    }

    // 5. Stock check
    foreach ($productData as $cat => $pd) {
        if ($pd['qty'] > $pd['stock']) {
            $warnings[] = [
                'type'    => 'low_stock',
                'message' => "{$pd['name']}: Only {$pd['stock']} in stock, you requested {$pd['qty']}.",
                'severity' => 'warning',
            ];
        }
    }

    // ── Performance Calculations ─────────────────────────────────────────────
    $inverterEfficiency = 0.97; // default
    if ($inverterData) {
        $eff = (float)($inverterData['specs']['efficiency'] ?? 97);
        $inverterEfficiency = $eff / 100;
    }

    $systemEfficiency = $inverterEfficiency * SYSTEM_LOSS_FACTOR;
    $dailyOutputKWh  = ($totalPanelKw) * PEAK_SUN_HOURS * $systemEfficiency;
    $monthlyOutputKWh = $dailyOutputKWh * 30;
    $monthlySavings  = $monthlyOutputKWh * ELECTRICITY_RATE;
    $yearlySavings   = $monthlySavings * 12;
    $roiYears        = ($totalCost > 0 && $yearlySavings > 0) ? round($totalCost / $yearlySavings, 1) : 0;
    $co2ReductionT   = round(($dailyOutputKWh * 365 * CO2_PER_KWH) / 1000, 2);

    // Battery capacity
    $totalBatteryKWh = 0;
    if ($batteryData) {
        $totalBatteryKWh = (float)($batteryData['specs']['capacity_kwh'] ?? 0) * $batteryData['qty'];
    }

    // Home coverage
    if ($dailyOutputKWh < 8)       $coverage = '1 BR';
    elseif ($dailyOutputKWh < 15)  $coverage = '1–2 BR';
    elseif ($dailyOutputKWh < 25)  $coverage = '2–3 BR';
    elseif ($dailyOutputKWh < 40)  $coverage = '3–4 BR';
    elseif ($dailyOutputKWh < 55)  $coverage = '4–5 BR';
    else                           $coverage = '5+ BR / Commercial';

    // System tier
    if ($totalCost < 80000)        { $tier = 'STARTER';    $tierPct = 10; }
    elseif ($totalCost < 150000)   { $tier = 'ENTRY LEVEL'; $tierPct = 25; }
    elseif ($totalCost < 300000)   { $tier = 'MID-RANGE';  $tierPct = 50; }
    elseif ($totalCost < 600000)   { $tier = 'HIGH END';   $tierPct = 78; }
    else                           { $tier = 'PREMIUM';    $tierPct = 95; }

    // Radar scores (0-1)
    $radarScores = [
        'output'   => min(1, $dailyOutputKWh / 50),
        'panels'   => 0,
        'battery'  => 0,
        'inverter' => 0,
        'wiring'   => 0,
        'mounting' => 0,
    ];
    foreach (['panels', 'inverter', 'battery', 'mounting', 'wiring', 'monitoring'] as $cat) {
        $radarKey = $cat === 'panels' ? 'panels' : ($cat === 'monitoring' ? null : $cat);
        if ($radarKey && isset($productData[$cat]['specs']['builder_score'])) {
            $radarScores[$radarKey] = (float)$productData[$cat]['specs']['builder_score'] / 100;
        }
    }
    // Output is average of panels + inverter
    if ($radarScores['panels'] > 0 || $radarScores['inverter'] > 0) {
        $radarScores['output'] = ($radarScores['panels'] + $radarScores['inverter']) / 2;
    }

    $isCompatible = empty($errors);

    echo json_encode([
        'success'     => true,
        'compatible'  => $isCompatible,
        'errors'      => $errors,
        'warnings'    => $warnings,
        'performance' => [
            'total_wattage_kw'      => round($totalPanelKw, 2),
            'daily_output_kwh'      => round($dailyOutputKWh, 1),
            'monthly_output_kwh'    => round($monthlyOutputKWh, 0),
            'daily_savings'         => round($monthlySavings / 30, 0),
            'monthly_savings'       => round($monthlySavings, 0),
            'roi_years'             => $roiYears,
            'co2_reduction_tonnes'  => $co2ReductionT,
            'total_battery_kwh'     => round($totalBatteryKWh, 2),
            'home_coverage'         => $coverage,
            'system_tier'           => $tier,
            'tier_percentage'       => $tierPct,
            'total_cost'            => round($totalCost, 2),
        ],
        'radar' => $radarScores,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
