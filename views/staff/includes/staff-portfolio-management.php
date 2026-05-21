<?php
/**
 * staff-portfolio-management.php
 * Content Management System (CMS) for the "Recent Installations" Projects portfolio.
 */
?>
<div id="portfolio" class="page-content portfolio-management-page">
<style>
  .portfolio-management-page {
    --sun: #F5A623;
    --solar: #1A3C5E;
    --solar-mid: #2D6A9F;
    --surface: #F7F9FC;
    --card: #FFFFFF;
    --border: #E2E8F0;
    --text: #1A202C;
    --muted: #718096;
    --success: #38A169;
    --danger: #E53E3E;
    --radius: 14px;
    --shadow: 0 4px 20px rgba(26,60,94,.09);
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
  }

  .portfolio-management-page .pm-container {
    padding: 24px;
  }
  
  .portfolio-management-page .section-label {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .72rem;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 18px;
  }

  /* ── List View (Main) ── */
  .pm-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
  .pm-stat-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow); display: flex; align-items: center; gap: 16px; }
  .pm-stat-icon { width: 48px; height: 48px; border-radius: 12px; background: #EBF4FF; color: var(--solar-mid); display: grid; place-items: center; font-size: 1.4rem; }
  .pm-stat-info h3 { font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0; }
  .pm-stat-info p { font-size: .8rem; color: var(--muted); margin: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

  .pm-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
  .pm-btn-primary { background: var(--sun); color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
  .pm-btn-primary:hover { filter: brightness(1.05); transform: translateY(-1px); }
  
  .pm-table-wrapper { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
  .pm-table { width: 100%; border-collapse: collapse; }
  .pm-table th { background: #f8fafc; padding: 14px 20px; text-align: left; font-size: .75rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
  .pm-table td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: .9rem; vertical-align: middle; }
  .pm-table tr:last-child td { border-bottom: none; }
  .pm-table-img { width: 60px; height: 40px; border-radius: 6px; object-fit: cover; }
  .pm-status-badge { padding: 4px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; }
  .pm-status-badge.completed { background: #F0FFF4; color: var(--success); }
  .pm-status-badge.maintenance { background: #FFF3DC; color: #b7791f; }
  .pm-action-btn { background: none; border: none; color: var(--muted); cursor: pointer; padding: 6px; transition: 0.2s; font-size: 1rem; }
  .pm-action-btn:hover { color: var(--solar-mid); }
  .pm-action-btn.delete:hover { color: var(--danger); }

  /* ── POPUP MODAL STYLES ── */
  .pm-modal-overlay {
    position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7);
    z-index: 9999; display: flex; justify-content: center; align-items: center;
    opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
    backdrop-filter: blur(4px);
  }
  .pm-modal-overlay.active { opacity: 1; pointer-events: auto; }
  
  .pm-modal-content {
    background: #f8fafc; width: 95%; max-width: 1200px; max-height: 90vh;
    border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    display: flex; flex-direction: column; overflow: hidden;
    transform: translateY(20px) scale(0.98); transition: transform 0.3s ease;
  }
  .pm-modal-overlay.active .pm-modal-content { transform: translateY(0) scale(1); }

  .pm-modal-header {
    background: #fff; padding: 16px 24px; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
  }
  .pm-modal-header h2 { margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--text); }
  .pm-modal-close { background: none; border: none; font-size: 1.5rem; color: var(--muted); cursor: pointer; transition: 0.2s; }
  .pm-modal-close:hover { color: var(--danger); transform: rotate(90deg); }

  .pm-modal-body {
    padding: 24px; overflow-y: auto; flex: 1;
    display: grid; grid-template-columns: 1fr 400px; gap: 30px;
  }
  @media (max-width: 950px) { .pm-modal-body { grid-template-columns: 1fr; } }

  /* ── Form View Inside Modal ── */
  .pm-form-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 20px; }
  .pm-form-group { margin-bottom: 18px; }
  .pm-form-group label { display: block; font-size: .75rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
  .pm-form-control { width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border); font-size: .9rem; font-family: inherit; transition: border-color .2s; background: var(--surface); }
  .pm-form-control:focus { outline: none; border-color: var(--solar-mid); background: #fff; }
  .pm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

  /* Image Upload Zone */
  .pm-drop-zone { border: 2px dashed var(--border); border-radius: 10px; padding: 24px 16px; text-align: center; cursor: pointer; transition: 0.2s; position: relative; background: var(--surface); }
  .pm-drop-zone:hover, .pm-drop-zone.dragover { border-color: var(--solar-mid); background: #EBF4FF; }
  .pm-drop-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
  
  /* ── Live Preview Styles ── */
  .preview-panel { position: sticky; top: 0; align-self: start; }
  .preview-shell { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; }
  .preview-topbar { background: #f0f2f5; padding: 10px 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border); }
  .preview-topbar-dot { width: 8px; height: 8px; border-radius: 50%; }
  .preview-topbar-label { margin-left: auto; font-size: .7rem; font-weight: 600; color: var(--muted); letter-spacing: .8px; text-transform: uppercase; }
  .preview-body { padding: 0; background: #f8fafc; display: flex; justify-content: center; }
  
  /* Exact CSS Cloned from projects.php */
  .live-project-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; border: 1px solid rgba(0, 0, 0, 0.04); width: 100%; margin: 20px; cursor: default; }
  .live-card-img-panel { position: relative; height: 180px; overflow: hidden; }
  .live-card-img-panel img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
  .live-card-img-panel::after { content: ''; position: absolute; bottom: -15px; left: -10%; right: -10%; height: 40px; background: #fff; transform: rotate(-3deg); z-index: 2; }
  .live-card-info-panel { padding: 20px 24px 28px; background: #fff; position: relative; z-index: 3; }
  .live-card-project-title { font-size: 1.15rem; font-weight: 900; color: #1b262c; text-transform: uppercase; letter-spacing: 0.02em; margin: 0 0 4px 0; }
  .live-card-project-subtitle { font-size: 0.65rem; font-weight: 700; color: var(--sun); letter-spacing: 0.15em; text-transform: uppercase; margin: 0 0 16px 0; }
  .live-project-detail-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
  .live-project-detail-item { display: flex; align-items: flex-start; gap: 12px; }
  .live-detail-icon-wrap { width: 28px; height: 28px; border-radius: 50%; background: rgba(10, 92, 61, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .live-detail-icon-wrap i { font-size: 0.8rem; color: #0a5c3d; }
  .live-detail-text-wrap { display: flex; flex-direction: column; }
  .live-detail-value { font-size: 0.9rem; font-weight: 800; color: #1b262c; }
  .live-detail-label { font-size: 0.6rem; font-weight: 600; text-transform: uppercase; color: #7a8c95; letter-spacing: 0.05em; }

</style>

<div class="pm-container">
  
  <!-- MAIN LIST VIEW -->
  <div id="portfolio-list-view">
    <div class="section-label">Portfolio Overview</div>
    
    <div class="pm-stats-grid">
      <div class="pm-stat-card">
        <div class="pm-stat-icon"><i class="fas fa-project-diagram"></i></div>
        <div class="pm-stat-info">
          <h3 id="stat-total">0</h3>
          <p>Total Projects</p>
        </div>
      </div>
      <div class="pm-stat-card">
        <div class="pm-stat-icon" style="color: #38A169; background: #F0FFF4;"><i class="fas fa-home"></i></div>
        <div class="pm-stat-info">
          <h3 id="stat-residential">0</h3>
          <p>Residential</p>
        </div>
      </div>
      <div class="pm-stat-card">
        <div class="pm-stat-icon" style="color: #D69E2E; background: #FFFFF0;"><i class="fas fa-city"></i></div>
        <div class="pm-stat-info">
          <h3 id="stat-commercial">0</h3>
          <p>Commercial</p>
        </div>
      </div>
    </div>

    <div class="pm-toolbar">
      <div class="pm-form-group" style="margin: 0; width: 300px;">
        <input type="text" class="pm-form-control" placeholder="Search projects...">
      </div>
      <button class="pm-btn-primary" onclick="openPortfolioModal()">
        <i class="fas fa-plus"></i> Add New Project
      </button>
    </div>

    <div class="pm-table-wrapper">
      <table class="pm-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Project Name</th>
            <th>Location</th>
            <th>System Type</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody id="portfolio-table-body">
          <tr><td colspan="6" style="text-align:center;">Loading projects...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div> <!-- End container -->

<!-- THE POPUP MODAL -->
<div class="pm-modal-overlay" id="portfolioModal">
  <div class="pm-modal-content">
    
    <div class="pm-modal-header">
      <h2 id="portfolio-form-title">Add New Project</h2>
      <button class="pm-modal-close" onclick="closePortfolioModal()"><i class="fas fa-times"></i></button>
    </div>

    <div class="pm-modal-body">
      
      <!-- Left: Input Form -->
      <div>
        <form id="portfolio-form" onsubmit="savePortfolioProject(event)">
          <input type="hidden" id="pf-id" name="id">
          <input type="hidden" id="pf-action" name="action" value="save">
          
          <div class="pm-form-card">
            <h4 style="margin: 0 0 16px 0; font-size: 1rem;">Core Details</h4>
            <div class="pm-form-group">
              <label>Project Name / Title</label>
              <input type="text" id="pf-title" name="project_name" class="pm-form-control" placeholder="e.g. BF HOMES PARAÑAQUE" required oninput="updateLivePreview()">
            </div>
            
            <div class="pm-form-row">
              <div class="pm-form-group">
                <label>Location</label>
                <input type="text" id="pf-location" name="location" class="pm-form-control" placeholder="e.g. Parañaque City" required oninput="updateLivePreview()">
              </div>
              <div class="pm-form-group">
                <label>Status</label>
                <select id="pf-status" name="status" class="pm-form-control" onchange="updateLivePreview()">
                  <option value="Completed">Completed</option>
                  <option value="Maintenance">Preventive Maintenance</option>
                  <option value="Ongoing">Ongoing</option>
                </select>
              </div>
            </div>
            
            <div class="pm-form-group">
              <label>Project Category Subtitle</label>
              <input type="text" id="pf-subtitle" name="subtitle" class="pm-form-control" placeholder="e.g. RESIDENTIAL INSTALLATION" required oninput="updateLivePreview()">
            </div>
          </div>

          <div class="pm-form-card">
            <h4 style="margin: 0 0 16px 0; font-size: 1rem;">Performance Metrics</h4>
            <div class="pm-form-row">
              <div class="pm-form-group">
                <label>System Capacity & Type</label>
                <input type="text" id="pf-system" name="system_type" class="pm-form-control" placeholder="e.g. 12kW Hybrid" required oninput="autoGenerateMetrics()">
              </div>
              <div class="pm-form-group">
                <label>CO2 Emissions Saved (t)</label>
                <input type="text" id="pf-co2" name="co2_reduction" class="pm-form-control" placeholder="e.g. 470.80 t" required oninput="updateLivePreview()">
              </div>
            </div>
            <div class="pm-form-row">
              <div class="pm-form-group">
                <label>Equivalent Trees Planted</label>
                <input type="text" id="pf-efficiency" name="efficiency_rate" class="pm-form-control" placeholder="e.g. 14.10 K" oninput="updateLivePreview()">
              </div>
            </div>
          </div>

          <div class="pm-form-card">
            <h4 style="margin: 0 0 16px 0; font-size: 1rem;">Project Images (Max 10)</h4>
            <div class="pm-drop-zone" id="pf-drop-zone">
              <input type="file" id="pf-image" name="images[]" accept="image/*" multiple onchange="handlePortfolioImageUpload(this)">
              <div style="font-size: 2rem; color: var(--muted); margin-bottom: 10px;"><i class="fas fa-cloud-upload-alt"></i></div>
              <div style="font-size: .85rem; color: var(--muted);"><strong id="pf-img-label">Click or drag</strong> to upload up to 10 files<br>JPG, PNG, WebP — max 5MB each</div>
            </div>
          </div>

          <button type="submit" class="pm-btn-primary" id="pf-save-btn" style="width: 100%; justify-content: center; font-size: 1rem; padding: 14px;">
            <i class="fas fa-save"></i> Save Project Configuration
          </button>
        </form>
      </div>

      <!-- Right: Live Preview Panel -->
      <div class="preview-panel">
        <div class="section-label" style="margin: 0 0 12px 0;">Live Website Preview</div>
        <div class="preview-shell">
          <div class="preview-topbar">
            <div class="preview-topbar-dot" style="background:#ff5f57"></div>
            <div class="preview-topbar-dot" style="background:#febc2e"></div>
            <div class="preview-topbar-dot" style="background:#28c840"></div>
            <span class="preview-topbar-label">Public Card View</span>
          </div>
          <div class="preview-body">
            
            <div class="live-project-card">
              <div class="live-card-img-panel">
                  <img id="live-img" src="../../assets/img/product-placeholder.png" alt="Project Image">
              </div>
              <div class="live-card-info-panel">
                  <div>
                      <h4 class="live-card-project-title" id="live-title">PROJECT TITLE</h4>
                      <p class="live-card-project-subtitle" id="live-subtitle">CATEGORY</p>
                  </div>
                  <ul class="live-project-detail-list">
                      <li class="live-project-detail-item">
                          <div class="live-detail-icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
                          <div class="live-detail-text-wrap">
                              <span class="live-detail-value" id="live-location">-</span>
                              <span class="live-detail-label">Location</span>
                          </div>
                      </li>
                      <li class="live-project-detail-item">
                          <div class="live-detail-icon-wrap"><i class="fas fa-solar-panel"></i></div>
                          <div class="live-detail-text-wrap">
                              <span class="live-detail-value" id="live-system">-</span>
                              <span class="live-detail-label">System Size</span>
                          </div>
                      </li>
                      <li class="live-project-detail-item">
                          <div class="live-detail-icon-wrap"><i class="fas fa-smog"></i></div>
                          <div class="live-detail-text-wrap">
                              <span class="live-detail-value" id="live-co2">-</span>
                              <span class="live-detail-label">CO₂ Emissions Saved</span>
                          </div>
                      </li>
                      <li class="live-project-detail-item">
                          <div class="live-detail-icon-wrap"><i class="fas fa-tree"></i></div>
                          <div class="live-detail-text-wrap">
                              <span class="live-detail-value" id="live-efficiency">-</span>
                              <span class="live-detail-label">Equivalent Trees Planted</span>
                          </div>
                      </li>
                  </ul>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</div> <!-- End page-content -->

<script>
/**
 * CMS Frontend State & Logic (Popup + MySQL Integration)
 */
let projectsDb = [];

async function fetchPortfolioProjects() {
  try {
    const res = await fetch('../../controllers/portfolio_api.php');
    const json = await res.json();
    if (json.status === 'success') {
      projectsDb = json.data;
      renderPortfolioTable();
    } else {
      console.error(json.message);
    }
  } catch(e) {
    console.error("Error fetching projects", e);
  }
}

function renderPortfolioTable() {
  const tbody = document.getElementById('portfolio-table-body');
  tbody.innerHTML = '';
  
  if (projectsDb.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No projects found. Add one above!</td></tr>';
  }

  let resCount = 0;
  let comCount = 0;

  projectsDb.forEach(proj => {
    if(proj.subtitle && proj.subtitle.toLowerCase().includes('commercial')) comCount++;
    else resCount++;

    let badgeClass = proj.status === 'Completed' ? 'completed' : 'maintenance';
    
    // Parse images JSON array
    let images = [];
    try {
      images = JSON.parse(proj.image_url);
      if (!Array.isArray(images)) images = [proj.image_url];
    } catch(e) {
      images = [proj.image_url];
    }
    let firstImg = images[0] || 'assets/img/product-placeholder.png';
    let imageSrc = (firstImg.startsWith('uploads') || firstImg.startsWith('assets')) ? '../../' + firstImg : firstImg;
    
    tbody.innerHTML += `
      <tr>
        <td><img src="${imageSrc}" class="pm-table-img"></td>
        <td><strong>${proj.project_name}</strong></td>
        <td>${proj.location}</td>
        <td>${proj.system_type}</td>
        <td><span class="pm-status-badge ${badgeClass}">${proj.status}</span></td>
        <td style="text-align: right;">
          <button class="pm-action-btn" onclick="editProject('${proj.id}')"><i class="fas fa-edit"></i></button>
          <button class="pm-action-btn delete" onclick="deleteProject('${proj.id}')"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
    `;
  });

  document.getElementById('stat-total').innerText = projectsDb.length;
  document.getElementById('stat-residential').innerText = resCount;
  document.getElementById('stat-commercial').innerText = comCount;
}

/* Modal Toggles */
function openPortfolioModal() {
  document.getElementById('portfolio-form').reset();
  document.getElementById('pf-id').value = '';
  document.getElementById('portfolio-form-title').innerText = 'Add New Project';
  document.getElementById('live-img').src = '../../assets/img/product-placeholder.png';
  document.getElementById('pf-img-label').innerHTML = 'Click or drag';
  updateLivePreview();
  
  document.getElementById('portfolioModal').classList.add('active');
  document.body.style.overflow = 'hidden'; // prevent background scrolling
}

function closePortfolioModal() {
  document.getElementById('portfolioModal').classList.remove('active');
  document.body.style.overflow = '';
}

function updateLivePreview() {
  const title = document.getElementById('pf-title').value || 'PROJECT TITLE';
  const subtitle = document.getElementById('pf-subtitle').value || 'CATEGORY';
  const loc = document.getElementById('pf-location').value || '-';
  const sys = document.getElementById('pf-system').value || '-';
  const co2 = document.getElementById('pf-co2').value || '-';
  const eff = document.getElementById('pf-efficiency').value || '-';

  document.getElementById('live-title').innerText = title.toUpperCase();
  document.getElementById('live-subtitle').innerText = subtitle.toUpperCase();
  document.getElementById('live-location').innerText = loc;
  document.getElementById('live-system').innerText = sys;
  document.getElementById('live-co2').innerText = co2;
  document.getElementById('live-efficiency').innerText = eff;
}

/* Auto Generate Metrics based on Capacity */
function autoGenerateMetrics() {
  const systemVal = document.getElementById('pf-system').value;
  // Match any digits/float (e.g. "12", "12.5", "6.2")
  const match = systemVal.match(/(\d+(\.\d+)?)/);
  if (match) {
    const capacity = parseFloat(match[1]);
    if (!isNaN(capacity) && capacity > 0) {
      const co2 = (capacity * 39.2333).toFixed(2);
      const trees = (capacity * 1.175).toFixed(2);
      
      document.getElementById('pf-co2').value = co2 + " t";
      document.getElementById('pf-efficiency').value = trees + " K";
    }
  }
  updateLivePreview();
}

function handlePortfolioImageUpload(input) {
  const files = Array.from(input.files);
  if (files.length === 0) return;
  if (files.length > 10) {
    alert('You can only upload up to 10 images.');
    input.value = '';
    return;
  }
  
  const invalidFile = files.find(file => file.size > 5 * 1024 * 1024);
  if (invalidFile) {
    alert('One of the files exceeds the 5MB limit.');
    input.value = '';
    return;
  }

  let loadedCount = 0;
  const previews = [];
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      previews.push(e.target.result);
      loadedCount++;
      if (loadedCount === files.length) {
        document.getElementById('live-img').src = previews[0];
        document.getElementById('pf-img-label').innerHTML = `<i class="fas fa-check-circle text-green-500"></i> ${files.length} images selected`;
      }
    };
    reader.readAsDataURL(file);
  });
}

function editProject(id) {
  const p = projectsDb.find(x => x.id == id);
  if(!p) return;
  
  document.getElementById('pf-id').value = p.id;
  document.getElementById('pf-title').value = p.project_name;
  document.getElementById('pf-subtitle').value = p.subtitle;
  document.getElementById('pf-location').value = p.location;
  document.getElementById('pf-system').value = p.system_type;
  document.getElementById('pf-co2').value = p.co2_reduction;
  document.getElementById('pf-efficiency').value = p.efficiency_rate;
  document.getElementById('pf-status').value = p.status;
  
  // Parse images JSON array
  let images = [];
  try {
    images = JSON.parse(p.image_url);
    if (!Array.isArray(images)) images = [p.image_url];
  } catch(e) {
    images = [p.image_url];
  }
  let firstImg = images[0] || 'assets/img/product-placeholder.png';
  let imageSrc = (firstImg.startsWith('uploads') || firstImg.startsWith('assets')) ? '../../' + firstImg : firstImg;
  
  document.getElementById('live-img').src = imageSrc;
  document.getElementById('pf-img-label').innerHTML = `<i class="fas fa-images text-blue-500"></i> ${images.length} images set`;
  
  document.getElementById('portfolio-form-title').innerText = 'Edit Project';
  updateLivePreview();
  
  document.getElementById('portfolioModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

async function deleteProject(id) {
  if(confirm('Are you sure you want to delete this project?')) {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    
    try {
        const res = await fetch('../../controllers/portfolio_api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if(json.status === 'success') {
            fetchPortfolioProjects();
        } else {
            alert(json.message);
        }
    } catch(e) {
        console.error(e);
        alert('Network Error');
    }
  }
}

async function savePortfolioProject(e) {
  e.preventDefault();
  const form = document.getElementById('portfolio-form');
  const fd = new FormData(form);
  
  const btn = document.getElementById('pf-save-btn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  btn.style.opacity = '0.7';
  
  try {
      const res = await fetch('../../controllers/portfolio_api.php', { method: 'POST', body: fd });
      const json = await res.json();
      
      if(json.status === 'success') {
          closePortfolioModal();
          fetchPortfolioProjects();
      } else {
          alert("Error: " + json.message);
      }
  } catch (err) {
      console.error(err);
      alert('Error saving project.');
  } finally {
      btn.innerHTML = '<i class="fas fa-save"></i> Save Project Configuration';
      btn.style.opacity = '1';
  }
}

// Initial Fetch on load
document.addEventListener('DOMContentLoaded', () => {
  // If the dashboard toggles sections dynamically, we might need to check visibility, 
  // but we can just fetch data silently in the background
  fetchPortfolioProjects();
});

// Close modal if clicking outside the modal content
document.getElementById('portfolioModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closePortfolioModal();
  }
});

// Drag and drop cosmetics
const dz = document.getElementById('pf-drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('dragover'); });
dz.addEventListener('dragleave', e => { dz.classList.remove('dragover'); });
dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('dragover'); });

</script>
