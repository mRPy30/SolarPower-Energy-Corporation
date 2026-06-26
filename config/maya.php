<?php
/**
 * Central Maya Checkout config.
 *
 * Environment variables win. Legacy controller config is read only as a fallback
 * so existing local setups keep working while secrets can be moved out of code.
 */

$legacy = [];
$legacyPath = __DIR__ . '/../controllers/ordering/maya.php';

if (file_exists($legacyPath)) {
    $loaded = include $legacyPath;
    if (is_array($loaded)) {
        $legacy = $loaded;
    }
}

return [
    'public_key' => getenv('MAYA_LIVE_PUBLIC_KEY') ?: getenv('MAYA_PUBLIC_KEY') ?: ($legacy['public_key'] ?? ''),
    'secret_key' => getenv('MAYA_LIVE_SECRET_KEY') ?: getenv('MAYA_SECRET_KEY') ?: ($legacy['secret_key'] ?? ''),
    'base_url' => getenv('MAYA_CHECKOUT_BASE_URL') ?: getenv('MAYA_BASE_URL') ?: 'https://pg.maya.ph',
    'is_live' => true,
];
