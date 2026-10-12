<?php
session_start();
require_once __DIR__.'/../includes/careers.php';
header('Cache-Control: private, no-store');
$id=filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) { http_response_code(400); exit('Invalid application.'); }
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['staff','admin'],true)) {
    $_SESSION['career_resume_return']=$id;
    header('Location: '.clean_url('views/login.php'));
    exit;
}
career_staff();
header('Location: '.clean_url('views/staff/dashboard.php').'?career_application='.$id.'#careers');
exit;
