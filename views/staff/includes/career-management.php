<?php
require_once __DIR__.'/../../../includes/careers.php';
?>
<link rel="stylesheet" href="<?= career_escape(asset_url('assets/careers-admin.css') . '?v=' . filemtime(__DIR__.'/../../../assets/careers-admin.css')) ?>">
<section id="careers" class="page-content career-admin" data-endpoint="<?= career_escape(asset_url('controllers/careers-admin.php')) ?>" data-resume="<?= career_escape(asset_url('controllers/careers-resume.php')) ?>" data-csrf="<?= career_escape(career_token()) ?>">
 <div class="career-admin-heading"><div><h2>Your hiring workspace</h2><p>Create a clear job ad. Find the right people for SolarPower.</p></div><a href="<?= career_escape(clean_url('careers.php')) ?>" target="_blank" rel="noopener">View Careers page <span class="small">(new tab)</span></a></div>
 <div class="career-admin-toolbar"><button type="button" id="careerJobsTab" aria-pressed="true">Job postings</button><button type="button" id="careerApplicationsTab" aria-pressed="false">Applications</button><button type="button" id="careerRefresh">Refresh</button></div>
 <p id="careerAdminMessage" role="status"></p>
 <section id="careerJobsPanel">
  <div class="career-workspace-summary" id="careerJobSummary" aria-label="Job posting summary"></div>
  <div class="career-jobs-heading"><div><h3>Manage jobs</h3><p>Keep your vacancies and applications moving.</p></div><button type="button" class="career-action-primary" id="careerNewJob">+ Create a job</button></div>
  <div class="career-job-filters"><label>Find a job<input id="careerJobSearch" type="search" placeholder="Search job title or location"></label><label>Job status<select id="careerJobStatus"><option value="">All jobs</option><option value="published">Open</option><option value="draft">Draft</option><option value="expired">Expired</option><option value="archived">Archived</option></select></label><span id="careerJobCount" role="status"></span></div>
  <div id="careerJobsList" class="career-table-wrap"></div>
 </section>
 <section id="careerApplicationsPanel" hidden>
  <form id="careerApplicationFilters" class="career-admin-filters">
   <label>Search applicants / positions<input name="search" type="search" maxlength="160"></label>
   <label>Status<select name="status"><option value="">All statuses</option><?php foreach (['New','Reviewing','Shortlisted','Interview','Hired','Rejected'] as $status): ?><option><?= $status ?></option><?php endforeach; ?></select></label>
   <label>View<select name="archived"><option value="0">Active applications</option><option value="1">Archived applications</option></select></label>
   <button type="submit">Filter</button>
  </form>
  <div id="careerApplicationsList" class="career-table-wrap"></div><div class="career-admin-toolbar"><button type="button" id="careerPrevious">Previous</button><span id="careerPagination"></span><button type="button" id="careerNext">Next</button></div>
 </section>
 <dialog id="careerJobEditor" aria-labelledby="careerJobEditorTitle">
  <div class="career-dialog-heading"><div><p class="career-editor-context">SolarPower · Employer workspace</p><h3 id="careerJobEditorTitle">Create a job</h3></div><button type="button" data-career-close aria-label="Close job editor">×</button></div>
  <nav class="career-editor-steps" aria-label="Job ad sections">
   <button type="button" data-career-step="0" aria-current="step">Job basics</button>
   <button type="button" data-career-step="1">Job description</button>
   <button type="button" data-career-step="2">Qualifications</button>
   <button type="button" data-career-step="3">Review &amp; publish</button>
  </nav>
  <form id="careerJobForm" novalidate>
   <input type="hidden" name="id">
   <section class="career-editor-panel" data-career-panel="0">
    <h4>Start with the essentials</h4><p class="career-editor-help">Help candidates find your role with a specific title and location.</p>
    <div class="career-form-grid">
     <label>Job title<input name="title" maxlength="160" placeholder="e.g. Solar Sales Officer" required></label>
     <label>Employment type<select name="employment_type" required><?php foreach (['Full-time','Part-time','Contract','Internship','Temporary'] as $type): ?><option><?= $type ?></option><?php endforeach; ?></select></label>
     <label>Location<input name="location" maxlength="160" placeholder="e.g. Muntinlupa City" required></label>
    </div>
    <label>Short description<textarea name="summary" maxlength="500" rows="3" required placeholder="Write a short introduction that helps candidates understand the opportunity."></textarea><small>Appears in the public job listing. Up to 500 characters.</small></label>
   </section>
   <section class="career-editor-panel" data-career-panel="1" hidden>
    <h4>Tell candidates about the role</h4><p class="career-editor-help">Explain the work, your team, and what success looks like.</p>
    <label>Job description<textarea name="description" maxlength="20000" rows="8" required placeholder="Introduce the role and the impact this person will have."></textarea></label>
    <label>Responsibilities <span class="career-optional">Optional</span><textarea name="responsibilities" maxlength="12000" rows="5" placeholder="List the key day-to-day responsibilities, if needed."></textarea><small>Leave blank if responsibilities are already covered in the description.</small></label>
    <label>Benefits <span class="career-optional">Optional</span><textarea name="benefits" maxlength="12000" rows="4" placeholder="Share the benefits you offer for this role."></textarea></label>
   </section>
   <section class="career-editor-panel" data-career-panel="2" hidden>
    <h4>What does the right candidate bring?</h4><p class="career-editor-help" id="careerQualificationsHelp">Add one qualification per item: relevant experience, education, a licence, or a practical skill. These appear in your job ad, not as applicant screening questions.</p>
    <input type="hidden" name="qualifications">
    <div id="careerQualificationsList" aria-describedby="careerQualificationsHelp"></div>
    <button type="button" id="careerAddQualification" class="career-add-requirement">+ Add qualification</button>
    <p class="small">At least one qualification is required. Maximum 12,000 characters in total.</p>
    <label>Preferred skills <span class="career-optional">Optional</span><textarea name="skills" maxlength="12000" rows="4" placeholder="Nice-to-have skills that are not essential to apply."></textarea></label>
   </section>
   <section class="career-editor-panel" data-career-panel="3" hidden>
    <h4>Review your job ad</h4><p class="career-editor-help">Check how the role reads, then choose whether to save a draft or publish.</p>
    <div class="career-form-grid">
     <label>Posting status<select name="status"><option value="draft">Draft — not visible to candidates</option><option value="published">Published — accept applications</option><option value="archived">Archived — keep for your records</option></select></label>
     <label>Application deadline <span class="career-optional">Optional</span><input type="date" name="deadline"><small>Closes at the end of this date in Philippine time.</small></label>
    </div>
    <article id="careerJobPreview" class="career-job-preview" aria-label="Job ad preview"></article>
   </section>
   <div class="career-editor-actions"><p class="career-form-error" role="alert"></p><div><button type="button" id="careerEditorBack">Back</button><span id="careerEditorProgress">Step 1 of 4</span><button type="button" class="career-action-primary" id="careerEditorNext">Continue</button><button class="career-action-primary" type="submit" id="careerSaveJob" hidden>Save job</button></div></div>
  </form>
 </dialog>
 <dialog id="careerApplicationEditor" aria-labelledby="careerApplicationEditorTitle">
  <div class="career-dialog-heading"><h3 id="careerApplicationEditorTitle">Application details</h3><button type="button" data-career-close aria-label="Close application details">×</button></div>
  <div id="careerApplicantDetails"></div>
  <form id="careerApplicationForm">
   <input type="hidden" name="id">
   <label>Application status<select name="status"><?php foreach (['New','Reviewing','Shortlisted','Interview','Hired','Rejected'] as $status): ?><option><?= $status ?></option><?php endforeach; ?></select></label>
   <label>Internal notes<textarea name="internal_notes" maxlength="20000" rows="5"></textarea></label>
   <label class="career-checkbox"><input name="archived" type="checkbox" value="1"> Archive this application (uncheck to restore)</label>
   <p class="career-form-error" role="alert"></p><button class="career-action-primary" type="submit">Save application</button>
  </form>
  <div class="career-email-panel"><h4>Email notifications</h4><div id="careerEmailStatus"></div><button type="button" id="careerRetryEmail">Retry pending / failed emails</button><p class="small">Sent means accepted by Resend, not confirmed inbox delivery. Interrupted sends can be retried after 10 minutes; a retry may duplicate an email if the provider accepted it before a timeout.</p></div>
 </dialog>
</section>
<script src="<?= career_escape(asset_url('assets/careers-admin.js') . '?v=' . filemtime(__DIR__.'/../../../assets/careers-admin.js')) ?>" defer></script>
