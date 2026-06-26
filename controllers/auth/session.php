<?php
require_once __DIR__ . '/../../includes/client-auth.php';

header('Content-Type: application/json');

echo json_encode([
    'logged_in' => client_auth_is_logged_in(),
    'client' => client_auth_session_payload(),
]);
