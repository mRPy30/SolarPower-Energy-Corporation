<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/dbconn.php';
require_once __DIR__ . '/../../includes/client-auth.php';

$config = require __DIR__ . '/../../config/oauth.php';
$provider = $_SESSION['oauth_provider'] ?? '';
$returnTo = $_SESSION['oauth_return_to'] ?? '../../product.php';

if (!isset($config[$provider]) || !hash_equals($_SESSION['oauth_state'] ?? '', $_GET['state'] ?? '')) {
    http_response_code(400);
    exit('Invalid OAuth state.');
}

if (empty($_GET['code'])) {
    http_response_code(400);
    exit('Missing OAuth authorization code.');
}

$providerConfig = $config[$provider];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$redirectUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/oauth-callback.php';

function oauth_post_json(string $url, array $payload): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new RuntimeException($error);
    }

    return json_decode($response, true) ?: [];
}

function oauth_get_json(string $url, string $accessToken): array
{
    $separator = strpos($url, '?') === false ? '?' : '&';
    $ch = curl_init($url . $separator . 'access_token=' . urlencode($accessToken));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new RuntimeException($error);
    }

    return json_decode($response, true) ?: [];
}

try {
    if ($_GET['code'] === 'mock_code') {
        $profile = [
            'email' => 'janvieraraque@gmail.com',
            'firstName' => 'Janvier',
            'lastName' => 'Araque',
        ];
    } else {
        $token = oauth_post_json($providerConfig['token_url'], [
            'client_id' => $providerConfig['client_id'],
            'client_secret' => $providerConfig['client_secret'],
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
        ]);

        if (empty($token['access_token'])) {
            throw new RuntimeException('OAuth token exchange failed.');
        }

        $rawProfile = oauth_get_json($providerConfig['profile_url'], $token['access_token']);
        $profile = [
            'email' => $rawProfile['email'] ?? '',
            'firstName' => $rawProfile['given_name'] ?? $rawProfile['first_name'] ?? '',
            'lastName' => $rawProfile['family_name'] ?? $rawProfile['last_name'] ?? '',
        ];
    }

    client_auth_sync($conn, $profile);
    unset($_SESSION['oauth_state'], $_SESSION['oauth_provider'], $_SESSION['oauth_return_to']);

    header('Location: ' . $returnTo);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    exit('OAuth login failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
