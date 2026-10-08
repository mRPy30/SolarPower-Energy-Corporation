<?php
session_start();
require_once __DIR__.'/../includes/careers.php';
try {
    career_staff();
    $db=getPDO();
    if ($_SERVER['REQUEST_METHOD']==='GET') {
        $action=$_GET['action'] ?? 'jobs';
        if ($action==='jobs') {
            $jobs=$db->query('SELECT j.*, (SELECT COUNT(*) FROM career_applications a WHERE a.job_id=j.id AND a.archived_at IS NULL) AS application_count FROM career_jobs j ORDER BY j.created_at DESC,j.id DESC')->fetchAll();
            career_json(['success'=>true,'jobs'=>$jobs]);
        }
        if ($action==='applications') {
            $archived=($_GET['archived'] ?? '')==='1';
            $page=max(1,(int)($_GET['page'] ?? 1)); $offset=($page-1)*30;
            $where=$archived?'archived_at IS NOT NULL':'archived_at IS NULL'; $args=[];
            $search=substr((string)($_GET['search'] ?? ''),0,160);
            if ($search!=='') { $where.=' AND (full_name LIKE ? OR email LIKE ? OR position_title LIKE ?)'; $args=array_fill(0,3,'%'.$search.'%'); }
            if (!empty($_GET['status'])) { $where.=' AND status=?'; $args[]=(string)$_GET['status']; }
            $count=$db->prepare('SELECT COUNT(*) FROM career_applications WHERE '.$where); $count->execute($args);
            $stmt=$db->prepare('SELECT id,position_title,full_name,email,phone,status,created_at,archived_at FROM career_applications WHERE '.$where.' ORDER BY id DESC LIMIT 30 OFFSET '.$offset); $stmt->execute($args);
            career_json(['success'=>true,'applications'=>$stmt->fetchAll(),'total'=>(int)$count->fetchColumn(),'page'=>$page]);
        }
        if ($action==='application') {
            $stmt=$db->prepare('SELECT id,job_id,position_title,full_name,email,phone,message,status,internal_notes,archived_at,resume_name,resume_size,created_at FROM career_applications WHERE id=?');
            $stmt->execute([(int)($_GET['id'] ?? 0)]); $app=$stmt->fetch();
            if (!$app) career_json(['success'=>false,'message'=>'Application not found.'],404);
            $stmt=$db->prepare('SELECT audience,recipient,status,attempts,last_error FROM career_email_deliveries WHERE application_id=?'); $stmt->execute([$app['id']]);
            career_json(['success'=>true,'application'=>$app,'emails'=>$stmt->fetchAll()]);
        }
        career_json(['success'=>false,'message'=>'Unknown action.'],400);
    }
    if ($_SERVER['REQUEST_METHOD']!=='POST') career_json(['success'=>false,'message'=>'POST required.'],405);
    career_csrf();
    $action=$_POST['action'] ?? '';
    if ($action==='save_job') {
        $job=career_job_fields($_POST); $id=(int)($_POST['id'] ?? 0); $now=career_now();
        if ($id) {
            if (!array_key_exists('department', $_POST)) unset($job['department']);
            $check=$db->prepare('SELECT id FROM career_jobs WHERE id=?'); $check->execute([$id]);
            if (!$check->fetchColumn()) career_json(['success'=>false,'message'=>'Job not found.'],404);
            $sets=implode(',',array_map(static function($k){ return $k.'=?'; },array_keys($job)));
            $db->prepare('UPDATE career_jobs SET '.$sets.',updated_at=? WHERE id=?')->execute(array_merge(array_values($job),[$now,$id]));
        } else {
            $db->prepare('INSERT INTO career_jobs ('.implode(',',array_keys($job)).',created_at,updated_at) VALUES ('.implode(',',array_fill(0,count($job)+2,'?')).')')->execute(array_merge(array_values($job),[$now,$now]));
            $id=(int)$db->lastInsertId();
        }
        career_json(['success'=>true,'id'=>$id,'message'=>'Job saved.']);
    }
    if ($action==='update_application') {
        $status=career_text($_POST,'status',30);
        if (!in_array($status,['New','Reviewing','Shortlisted','Interview','Hired','Rejected'],true)) throw new InvalidArgumentException('Invalid application status.');
        $notes=career_text($_POST,'internal_notes',20000,false);
        $id=(int)($_POST['id'] ?? 0);
        $check=$db->prepare('SELECT id FROM career_applications WHERE id=?'); $check->execute([$id]);
        if (!$check->fetchColumn()) career_json(['success'=>false,'message'=>'Application not found.'],404);
        $db->prepare('UPDATE career_applications SET status=?,internal_notes=?,archived_at=?,updated_at=? WHERE id=?')->execute([$status,$notes,($_POST['archived'] ?? '')==='1'?career_now():null,career_now(),$id]);
        career_json(['success'=>true,'message'=>'Application updated.']);
    }
    if ($action==='retry_email') {
        $id=(int)($_POST['id'] ?? 0);
        // Recover a worker interrupted before recording a delivery result; allow ten minutes first.
        $db->prepare("UPDATE career_email_deliveries SET status='failed',last_error='Previous send interrupted' WHERE application_id=? AND status='sending' AND updated_at<?")->execute([$id,(new DateTimeImmutable(career_now()))->modify('-10 minutes')->format('Y-m-d H:i:s')]);
        session_write_close();
        career_send_emails($db,$id);
        career_json(['success'=>true,'message'=>'Pending/failed notifications retried. Check delivery status below.']);
    }
    career_json(['success'=>false,'message'=>'Unknown action.'],400);
} catch (InvalidArgumentException $e) { career_json(['success'=>false,'message'=>$e->getMessage()],422);
} catch (Throwable $e) { error_log('Careers admin: '.$e->getMessage()); career_json(['success'=>false,'message'=>'Career Management is unavailable. Check the database migration and server log.'],503); }
