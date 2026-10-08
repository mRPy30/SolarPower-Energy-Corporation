<?php
session_start();
require_once __DIR__.'/../includes/careers.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') career_json(['success'=>false,'message'=>'POST required.'],405);
career_csrf();
try {
    $name=career_text($_POST,'full_name',160);
    $email=strtolower(career_text($_POST,'email',254));
    $phone=career_text($_POST,'phone',32);
    $message=career_text($_POST,'message',12000,false);
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
    if (!preg_match('/^[+0-9 ()-]{7,32}$/',$phone) || strlen(preg_replace('/\D/','',$phone))<7) throw new InvalidArgumentException('Enter a valid phone number.');
    if (($_POST['consent'] ?? '')!=='1') throw new InvalidArgumentException('Please agree to the application privacy notice.');
    if (!empty($_POST['company_website'])) throw new InvalidArgumentException('Unable to submit this application.');
    $request=career_text($_POST,'request_key',64);
    if (!preg_match('/^[a-f0-9]{64}$/',$request)) throw new InvalidArgumentException('Reload the application form.');
    // Bind the idempotency token to this browser session.
    $key=hash('sha256',session_id().$request);
    $db=getPDO();
    $exists=$db->prepare('SELECT id FROM career_applications WHERE request_key=?'); $exists->execute([$key]);
    if ($exists->fetchColumn()) career_json(['success'=>true,'message'=>'Your application has already been received.']);
    $ipHash=hash('sha256',$_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $rate=$db->prepare('SELECT COUNT(*) FROM career_applications WHERE (ip_hash=? OR email=?) AND created_at>=?');
    $rate->execute([$ipHash,$email,(new DateTimeImmutable(career_now()))->modify('-1 hour')->format('Y-m-d H:i:s')]);
    if ((int)$rate->fetchColumn()>=5) career_json(['success'=>false,'message'=>'Too many applications. Please try again in one hour.'],429);
    $resume=career_resume($_FILES['resume'] ?? []);
    $jobId=filter_var($_POST['job_id'] ?? '',FILTER_VALIDATE_INT);
    $db->beginTransaction();
    $stmt=$db->prepare("SELECT id,title FROM career_jobs WHERE id=? AND status='published' AND (deadline IS NULL OR deadline>=?) FOR UPDATE");
    $stmt->execute([$jobId,career_today()]); $job=$stmt->fetch();
    if (!$job) throw new InvalidArgumentException('This position is no longer accepting applications.');
    $now=career_now();
    $stmt=$db->prepare('INSERT INTO career_applications (job_id,position_title,full_name,email,phone,message,internal_notes,resume_name,resume_mime,resume_size,request_key,ip_hash,created_at,updated_at) VALUES (?,?,?,?,?,?,?, ?,?,?,?,?,?,?)');
    $stmt->execute([$job['id'],$job['title'],$name,$email,$phone,$message,'',$resume['name'],$resume['mime'],$resume['size'],$key,$ipHash,$now,$now]);
    $id=(int)$db->lastInsertId();
    $chunkInsert=$db->prepare('INSERT INTO career_resume_chunks (application_id,chunk_index,content) VALUES (?,?,?)');
    foreach (str_split($resume['data'],256*1024) as $index=>$chunk) $chunkInsert->execute([$id,$index,$chunk]);
    $recipients=career_hr_recipients();
    $queue=$db->prepare('INSERT INTO career_email_deliveries (application_id,audience,recipient,updated_at) VALUES (?,?,?,?)');
    foreach ($recipients as $recipient) $queue->execute([$id,'hr',$recipient,$now]);
    $queue->execute([$id,'applicant',$email,$now]);
    $db->commit();
    session_write_close();
    // Stored applications remain successful even if the email provider is temporarily unavailable.
    try { career_send_emails($db,$id); } catch (Throwable $e) { error_log('Careers mail queue: '.$e->getMessage()); }
    career_json(['success'=>true,'message'=>'Thank you for applying to SolarPower Energy Corporation. We have successfully received your application and our team will review your information. If your qualifications match our current requirements, we will contact you.']);
} catch (InvalidArgumentException $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    career_json(['success'=>false,'message'=>$e->getMessage()],422);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log('Careers application: '.$e->getMessage());
    career_json(['success'=>false,'message'=>'We could not save your application. Please try again shortly.'],503);
}
