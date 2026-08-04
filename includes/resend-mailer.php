<?php

if (!function_exists('solar_resend_config')) {
    function solar_resend_config(): array
    {
        $configPath = __DIR__ . '/../config/resend.php';
        $fileConfig = is_file($configPath) ? require $configPath : [];

        return [
            'api_key' => trim((string) (getenv('RESEND_API_KEY') ?: ($fileConfig['api_key'] ?? ''))),
            'from' => trim((string) (getenv('RESEND_FROM_EMAIL') ?: ($fileConfig['from'] ?? 'SolarPower Energy Corporation <solar@solarpower.com.ph>'))),
            'reply_to' => trim((string) (getenv('RESEND_REPLY_TO') ?: ($fileConfig['reply_to'] ?? 'solar@solarpower.com.ph'))),
            'timeout' => (int) (getenv('RESEND_TIMEOUT') ?: ($fileConfig['timeout'] ?? 25)),
            'connect_timeout' => (int) (getenv('RESEND_CONNECT_TIMEOUT') ?: ($fileConfig['connect_timeout'] ?? 8)),
        ];
    }
}

if (!function_exists('solar_send_resend_email')) {
    function solar_send_resend_email(string $to, string $subject, string $html, array $options = []): array
    {
        $config = solar_resend_config();
        $to = trim($to);

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'provider' => 'resend', 'message' => 'Invalid recipient email.'];
        }

        if ($config['api_key'] === '') {
            return ['success' => false, 'provider' => 'resend', 'message' => 'Missing RESEND_API_KEY.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'provider' => 'resend', 'message' => 'PHP cURL extension is unavailable.'];
        }

        $payload = [
            'from' => $options['from'] ?? $config['from'],
            'to' => [$to],
            'subject' => $subject,
            'html' => $html,
        ];

        $replyTo = $options['reply_to'] ?? $config['reply_to'];
        if ($replyTo !== '') {
            $payload['reply_to'] = $replyTo;
        }

        $timeout = max(10, min(60, (int) ($config['timeout'] ?? 25)));
        $connectTimeout = max(3, min($timeout, (int) ($config['connect_timeout'] ?? 8)));

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config['api_key'],
            'Content-Type: application/json',
            'User-Agent: SolarPower-Website/1.0',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false || $curlError !== '') {
            return ['success' => false, 'provider' => 'resend', 'message' => $curlError ?: 'Unable to connect to Resend.'];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'provider' => 'resend', 'message' => 'Email sent through Resend.'];
        }

        return [
            'success' => false,
            'provider' => 'resend',
            'message' => 'Resend API returned HTTP ' . $httpCode . '.',
            'response' => $responseBody,
        ];
    }
}

if (!function_exists('solar_internal_lead_recipients')) {
    function solar_internal_lead_recipients(): array
    {
        return [
            'solar@solarpower.com.ph',
            'ddc@solarpower.com.ph',
        ];
    }
}

if (!function_exists('solar_send_internal_lead_email')) {
    function solar_send_internal_lead_email(string $subject, string $html, array $options = []): array
    {
        $recipients = $options['recipients'] ?? solar_internal_lead_recipients();
        $recipients = array_values(array_unique(array_filter(array_map('trim', $recipients))));

        if (empty($recipients)) {
            return ['success' => false, 'provider' => 'resend', 'message' => 'No internal lead recipients configured.'];
        }

        $sentCount = 0;
        $failures = [];

        foreach ($recipients as $recipient) {
            $result = solar_send_resend_email($recipient, $subject, $html, $options);
            if (!empty($result['success'])) {
                $sentCount++;
                continue;
            }

            $failures[] = $recipient . ': ' . ($result['message'] ?? 'Unknown Resend error');
        }

        if (empty($failures)) {
            return [
                'success' => true,
                'provider' => 'resend',
                'message' => 'Email sent through Resend to ' . $sentCount . ' internal recipient(s).',
            ];
        }

        return [
            'success' => false,
            'provider' => 'resend',
            'message' => 'Sent to ' . $sentCount . ' recipient(s); failed for ' . implode('; ', $failures),
        ];
    }
}
