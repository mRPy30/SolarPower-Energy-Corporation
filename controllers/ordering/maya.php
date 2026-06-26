<?php
/**
 * Maya Checkout configuration - LIVE CHECKOUT API ONLY.
 * This must point to Maya Checkout API, not PayMaya.me manual payment links.
 */
return [
    'public_key' => getenv('MAYA_LIVE_PUBLIC_KEY') ?: getenv('MAYA_PUBLIC_KEY') ?: 'pk-qHHZGTX1Bw3soTrLl2OqFTIyDk2lflGj7zQSkKGKsxr',
    'secret_key' => getenv('MAYA_LIVE_SECRET_KEY') ?: getenv('MAYA_SECRET_KEY') ?: 'sk-bJShA5MHzwgqWUl9AtdpchYMiY5ZjdVfm2i75MHbH2X',
    'base_url' => 'https://pg.maya.ph',
    'is_live' => true,
];
