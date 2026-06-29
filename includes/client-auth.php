<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function client_auth_is_logged_in(): bool
{
    return (isset($_SESSION['client_id']) && intval($_SESSION['client_id']) > 0) || (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0);
}

function client_auth_session_payload(): array
{
    return [
        'client_id' => $_SESSION['client_id'] ?? $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['client_email'] ?? $_SESSION['user_email'] ?? '',
        'firstName' => $_SESSION['client_firstName'] ?? $_SESSION['first_name'] ?? '',
        'lastName' => $_SESSION['client_lastName'] ?? $_SESSION['last_name'] ?? '',
        'contact_number' => $_SESSION['client_contact_number'] ?? '',
        'address' => $_SESSION['client_address'] ?? '',
    ];
}

function client_auth_sync(mysqli $conn, array $profile): int
{
    $email = trim(strtolower($profile['email'] ?? ''));
    $firstName = trim($profile['firstName'] ?? '');
    $lastName = trim($profile['lastName'] ?? '');

    if ($email === '') {
        throw new RuntimeException('OAuth profile did not include an email address.');
    }

    $stmt = $conn->prepare('SELECT id, email, firstName, lastName, contact_number, address FROM client WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$client) {
        $contactNumber = 0;
        $address = '';
        $stmt = $conn->prepare('INSERT INTO client (email, firstName, lastName, password, contact_number, address) VALUES (?, ?, ?, NULL, ?, ?)');
        $stmt->bind_param('sssis', $email, $firstName, $lastName, $contactNumber, $address);
        $stmt->execute();
        $clientId = $conn->insert_id;
        $stmt->close();

        $client = [
            'id' => $clientId,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'contact_number' => $contactNumber,
            'address' => $address,
        ];
    }

    $_SESSION['client_id'] = intval($client['id']);
    $_SESSION['client_email'] = $client['email'];
    $_SESSION['client_firstName'] = $client['firstName'];
    $_SESSION['client_lastName'] = $client['lastName'];
    $_SESSION['client_contact_number'] = $client['contact_number'];
    $_SESSION['client_address'] = $client['address'];

    // Sync with requested user session keys
    $_SESSION['user_id'] = intval($client['id']);
    $_SESSION['user_email'] = $client['email'];
    $_SESSION['first_name'] = $client['firstName'];
    $_SESSION['last_name'] = $client['lastName'];

    return intval($client['id']);
}

function client_auth_require_json(): void
{
    if (!client_auth_is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'requires_auth' => true,
            'message' => 'Please continue with Google or Facebook before checkout.',
        ]);
        exit;
    }
}
