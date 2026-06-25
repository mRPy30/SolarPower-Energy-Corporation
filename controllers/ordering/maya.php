<?php
/**
 * ============================================================
 *  MAYA PAYMENT GATEWAY — CONFIGURATION FILE
 * ============================================================
 *  PLACEMENT:  Put this file ONE FOLDER ABOVE your project.
 *
 *  Example folder structure:
 *    /htdocs/                          ← place maya.php HERE
 *    /htdocs/SolarPower-Energy-Corporation/   ← your project
 *
 *  HOW TO GET YOUR KEYS:
 *    1. Go to https://developers.maya.ph
 *    2. Log in → Dashboard → Select your app
 *    3. Copy your Secret Key and Public Key
 *    4. Paste them below (replace the placeholder text)
 * ============================================================
 */

// ── SET THIS TO true WHEN YOU ARE READY TO ACCEPT REAL PAYMENTS ─────────────
$is_live = false;

// ── SANDBOX KEYS (for testing only — get from Maya Developer Portal) ─────────
$sandbox_public_key = 'pk-PASTE-YOUR-SANDBOX-PUBLIC-KEY-HERE';
$sandbox_secret_key = 'sk-PASTE-YOUR-SANDBOX-SECRET-KEY-HERE';

// ── LIVE KEYS (real money — get from Maya Developer Portal) ──────────────────
$live_public_key = 'pk-PASTE-YOUR-LIVE-PUBLIC-KEY-HERE';
$live_secret_key = 'sk-PASTE-YOUR-LIVE-SECRET-KEY-HERE';

// ── AUTO-SELECTS THE RIGHT KEY AND URL BASED ON $is_live ─────────────────────
return [
    'public_key' => $is_live ? $live_public_key : $sandbox_public_key,
    'secret_key' => $is_live ? $live_secret_key : $sandbox_secret_key,
    'base_url'   => $is_live
        ? 'https://pg.paymaya.com'
        : 'https://pg-sandbox.paymaya.com',
    'is_live'    => $is_live,
];
