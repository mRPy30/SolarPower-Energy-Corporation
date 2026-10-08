<?php
session_start();
require_once __DIR__.'/includes/careers.php';
header('Cache-Control: no-store');
$jobs=[]; $selected=null; $unavailable=false;
try {
    $stmt=getPDO()->prepare("SELECT * FROM career_jobs WHERE status='published' AND (deadline IS NULL OR deadline>=?) ORDER BY created_at DESC,id DESC");
    $stmt->execute([career_today()]); $jobs=$stmt->fetchAll();
    foreach ($jobs as $job) if ((string)$job['id']===(string)($_GET['job'] ?? '')) $selected=$job;
} catch (Throwable $e) { error_log('Careers public: '.$e->getMessage()); $unavailable=true; }
$invalidJob=isset($_GET['job']) && !$selected;
if ($invalidJob && !$unavailable) http_response_code(404);
$csrf=career_token();
$requestKey=bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $selected ? career_escape($selected['title']).' | ' : '' ?>Careers | SolarPower Energy Corporation</title>
<meta name="description" content="Build your career in solar energy. Explore current opportunities and apply to join SolarPower Energy Corporation.">
<link rel="icon" href="<?= career_escape(asset_url('assets/img/icon.png')) ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= career_escape(asset_url('assets/style.css')) ?>">
<link rel="stylesheet" href="<?= career_escape(asset_url('assets/careers.css')) ?>">
</head>
<body>
<?php include __DIR__.'/includes/header.php'; ?>
<main class="careers-public">
<section class="careers-hero">
 <video class="careers-hero-video" id="careersHeroVideo" autoplay muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1">
  <source src="<?= career_escape(asset_url('assets/img/drone-shot.mp4')) ?>" type="video/mp4">
 </video>
 <div class="careers-hero-overlay" aria-hidden="true"></div>
 <button type="button" class="careers-video-toggle" id="careersVideoToggle" aria-controls="careersHeroVideo" hidden>Pause background video</button>
 <div class="container careers-hero-grid">
  <div><p class="careers-kicker">Careers at SolarPower</p><h1>Build a career.<br>Power a cleaner future.</h1><p>Bring your skills to a team helping Filipino homes and businesses make the switch to solar energy.</p><a class="btn careers-primary" href="#openings">Explore open positions</a></div>
  <aside class="careers-purpose"><span aria-hidden="true" class="careers-sun"></span><h2>Good work.<br>Real-world impact.</h2><p>From planning and customer support to installation, every role contributes to the energy systems our communities rely on.</p><p class="careers-purpose-sign">SolarPower Energy Corporation</p></aside>
 </div>
</section>
<section class="container careers-content" id="openings">
 <div class="careers-section-heading"><div><h2>Find your next opportunity</h2><p>Explore where your experience can make a difference.</p></div><span><?= count($jobs) ?> open <?= count($jobs)===1?'position':'positions' ?></span></div>
 <?php if ($unavailable): ?><div role="alert" class="alert alert-warning">We’re unable to load opportunities right now. Please check again shortly.</div><?php endif; ?>
 <?php if ($invalidJob && !$unavailable): ?><div role="alert" class="alert alert-info">This position is no longer available. Explore our current openings below.</div><?php endif; ?>
 <?php if (!$jobs && !$unavailable): ?><div class="careers-empty"><h3>No open positions right now</h3><p>Thank you for your interest in SolarPower. Check back here for future opportunities.</p></div><?php endif; ?>
 <?php if ($jobs): ?>
 <div class="careers-filter"><label for="careerSearch">Search openings</label><input id="careerSearch" type="search" class="form-control" placeholder="Job title or location"><p id="careerCount" role="status" class="small mb-0"></p></div>
 <div class="careers-job-list">
 <?php foreach ($jobs as $job): ?>
  <article class="careers-job" data-search="<?= career_escape(mb_strtolower($job['title'].' '.$job['location'])) ?>">
   <div><h3><a href="<?= career_escape(clean_url('careers.php').'?job='.$job['id'].'#job-detail') ?>"><?= career_escape($job['title']) ?></a></h3><p class="careers-job-meta"><?= career_escape($job['employment_type']) ?> <span aria-hidden="true">·</span> <?= career_escape($job['location']) ?></p><p><?= career_escape($job['summary']) ?></p></div>
   <a class="btn careers-outline" href="<?= career_escape(clean_url('careers.php').'?job='.$job['id'].'#job-detail') ?>" aria-label="View and apply for <?= career_escape($job['title']) ?>">View &amp; apply</a>
  </article>
 <?php endforeach; ?>
 </div>
 <p id="careerNoMatches" hidden>No openings match your search. Try another title or location.</p>
 <?php endif; ?>
</section>
<?php if ($selected): ?>
<section class="careers-detail-section" id="job-detail">
 <div class="container careers-detail-grid">
  <article class="careers-job-detail">
   <a href="<?= career_escape(clean_url('careers.php').'#openings') ?>">All openings</a>
   <h2><?= career_escape($selected['title']) ?></h2>
   <p class="careers-job-meta"><?= career_escape($selected['employment_type']) ?> · <?= career_escape($selected['location']) ?></p>
   <?php if ($selected['deadline']): ?><p>Apply by <?= career_escape(date('F j, Y',strtotime($selected['deadline']))) ?> (Philippine time)</p><?php endif; ?>
   <?php foreach (['description'=>'About the role','responsibilities'=>'Responsibilities','qualifications'=>'Qualifications','skills'=>'Preferred skills','benefits'=>'Benefits'] as $field=>$label): if (trim($selected[$field])==='') continue; ?>
    <section><h3><?= $label ?></h3>
     <?php if ($field === 'qualifications'): ?>
      <ul class="careers-prose"><?php foreach (preg_split('/\\r?\\n/', $selected[$field]) as $qualification): if (trim($qualification) === '') continue; ?><li><?= career_escape($qualification) ?></li><?php endforeach; ?></ul>
     <?php else: ?><div class="careers-prose"><?= career_escape($selected[$field]) ?></div><?php endif; ?>
    </section>
   <?php endforeach; ?>
   <a class="btn careers-primary" href="#career-application">Apply now</a>
  </article>
  <aside>
   <form id="career-application" class="careers-application" method="post" enctype="multipart/form-data" action="<?= career_escape(asset_url('controllers/careers-apply.php')) ?>">
    <h2>Your next chapter starts here</h2><p>Tell us about yourself and share your CV. All fields are required unless marked optional.</p>
    <input type="hidden" name="csrf" value="<?= career_escape($csrf) ?>"><input type="hidden" name="request_key" value="<?= career_escape($requestKey) ?>"><input type="hidden" name="job_id" value="<?= (int)$selected['id'] ?>">
    <div class="careers-trap" aria-hidden="true"><label>Company website<input name="company_website" tabindex="-1" autocomplete="off"></label></div>
    <label for="career-name">Full name</label><input class="form-control" id="career-name" name="full_name" maxlength="160" autocomplete="name" required>
    <label for="career-email">Email address</label><input class="form-control" id="career-email" name="email" type="email" maxlength="254" autocomplete="email" required>
    <label for="career-phone">Phone number</label><input class="form-control" id="career-phone" name="phone" type="tel" maxlength="32" autocomplete="tel" required>
    <label for="career-position">Position applied for</label><input class="form-control" id="career-position" value="<?= career_escape($selected['title']) ?>" readonly>
    <label for="career-message">Cover letter / message <span>(optional)</span></label><textarea class="form-control" id="career-message" name="message" rows="5" maxlength="12000"></textarea>
    <label for="career-resume">Resume / CV</label><input class="form-control" id="career-resume" type="file" name="resume" accept=".pdf,.doc,.docx" aria-describedby="resume-help" required><p id="resume-help" class="small">PDF, DOC or DOCX. Maximum 5 MB. Available only to authorized staff.</p>
    <label class="careers-consent"><input type="checkbox" name="consent" value="1" required><span>I agree that SolarPower may process my information to review this application, as described in the <a href="<?= career_escape(clean_url('privacy-policy.php')) ?>" target="_blank" rel="noopener">Privacy Policy (opens in a new tab)</a>.</span></label>
    <div id="careerFormError" class="alert alert-danger" role="alert" tabindex="-1" hidden></div>
    <button type="submit" class="btn careers-primary w-100">Submit application</button><p class="small mt-3 mb-0" id="careerSubmitStatus" role="status"></p>
    <noscript><p>Please enable JavaScript to submit your application and receive on-page confirmation.</p></noscript>
   </form>
  </aside>
 </div>
</section>
<?php endif; ?>
<section class="container careers-process"><h2>A clear path forward</h2><div><article><h3>Apply</h3><p>Choose a role that fits your skills and send your application.</p></article><article><h3>Review</h3><p>Our team reviews your experience against the role’s requirements.</p></article><article><h3>Connect</h3><p>If your qualifications match, we’ll contact you about the next steps.</p></article></div></section>
</main>
<div class="modal fade" id="careerSuccess" tabindex="-1" aria-labelledby="careerSuccessTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-4" id="careerSuccessTitle">Application received</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p id="careerSuccessMessage"></p></div><div class="modal-footer"><button type="button" class="btn careers-primary" data-bs-dismiss="modal">Got it, thank you</button></div></div></div></div>
<?php include __DIR__.'/includes/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="<?= career_escape(asset_url('assets/careers.js')) ?>"></script>
</body></html>
