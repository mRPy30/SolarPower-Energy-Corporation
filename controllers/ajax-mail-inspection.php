<?php
header('Content-Type: application/json');

/* ---------- CONFIG ---------- */
$myEmail = 'solar@solarpower.com.ph';   // ← your address
$subject = 'New Solar Inspection Request';

/* ---------- GET & VALIDATE ---------- */
$data = $_POST;
foreach (['fullname','phone','email','address'] as $key){
    if (empty($data[$key])){
        http_response_code(400);
        exit(json_encode(['success'=>false,'msg'=>'Missing required field: '.$key]));
    }
}

/* ---------- BUILD HTML TABLE ---------- */
$rows = '';
foreach ($data as $k => $v){
    $v = htmlspecialchars(trim($v));
    $k = ucfirst($k);
    $rows .= "
      <tr>
        <td style='padding:8px 12px;border:1px solid #ddd;background:#f9f9f9;width:30%;'><strong>{$k}</strong></td>
        <td style='padding:8px 12px;border:1px solid #ddd;'>{$v}</td>
      </tr>";
}

$html = "
<html>
<head><style>body{font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333}</style></head>
<body>
  <h2 style='color:#0d6efd'>Solar Inspection Request</h2>
  <table style='width:100%;border-collapse:collapse;margin-top:15px'>{$rows}</table>
  <p style='margin-top:25px;font-size:12px;color:#888'>This mail was sent from your website booking form.</p>
</body>
</html>";

/* ---------- HEADERS ---------- */
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: {$data['fullname']} <{$data['email']}>\r\n";

/* ---------- SEND ---------- */
$sent = mail($myEmail, $subject, $html, $headers);
echo json_encode([
    'success' => $sent,
    'msg'     => $sent ? 'Request sent successfully.' : 'Sorry, an error occurred. Please try again.'
]);