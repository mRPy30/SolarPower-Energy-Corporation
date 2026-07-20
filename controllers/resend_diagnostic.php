<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in to the staff dashboard first.',
    ]);
    exit;
}

$rootPath = dirname(__DIR__);
$configPath = $rootPath . '/config/resend.php';
$localConfigPath = $rootPath . '/config/resend.local.php';
$mailerPath = $rootPath . '/includes/resend-mailer.php';

function diagnostic_safe_message($value): string
{
    $message = (string) $value;
    $message = preg_replace('/re_[A-Za-z0-9]+_[A-Za-z0-9]{10,}/', '[redacted-resend-key]', $message);
    return substr($message, 0, 1200);
}

function diagnostic_bool_label(bool $value): string
{
    return $value ? 'yes' : 'no';
}

$diagnostic = [
    'config_file_exists' => diagnostic_bool_label(is_file($configPath)),
    'local_secret_file_exists' => diagnostic_bool_label(is_file($localConfigPath)),
    'mailer_file_exists' => diagnostic_bool_label(is_file($mailerPath)),
    'curl_available' => diagnostic_bool_label(function_exists('curl_init')),
    'env_key_present' => diagnostic_bool_label(trim((string) getenv('RESEND_API_KEY')) !== ''),
    'api_key_present' => 'no',
    'from' => '',
    'reply_to' => '',
];

try {
    if (is_file($mailerPath)) {
        require_once $mailerPath;
    }

    if (!function_exists('solar_resend_config') || !function_exists('solar_send_resend_email')) {
        throw new RuntimeException('Email helper is not loaded. Upload includes/resend-mailer.php.');
    }

    $config = solar_resend_config();
    $diagnostic['api_key_present'] = diagnostic_bool_label(trim((string) ($config['api_key'] ?? '')) !== '');
    $diagnostic['from'] = $config['from'] ?? '';
    $diagnostic['reply_to'] = $config['reply_to'] ?? '';

    $shouldSend = isset($_GET['send']) && $_GET['send'] === '1';
    if ($shouldSend) {
        $result = solar_send_resend_email(
            'solar@solarpower.com.ph',
            'SolarPower Resend Diagnostic Test',
            '<p>This is a diagnostic test from SolarPower Energy Corporation.</p>'
        );

        $diagnostic['test_send'] = [
            'sent' => (bool) ($result['success'] ?? false),
            'provider' => $result['provider'] ?? 'resend',
            'message' => diagnostic_safe_message($result['message'] ?? ''),
            'response' => diagnostic_safe_message($result['response'] ?? ''),
        ];
    }

    echo json_encode([
        'success' => true,
        'diagnostic' => $diagnostic,
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => diagnostic_safe_message($e->getMessage()),
        'diagnostic' => $diagnostic,
    ]);
}
