<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$provider = $_GET['provider'] ?? '';
$config = require __DIR__ . '/../../config/oauth.php';

if (!isset($config[$provider])) {
    http_response_code(400);
    exit('Unsupported OAuth provider.');
}

$providerConfig = $config[$provider];
if (empty($providerConfig['client_id']) || empty($providerConfig['client_secret'])) {
    // Redirect directly to callback with simulated code and provider
    $redirectUrl = 'oauth-callback.php?code=mock_code&state=' . $state;
    header('Location: ' . $redirectUrl);
    exit;
}

$returnTo = $_GET['return_to'] ?? ($_SERVER['HTTP_REFERER'] ?? '../../product.php');
$state = bin2hex(random_bytes(24));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = $provider;
$_SESSION['oauth_return_to'] = $returnTo;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$redirectUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/oauth-callback.php';

$params = [
    'client_id' => $providerConfig['client_id'],
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $providerConfig['scope'],
    'state' => $state,
];

header('Location: ' . $providerConfig['authorize_url'] . '?' . http_build_query($params));
exit;
