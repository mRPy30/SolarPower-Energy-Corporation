(() => {
 'use strict';
 const video=document.getElementById('careersHeroVideo');
 const videoToggle=document.getElementById('careersVideoToggle');
 if(video && videoToggle) {
  const reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)');
  const syncVideo=()=>{videoToggle.textContent=video.paused?'Play background video':'Pause background video';};
  videoToggle.hidden=false;
  video.addEventListener('play',syncVideo);
  video.addEventListener('pause',syncVideo);
  video.addEventListener('error',()=>{videoToggle.hidden=true;});
  videoToggle.addEventListener('click',()=>{if(video.paused)video.play().catch(syncVideo);else video.pause();});
  const respectMotion=()=>{if(reducedMotion.matches)video.pause();syncVideo();};
  reducedMotion.addEventListener('change',respectMotion);
  respectMotion();
 }
 const search=document.getElementById('careerSearch');
 if(search) search.addEventListener('input',()=>{
  let visible=0; const term=search.value.toLocaleLowerCase().trim();
  document.querySelectorAll('.careers-job').forEach(card=>{card.hidden=!card.dataset.search.includes(term); if(!card.hidden) visible++;});
  document.getElementById('careerCount').textContent=visible+' matching positions';
  document.getElementById('careerNoMatches').hidden=visible!==0;
 });
 const form=document.getElementById('career-application');
 if(!form) return;
 form.addEventListener('submit',async event=>{
  event.preventDefault();
  const error=document.getElementById('careerFormError'), button=form.querySelector('[type=submit]'), status=document.getElementById('careerSubmitStatus');
  error.hidden=true;
  const file=form.elements.resume.files[0];
  if(!file || !/\.(pdf|docx?)$/i.test(file.name) || file.size>5*1024*1024 || !file.size) {
   error.textContent='Choose a PDF, DOC or DOCX resume up to 5 MB.';error.hidden=false;error.focus();return;
  }
  button.disabled=true;button.textContent='Submitting…';status.textContent='Please wait while we securely save your application.';
  try {
   const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}});
   const data=await response.json();
   if(!response.ok || !data.success) throw new Error(data.message || 'Unable to submit. Please try again.');
   const message=document.getElementById('careerSuccessMessage');message.textContent=data.message;
   status.textContent='Application received. Thank you for applying.';
   button.textContent='Application received';
   form.querySelectorAll('input,textarea').forEach(input=>input.disabled=true);
   if(window.bootstrap) bootstrap.Modal.getOrCreateInstance(document.getElementById('careerSuccess')).show();
   else { status.tabIndex=-1;status.focus(); }
  } catch(err) {
   error.textContent=err instanceof SyntaxError?'The server could not process the upload. Please try again.':err.message;
   error.hidden=false;error.focus();status.textContent='';button.disabled=false;button.textContent='Submit application';
  }
 });
})();
