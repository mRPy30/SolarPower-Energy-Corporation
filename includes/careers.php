<?php
require_once __DIR__ . '/../config/db_pdo.php';
require_once __DIR__ . '/routes.php';

function career_escape($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function career_now(): string { return (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s'); }
function career_today(): string { return substr(career_now(), 0, 10); }
function career_token(): string {
    if (empty($_SESSION['career_csrf'])) $_SESSION['career_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['career_csrf'];
}
function career_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(career_token(), $token)) career_json(['success'=>false,'message'=>'Your session expired. Reload the page and try again.'],403);
}
function career_json(array $data, int $status=200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function career_staff(): void {
    if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['staff','admin'],true)) career_json(['success'=>false,'message'=>'Staff sign-in required.'],401);
    $stmt=getPDO()->prepare("SELECT id FROM staff WHERE id=? AND (status IS NULL OR status <> 'Inactive')");
    $stmt->execute([(int)$_SESSION['user_id']]);
    if (!$stmt->fetchColumn()) career_json(['success'=>false,'message'=>'Staff access is unavailable.'],403);
}
function career_text(array $data,string $key,int $max,bool $required=true): string {
    $value=$data[$key] ?? '';
    if (!is_string($value)) throw new InvalidArgumentException('Invalid '.$key.'.');
    $value=trim($value);
    if (($required && $value==='') || mb_strlen($value)>$max || strpos($value,"\0")!==false) throw new InvalidArgumentException('Please check '.str_replace('_',' ',$key).' (maximum '.$max.' characters).');
    return $value;
}
function career_job_fields(array $data): array {
    $job=[];
    foreach (['title'=>160,'department'=>100,'employment_type'=>40,'location'=>160,'summary'=>500,'description'=>20000,'responsibilities'=>12000,'qualifications'=>12000,'skills'=>12000,'benefits'=>12000] as $key=>$max) {
        $job[$key]=career_text($data,$key,$max,!in_array($key,['department','responsibilities','skills','benefits'],true));
    }
    if (!in_array($job['employment_type'],['Full-time','Part-time','Contract','Internship','Temporary'],true)) throw new InvalidArgumentException('Choose an employment type.');
    $job['status']=career_text($data,'status',20);
    if (!in_array($job['status'],['draft','published','archived'],true)) throw new InvalidArgumentException('Invalid job status.');
    $deadline=career_text($data,'deadline',10,false);
    if ($deadline!=='') {
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$deadline);
        if (!$date || $date->format('Y-m-d')!==$deadline) throw new InvalidArgumentException('Enter a valid deadline.');
        if ($job['status']==='published' && $deadline<career_today()) throw new InvalidArgumentException('Published jobs need a current or future deadline.');
    }
    $job['deadline']=$deadline ?: null;
    return $job;
}
function career_resume(array $file): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) throw new InvalidArgumentException('Please upload a resume (PDF, DOC or DOCX, up to 50 MB).');
    $path=$file['tmp_name'];
    $size=filesize($path);
    if ($size<1 || $size>50*1024*1024) throw new InvalidArgumentException('Your resume must be between 1 byte and 50 MB.');
    $name=basename(str_replace('\\','/',(string)$file['name']));
    $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
    $bytes=file_get_contents($path);
    $valid=false;
    if ($ext==='pdf') $valid=$mime==='application/pdf' && substr($bytes,0,5)==='%PDF-';
    if ($ext==='doc') $valid=in_array($mime,['application/msword','application/x-ole-storage','application/CDFV2'],true) && substr($bytes,0,8)===hex2bin('d0cf11e0a1b11ae1') && strpos($bytes,mb_convert_encoding('WordDocument','UTF-16LE','UTF-8'))!==false;
    if ($ext==='docx' && in_array($mime,['application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],true)) {
        // PHP's built-in Phar reads ZIP structure without extracting any uploaded files.
        try {
            $zip=new PharData($path,0,null,Phar::ZIP);
            if (isset($zip['[Content_Types].xml'],$zip['word/document.xml']) && !isset($zip['word/vbaProject.bin'])) {
                $total=0; $entries=0;
                foreach (new RecursiveIteratorIterator($zip) as $entry) { $total+=$entry->getSize(); $entries++; if ($total>100*1024*1024 || $entries>1000) throw new RuntimeException('Archive too large.'); }
                $types=$zip['[Content_Types].xml']->getContent();
                $valid=strpos($types,'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml')!==false && stripos($types,'macroEnabled')===false;
            }
        } catch (Throwable $e) { $valid=false; }
    }
    if (!$valid) throw new InvalidArgumentException('The resume content does not match a supported PDF, DOC or DOCX file.');
    $types=['pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $name=preg_replace('/[^a-zA-Z0-9._ -]/','_', $name);
    return ['name'=>substr($name,0,180),'mime'=>$types[$ext],'size'=>$size,'data'=>$bytes];
}
function career_hr_recipients(): array {
    $raw=getenv('CAREERS_HR_EMAIL') ?: 'solar@solarpower.com.ph';
    $emails=array_values(array_unique(array_filter(array_map('trim',explode(',',$raw)))));
    foreach ($emails as $email) if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Invalid Careers HR email configuration.');
    if (!$emails) throw new RuntimeException('Missing Careers HR recipient.');
    return $emails;
}
function career_send_emails(PDO $db,int $applicationId): void {
    require_once __DIR__.'/resend-mailer.php';
    $stmt=$db->prepare('SELECT id,position_title,full_name,email,phone,message,resume_name,resume_size,created_at FROM career_applications WHERE id=?');
    $stmt->execute([$applicationId]); $app=$stmt->fetch();
    if (!$app) return;
    $stmt=$db->prepare("SELECT * FROM career_email_deliveries WHERE application_id=? AND status IN ('pending','failed')");
    $stmt->execute([$applicationId]);
    foreach ($stmt->fetchAll() as $delivery) {
        $claim=$db->prepare("UPDATE career_email_deliveries SET status='sending',attempts=attempts+1,updated_at=? WHERE id=? AND status IN ('pending','failed')");
        $claim->execute([career_now(),$delivery['id']]);
        if (!$claim->rowCount()) continue;
        $e=array_map('career_escape',$app);
        $body=$delivery['audience']==='hr'
            ? '<h2>New career application</h2><p><strong>'.$e['position_title'].'</strong></p><p>Applicant: '.$e['full_name'].'<br>Email: '.$e['email'].'<br>Phone: '.$e['phone'].'<br>Submitted: '.$e['created_at'].' (Philippine time)</p><h3>Message</h3><p>'.nl2br($e['message']).'</p><p>Resume: '.$e['resume_name'].' ('.number_format($app['resume_size']/1024).' KB)</p><p>Sign in to the SolarPower staff dashboard and open Career Management to securely download the resume. Application #'.$e['id'].'.</p>'
            : '<h2>Thank you for applying, '.$e['full_name'].'.</h2><p>We have successfully received your application for <strong>'.$e['position_title'].'</strong> at SolarPower Energy Corporation.</p><p>Our team will review your information. If your qualifications match our current requirements, we will contact you.</p><p>Application reference: #'.$e['id'].'</p>';
        $html='<div style="font-family:Arial,sans-serif;color:#333;line-height:1.7;max-width:640px;margin:auto"><div style="background:#0a5c3d;color:white;padding:24px;border-bottom:5px solid #ffc107">SolarPower Energy Corporation</div><div style="padding:24px">'.$body.'</div></div>';
        try {
            $result=solar_send_resend_email($delivery['recipient'],($delivery['audience']==='hr'?'New application: ':'Application received: ').$app['position_title'],$html,['reply_to'=>$delivery['audience']==='hr'?$app['email']:solar_resend_config()['reply_to']]);
            $state=!empty($result['success'])?'sent':'failed';
            $error=$state==='failed'?substr($result['message'] ?? 'Email delivery failed.',0,255):null;
        } catch (Throwable $e) { $state='failed'; $error='Email service unavailable.'; error_log('Careers notification: '.$e->getMessage()); }
        $db->prepare('UPDATE career_email_deliveries SET status=?,last_error=?,updated_at=? WHERE id=?')->execute([$state,$error,career_now(),$delivery['id']]);
    }
}
