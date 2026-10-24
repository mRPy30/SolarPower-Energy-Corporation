<?php
// Retired, unreferenced endpoint used an incompatible schema and browser totals.
header('Content-Type: application/json');
http_response_code(410);
echo json_encode(['success'=>false,'message'=>'Please place your order through Checkout.']);
