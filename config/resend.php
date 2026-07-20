<?php

$localConfigPath = __DIR__ . '/resend.local.php';
$localConfig = is_file($localConfigPath) ? require $localConfigPath : [];

return [
    'api_key' => getenv('RESEND_API_KEY') ?: ($localConfig['api_key'] ?? ''),
    'from' => getenv('RESEND_FROM_EMAIL') ?: ($localConfig['from'] ?? 'SolarPower Energy Corporation <solar@solarpower.com.ph>'),
    'reply_to' => getenv('RESEND_REPLY_TO') ?: ($localConfig['reply_to'] ?? 'solar@solarpower.com.ph'),
];
