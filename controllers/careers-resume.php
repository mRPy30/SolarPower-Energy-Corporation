<?php
session_start();
require_once __DIR__.'/../includes/careers.php';
try {
    career_staff();
    $stmt=getPDO()->prepare('SELECT resume_name,resume_mime,resume_size FROM career_applications WHERE id=?');
    $stmt->execute([(int)($_GET['id'] ?? 0)]); $file=$stmt->fetch();
    if (!$file) { http_response_code(404); exit('Resume not found.'); }
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: sandbox; default-src 'none'");
    header('Content-Type: '.$file['resume_mime']);
    header('Content-Length: '.$file['resume_size']);
    header('Content-Disposition: attachment; filename="'.str_replace(['"', "\r", "\n"],'_', $file['resume_name']).'"');
    $chunks=getPDO()->prepare('SELECT content FROM career_resume_chunks WHERE application_id=? ORDER BY chunk_index');
    $chunks->execute([(int)$_GET['id']]);
    while (($chunk=$chunks->fetchColumn()) !== false) echo $chunk;
} catch (Throwable $e) { error_log('Careers resume: '.$e->getMessage()); http_response_code(503); echo 'Resume temporarily unavailable.'; }
