<?php require_once __DIR__ . '/routes.php'; ?>
<link rel="stylesheet" href="<?= htmlspecialchars(asset_url('assets/sol-p.css?v=7'), ENT_QUOTES, 'UTF-8') ?>">
<div id="solp-assistant" class="solp-assistant" hidden>
    <button type="button" class="solp-button" aria-label="Say hello to SOL-P, your solar assistant" aria-controls="solp-greeting" aria-expanded="false">
        <span class="solp-body"><span class="solp-whole-pose" aria-hidden="true"></span><span class="solp-rig" aria-hidden="true"><span class="solp-part solp-leg-left"></span><span class="solp-part solp-leg-right"></span><span class="solp-part solp-arm-left"></span><span class="solp-part solp-arm-right"></span><span class="solp-part solp-torso"></span><span class="solp-tablet"><span></span></span><span class="solp-part solp-head"></span></span></span>
    </button>
</div>
<div id="solp-greeting" class="solp-greeting" hidden>
    <button type="button" class="solp-close" aria-label="Dismiss SOL-P greeting">×</button>
    <p>Hi! I'm SOL-P ☀️ Your Solar Assistant! Need help finding the right solar solution?</p>
    <button type="button" class="solp-chat" hidden>Start Chat</button>
</div>
<script src="<?= htmlspecialchars(asset_url('assets/sol-p.js?v=7'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
