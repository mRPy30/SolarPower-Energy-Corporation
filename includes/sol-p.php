<?php require_once __DIR__ . '/routes.php'; ?>
<link rel="stylesheet" href="<?= htmlspecialchars(asset_url('assets/sol-p.css?v=1'), ENT_QUOTES, 'UTF-8') ?>">
<div id="solp-assistant" class="solp-assistant" hidden>
    <button type="button" class="solp-button" aria-label="Say hello to SOL-P, your solar assistant" aria-controls="solp-greeting" aria-expanded="false">
        <span class="solp-body"><span class="solp-sprite" aria-hidden="true"></span></span>
    </button>
</div>
<div id="solp-greeting" class="solp-greeting" hidden>
    <button type="button" class="solp-close" aria-label="Dismiss SOL-P greeting">×</button>
    <p>Hi! I'm SOL-P ☀️ Your Solar Assistant. Need help finding the right solar solution?</p>
    <button type="button" class="solp-chat" hidden>Open solar chat</button>
</div>
<script src="<?= htmlspecialchars(asset_url('assets/sol-p.js?v=1'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
