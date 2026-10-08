(() => {
 'use strict';
 const root=document.getElementById('careers'); if(!root) return;
 const $=id=>document.getElementById(id);
 const esc=value=>String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 let jobs=[],page=1,total=0,active='jobs',loaded=false,applicationId=null;
 const message=text=>$('careerAdminMessage').textContent=text;
 async function api(action,data=null,params={}) {
  const url=new URL(root.dataset.endpoint,location.origin);
  const options={headers:{Accept:'application/json'}};
  if(data) { data.set('action',action);data.set('csrf',root.dataset.csrf);options.method='POST';options.body=data; }
  else {url.searchParams.set('action',action);Object.entries(params).forEach(([k,v])=>url.searchParams.set(k,v));}
  const response=await fetch(url,options);
  const result=await response.json();
  if(!response.ok || !result.success) throw new Error(result.message||'Request failed.');
  return result;
 }
 const todayPH=()=>{const parts=new Intl.DateTimeFormat('en-US',{timeZone:'Asia/Manila',year:'numeric',month:'2-digit',day:'2-digit'}).formatToParts(new Date());return ['year','month','day'].map(type=>parts.find(p=>p.type===type).value).join('-');};
 const jobState=j=>j.status==='published'&&j.deadline&&j.deadline<todayPH()?'expired':j.status;
 const stateLabel={published:'Open',draft:'Draft',expired:'Expired',archived:'Archived'};
 function renderJobs() {
  const term=$('careerJobSearch').value.trim().toLocaleLowerCase(),status=$('careerJobStatus').value;
  const visible=jobs.filter(j=>(!status||jobState(j)===status)&&[j.title,j.location].join(' ').toLocaleLowerCase().includes(term));
  $('careerJobSummary').innerHTML=[['Open jobs',jobs.filter(j=>jobState(j)==='published').length],['Drafts',jobs.filter(j=>j.status==='draft').length],['Active applications',jobs.reduce((sum,j)=>sum+Number(j.application_count),0)]].map(([label,count])=>'<div><span>'+label+'</span><strong>'+count+'</strong></div>').join('');
  $('careerJobCount').textContent=visible.length+' of '+jobs.length+' jobs';
  $('careerJobsList').innerHTML=visible.length?'<table><thead><tr><th>Job</th><th>Status</th><th>Applications</th><th>Closing date</th><th>Actions</th></tr></thead><tbody>'+visible.map(j=>'<tr><td><button type="button" class="career-job-title" data-edit-job="'+Number(j.id)+'">'+esc(j.title)+'</button><small>'+esc(j.employment_type)+'<br>'+esc(j.location)+'</small></td><td><span class="career-status career-status--'+jobState(j)+'">'+stateLabel[jobState(j)]+'</span></td><td><strong class="career-application-count">'+Number(j.application_count)+'</strong><small>active candidates</small></td><td>'+esc(j.deadline||'No closing date')+'</td><td><button type="button" data-edit-job="'+Number(j.id)+'" aria-label="Manage '+esc(j.title)+'">Manage job</button></td></tr>').join('')+'</tbody></table>':'<div class="career-empty-workspace"><h4>'+(!jobs.length?'Your next hire starts here':'No matching jobs')+'</h4><p>'+(!jobs.length?'Create a job ad and publish it when you’re ready to receive applications.':'Try another search or status filter.')+'</p></div>';
 }
 async function loadJobs() {const data=await api('jobs');jobs=data.jobs;renderJobs();}
 $('careerJobSearch').addEventListener('input',renderJobs);
 $('careerJobStatus').addEventListener('change',renderJobs);
 async function loadApplications() {
  const params=Object.fromEntries(new FormData($('careerApplicationFilters')));params.page=page;
  const data=await api('applications',null,params);total=data.total;
  $('careerApplicationsList').innerHTML=data.applications.length?'<table><thead><tr><th>Applicant</th><th>Position</th><th>Contact</th><th>Submitted (PH)</th><th>Status</th><th>Review</th></tr></thead><tbody>'+data.applications.map(a=>'<tr><td>'+esc(a.full_name)+'</td><td>'+esc(a.position_title)+'</td><td>'+esc(a.email)+'<small>'+esc(a.phone)+'</small></td><td>'+esc(a.created_at)+'</td><td>'+esc(a.status)+'</td><td><button type="button" data-application="'+Number(a.id)+'" aria-label="Review '+esc(a.full_name)+'">Open</button></td></tr>').join('')+'</tbody></table>':'<p>No applications match these filters.</p>';
  $('careerPagination').textContent='Page '+page+' of '+Math.max(1,Math.ceil(total/30))+' · '+total+' applications';
  $('careerPrevious').disabled=page<=1;$('careerNext').disabled=page*30>=total;
 }
 async function refresh() { message('Loading…');try {if(active==='jobs') await loadJobs();else await loadApplications();message('');loaded=true;}catch(e){message(e.message);} }
 function switchTab(tab) {active=tab;$('careerJobsPanel').hidden=tab!=='jobs';$('careerApplicationsPanel').hidden=tab!=='applications';$('careerJobsTab').setAttribute('aria-pressed',String(tab==='jobs'));$('careerApplicationsTab').setAttribute('aria-pressed',String(tab==='applications'));refresh();}
 $('careerJobsTab').onclick=()=>switchTab('jobs');$('careerApplicationsTab').onclick=()=>switchTab('applications');$('careerRefresh').onclick=refresh;
 let editorStep=0,qualificationCounter=0;
 const jobForm=$('careerJobForm');
 function syncQualifications() {
  jobForm.elements.qualifications.value=Array.from($('careerQualificationsList').querySelectorAll('textarea')).map(el=>el.value.trim()).filter(Boolean).join('\n');
 }
 function addQualification(value='',focus=false) {
  const row=document.createElement('div');row.className='career-qualification-row';
  const label=document.createElement('label');label.textContent='Qualification';
  const input=document.createElement('textarea');input.rows=2;input.maxLength=12000;input.required=true;input.value=value;
  input.id='careerQualification'+(++qualificationCounter);label.htmlFor=input.id;
  input.placeholder='e.g. Experience in solar sales or a related technical field';
  input.addEventListener('input',syncQualifications);label.append(input);
  const remove=document.createElement('button');remove.type='button';remove.textContent='Remove';remove.setAttribute('aria-label','Remove this qualification');
  remove.onclick=()=>{const next=row.nextElementSibling||row.previousElementSibling;row.remove();if(!$('careerQualificationsList').children.length)addQualification('',true);else next?.querySelector('textarea').focus();syncQualifications();};
  row.append(label,remove);$('careerQualificationsList').append(row);if(focus)input.focus();
 }
 $('careerAddQualification').onclick=()=>addQualification('',true);
 function previewJob() {
  syncQualifications();const fields=Object.fromEntries(new FormData(jobForm));
  $('careerJobPreview').innerHTML='<p class="career-preview-label">Candidate preview</p><h3>'+esc(fields.title||'Job title')+'</h3><p>'+esc(fields.employment_type)+' · '+esc(fields.location)+'</p><p>'+esc(fields.summary)+'</p>'+
   [['description','About the role'],['responsibilities','Responsibilities'],['qualifications','Qualifications'],['skills','Preferred skills'],['benefits','Benefits']].filter(([key])=>fields[key]?.trim()).map(([key,label])=>'<section><h4>'+label+'</h4>'+(key==='qualifications'?'<ul>'+fields[key].split('\n').map(line=>'<li>'+esc(line)+'</li>').join('')+'</ul>':'<div class="career-preview-prose">'+esc(fields[key])+'</div>')+'</section>').join('');
 }
 function setEditorStep(step,focus=true) {
  editorStep=Math.max(0,Math.min(3,step));previewJob();
  root.querySelectorAll('[data-career-panel]').forEach(panel=>{panel.hidden=Number(panel.dataset.careerPanel)!==editorStep;});
  root.querySelectorAll('[data-career-step]').forEach(button=>{if(Number(button.dataset.careerStep)===editorStep)button.setAttribute('aria-current','step');else button.removeAttribute('aria-current');});
  $('careerEditorBack').disabled=editorStep===0;
  $('careerEditorNext').hidden=editorStep===3;$('careerSaveJob').hidden=editorStep!==3;
  $('careerSaveJob').textContent=jobForm.elements.status.value==='published'?'Save & publish':'Save job';
  $('careerEditorProgress').textContent='Step '+(editorStep+1)+' of 4';
  if(focus){const heading=root.querySelector('[data-career-panel="'+editorStep+'"] h4');heading.tabIndex=-1;heading.focus();}
 }
 function validatePanel(panel) {
  const invalid=Array.from(panel.querySelectorAll('input,textarea,select')).find(el=>!el.checkValidity());
  if(invalid){setEditorStep(Number(panel.dataset.careerPanel),false);invalid.reportValidity();return false;}
  return true;
 }
 root.querySelectorAll('[data-career-step]').forEach(button=>button.onclick=()=>setEditorStep(Number(button.dataset.careerStep)));
 $('careerEditorBack').onclick=()=>setEditorStep(editorStep-1);
 $('careerEditorNext').onclick=()=>{if(validatePanel(root.querySelector('[data-career-panel="'+editorStep+'"]')))setEditorStep(editorStep+1);};
 jobForm.elements.status.addEventListener('change',()=>setEditorStep(editorStep,false));
 function editJob(job) {
  jobForm.reset();jobForm.querySelector('.career-form-error').textContent='';
  Array.from(jobForm.elements).forEach(el=>{if(el.name)el.value=job?.[el.name]??(el.name==='employment_type'?'Full-time':el.name==='status'?'draft':'');});
  $('careerQualificationsList').replaceChildren();
  const entries=String(job?.qualifications||'').split(/\r?\n/).filter(line=>line.trim());
  (entries.length?entries:['']).forEach(entry=>addQualification(entry));
  $('careerJobEditorTitle').textContent=job?'Edit job posting':'Create a job';
  setEditorStep(0,false);$('careerJobEditor').showModal();jobForm.elements.title.focus();
 }
 $('careerNewJob').onclick=()=>editJob(null);
 $('careerJobsList').onclick=e=>{const button=e.target.closest('[data-edit-job]');if(button)editJob(jobs.find(j=>Number(j.id)===Number(button.dataset.editJob)));};
 root.querySelectorAll('[data-career-close]').forEach(button=>button.onclick=()=>button.closest('dialog').close());
 jobForm.onsubmit=async e=>{
  e.preventDefault();
  if(editorStep!==3){$('careerEditorNext').click();return;}
  syncQualifications();
  for(const panel of root.querySelectorAll('[data-career-panel]'))if(!validatePanel(panel))return;
  if(jobForm.elements.qualifications.value.length>12000){setEditorStep(2);jobForm.querySelector('.career-form-error').textContent='Please keep qualifications within 12,000 characters in total.';return;}
  const button=$('careerSaveJob');button.disabled=true;
  try {await api('save_job',new FormData(jobForm));$('careerJobEditor').close();await loadJobs();message('Job saved.');}
  catch(err){jobForm.querySelector('.career-form-error').textContent=err.message;}
  finally{button.disabled=false;}
 };
 async function application(id,open=true) {
  const data=await api('application',null,{id});const a=data.application;applicationId=a.id;
  $('careerApplicantDetails').innerHTML='<h4>'+esc(a.full_name)+'</h4><p><strong>'+esc(a.position_title)+'</strong></p><p>'+esc(a.email)+'<br>'+esc(a.phone)+'<br>Submitted '+esc(a.created_at)+' (Philippine time)</p><h4>Cover letter / message</h4><div class="career-applicant-message">'+esc(a.message||'No message provided.')+'</div><p><a href="'+esc(root.dataset.resume+'?id='+Number(a.id))+'">Download '+esc(a.resume_name)+'</a> ('+Math.ceil(a.resume_size/1024)+' KB)</p>';
  const form=$('careerApplicationForm');form.elements.id.value=a.id;form.elements.status.value=a.status;form.elements.internal_notes.value=a.internal_notes;form.elements.archived.checked=!!a.archived_at;form.querySelector('.career-form-error').textContent='';
  $('careerEmailStatus').innerHTML='<ul>'+data.emails.map(mail=>'<li>'+esc(mail.audience)+' · '+esc(mail.recipient)+' — <strong>'+esc(mail.status)+'</strong>'+ (mail.last_error?'<br>'+esc(mail.last_error):'')+'</li>').join('')+'</ul>';
  if(open)$('careerApplicationEditor').showModal();
 }
 $('careerApplicationsList').onclick=async e=>{const button=e.target.closest('[data-application]');if(button)try{await application(button.dataset.application);}catch(err){message(err.message);}};
 $('careerApplicationForm').onsubmit=async e=>{
  e.preventDefault();const form=e.currentTarget,button=form.querySelector('[type=submit]');button.disabled=true;
  try{await api('update_application',new FormData(form));$('careerApplicationEditor').close();await loadApplications();message('Application updated.');}
  catch(err){form.querySelector('.career-form-error').textContent=err.message;}
  finally{button.disabled=false;}
 };
 $('careerRetryEmail').onclick=async e=>{
  const button=e.currentTarget;button.disabled=true;
  try{const data=new FormData();data.set('id',applicationId);await api('retry_email',data);await application(applicationId,false);}
  catch(err){$('careerApplicationForm').querySelector('.career-form-error').textContent=err.message;}
  finally{button.disabled=false;}
 };
 $('careerApplicationFilters').onsubmit=e=>{e.preventDefault();page=1;refresh();};
 $('careerPrevious').onclick=()=>{if(page>1){page--;refresh();}};$('careerNext').onclick=()=>{if(page*30<total){page++;refresh();}};
 new MutationObserver(()=>{if(root.classList.contains('active')&&!loaded){loaded=true;refresh();}}).observe(root,{attributes:true,attributeFilter:['class']});
 if(root.classList.contains('active'))refresh();
})();
