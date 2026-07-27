<?php
/**
 * staff-video-management.php
 * Staff CMS page for Solar Reels & Videos attached to portfolio projects.
 */
?>
<div id="portfolio-videos" class="page-content portfolio-video-page">
<style>
  .portfolio-video-page {
    --pv-green: #0f5f43;
    --pv-green-dark: #083f2d;
    --pv-blue: #2563eb;
    --pv-gold: #f5a623;
    --pv-bg: #f7fafc;
    --pv-card: #ffffff;
    --pv-line: #e2e8f0;
    --pv-text: #1a202c;
    --pv-muted: #718096;
    --pv-danger: #e53e3e;
    --pv-success: #38a169;
    --pv-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    color: var(--pv-text);
    font-family: 'DM Sans', sans-serif;
  }

  .pv-container { padding: 24px; }
  .pv-page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
  .pv-kicker { color: var(--pv-muted); font-size: .72rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; margin-bottom: 8px; }
  .pv-title { margin: 0; color: var(--pv-text); font-size: 1.75rem; font-weight: 900; letter-spacing: 0; }
  .pv-copy { margin: 8px 0 0; max-width: 680px; color: var(--pv-muted); line-height: 1.6; }
  .pv-btn { border: none; border-radius: 10px; padding: 11px 18px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: transform .2s ease, box-shadow .2s ease, filter .2s ease; }
  .pv-btn:hover { transform: translateY(-1px); filter: brightness(1.03); }
  .pv-btn-primary { background: var(--pv-gold); color: #111827; box-shadow: 0 12px 24px rgba(245, 166, 35, .22); }
  .pv-btn-green { background: var(--pv-green); color: #fff; box-shadow: 0 12px 24px rgba(15, 95, 67, .18); }
  .pv-btn-muted { background: #edf2f7; color: #334155; }
  .pv-btn-danger { background: #fff5f5; color: var(--pv-danger); }

  .pv-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
  .pv-stat { background: var(--pv-card); border: 1px solid var(--pv-line); border-radius: 16px; padding: 18px; box-shadow: var(--pv-shadow); display: flex; align-items: center; gap: 14px; }
  .pv-stat-icon { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; background: #ecfdf5; color: var(--pv-green); font-size: 1.2rem; }
  .pv-stat strong { display: block; font-size: 1.45rem; line-height: 1; }
  .pv-stat span { display: block; margin-top: 5px; color: var(--pv-muted); font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }

  .pv-panel { background: var(--pv-card); border: 1px solid var(--pv-line); border-radius: 18px; box-shadow: var(--pv-shadow); overflow: hidden; }
  .pv-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 18px; border-bottom: 1px solid var(--pv-line); background: #fbfdff; }
  .pv-search { width: min(360px, 100%); border: 1px solid var(--pv-line); border-radius: 10px; padding: 11px 14px; background: #fff; font: inherit; }
  .pv-search:focus, .pv-input:focus, .pv-select:focus, .pv-textarea:focus { outline: none; border-color: var(--pv-green); box-shadow: 0 0 0 3px rgba(15, 95, 67, .08); }
  .pv-table-wrap { overflow-x: auto; }
  .pv-table { width: 100%; border-collapse: collapse; min-width: 920px; }
  .pv-table th { background: #f8fafc; padding: 14px 18px; text-align: left; color: var(--pv-muted); font-size: .72rem; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; border-bottom: 1px solid var(--pv-line); }
  .pv-table td { padding: 16px 18px; border-bottom: 1px solid var(--pv-line); vertical-align: middle; font-size: .9rem; }
  .pv-table tr:last-child td { border-bottom: none; }
  .pv-thumb { width: 86px; aspect-ratio: 16 / 9; border-radius: 10px; object-fit: cover; background: #e2e8f0; display: block; }
  .pv-thumb.vertical { aspect-ratio: 9 / 16; width: 52px; }
  .pv-video-title { font-weight: 900; color: var(--pv-text); }
  .pv-sub { color: var(--pv-muted); font-size: .78rem; margin-top: 4px; }
  .pv-badge { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 5px 10px; font-size: .7rem; font-weight: 850; text-transform: uppercase; letter-spacing: .04em; }
  .pv-badge.published { background: #ecfdf5; color: #047857; }
  .pv-badge.draft { background: #f1f5f9; color: #475569; }
  .pv-badge.format { background: #eff6ff; color: #1d4ed8; }
  .pv-action { border: none; background: transparent; color: var(--pv-muted); padding: 7px; cursor: pointer; font-size: 1rem; transition: color .2s ease, transform .2s ease; }
  .pv-action:hover { color: var(--pv-green); transform: translateY(-1px); }
  .pv-action.delete:hover { color: var(--pv-danger); }
  .pv-empty { padding: 42px; text-align: center; color: var(--pv-muted); }

  .pv-modal { position: fixed; inset: 0; z-index: 10000; display: none; align-items: center; justify-content: center; padding: 22px; background: rgba(15, 23, 42, .72); backdrop-filter: blur(8px); }
  .pv-modal.active { display: flex; }
  .pv-dialog { width: min(980px, 100%); max-height: 92vh; overflow: hidden; border-radius: 18px; background: #f8fafc; box-shadow: 0 30px 90px rgba(0, 0, 0, .35); display: flex; flex-direction: column; }
  .pv-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 22px; background: #fff; border-bottom: 1px solid var(--pv-line); }
  .pv-modal-head h2 { margin: 0; font-size: 1.2rem; font-weight: 900; color: var(--pv-text); }
  .pv-close { width: 38px; height: 38px; border: none; border-radius: 50%; background: #f1f5f9; color: #64748b; cursor: pointer; transition: transform .2s ease, color .2s ease; }
  .pv-close:hover { transform: rotate(90deg); color: var(--pv-danger); }
  .pv-modal-body { overflow-y: auto; padding: 22px; }
  .pv-form-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 18px; }
  .pv-form-card { background: #fff; border: 1px solid var(--pv-line); border-radius: 16px; padding: 20px; margin-bottom: 18px; }
  .pv-form-card h3 { margin: 0 0 16px; font-size: .95rem; font-weight: 900; color: var(--pv-text); }
  .pv-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .pv-field { margin-bottom: 16px; }
  .pv-field label { display: block; margin-bottom: 8px; color: var(--pv-muted); font-size: .72rem; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
  .pv-input, .pv-select, .pv-textarea { width: 100%; border: 1px solid var(--pv-line); border-radius: 10px; padding: 12px 14px; background: #fff; color: var(--pv-text); font: inherit; transition: border-color .2s ease, box-shadow .2s ease; }
  .pv-textarea { min-height: 98px; resize: vertical; }
  .pv-source-tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
  .pv-source-option { border: 1px solid var(--pv-line); border-radius: 12px; padding: 12px; cursor: pointer; background: #fff; display: flex; align-items: center; gap: 10px; font-weight: 800; color: #334155; }
  .pv-source-option input { accent-color: var(--pv-green); }
  .pv-upload { position: relative; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 24px 16px; text-align: center; background: #f8fafc; color: var(--pv-muted); cursor: pointer; transition: border-color .2s ease, background .2s ease; }
  .pv-upload:hover { border-color: var(--pv-green); background: #f0fdf4; }
  .pv-upload input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
  .pv-upload i { display: block; margin-bottom: 10px; color: var(--pv-green); font-size: 1.7rem; }
  .pv-preview-card { position: sticky; top: 0; background: #0f172a; border-radius: 18px; padding: 14px; color: #fff; overflow: hidden; }
  .pv-preview-frame { position: relative; aspect-ratio: 16 / 9; border-radius: 14px; overflow: hidden; background: linear-gradient(135deg, #0f172a, #14532d); display: grid; place-items: center; }
  .pv-preview-frame.vertical { width: 62%; max-width: 220px; margin: 0 auto; aspect-ratio: 9 / 16; }
  .pv-preview-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .pv-preview-play { position: absolute; width: 54px; height: 54px; border-radius: 50%; background: rgba(255, 255, 255, .92); color: var(--pv-green); display: grid; place-items: center; box-shadow: 0 14px 30px rgba(0, 0, 0, .22); }
  .pv-preview-meta { padding: 16px 4px 4px; }
  .pv-preview-meta strong { display: block; font-size: 1rem; line-height: 1.25; }
  .pv-preview-meta span { display: block; margin-top: 6px; color: rgba(255,255,255,.72); font-size: .78rem; }
  .pv-modal-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 22px; background: #fff; border-top: 1px solid var(--pv-line); }

  @media (max-width: 980px) {
    .pv-page-head { flex-direction: column; }
    .pv-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .pv-form-grid { grid-template-columns: 1fr; }
  }

  @media (max-width: 640px) {
    .pv-container { padding: 16px; }
    .pv-stats, .pv-row { grid-template-columns: 1fr; }
    .pv-toolbar { flex-direction: column; align-items: stretch; }
    .pv-modal { padding: 10px; }
  }
</style>

<div class="pv-container">
  <div class="pv-page-head">
    <div>
      <div class="pv-kicker">Video Content Management</div>
      <h1 class="pv-title">Solar Reels & Videos</h1>
      <p class="pv-copy">Upload landscape project walkthroughs, attach videos to existing projects, and publish vertical reels for the public Projects page.</p>
    </div>
    <button class="pv-btn pv-btn-primary" type="button" onclick="PortfolioVideos.openModal()">
      <i class="fas fa-plus"></i> Add New Video
    </button>
  </div>

  <div class="pv-stats">
    <div class="pv-stat">
      <div class="pv-stat-icon"><i class="fas fa-video"></i></div>
      <div><strong id="pv-total">0</strong><span>Total Videos</span></div>
    </div>
    <div class="pv-stat">
      <div class="pv-stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-tv"></i></div>
      <div><strong id="pv-landscape">0</strong><span>Video Tours</span></div>
    </div>
    <div class="pv-stat">
      <div class="pv-stat-icon" style="background:#fdf2f8;color:#db2777;"><i class="fas fa-mobile-screen-button"></i></div>
      <div><strong id="pv-vertical">0</strong><span>Short Reels</span></div>
    </div>
    <div class="pv-stat">
      <div class="pv-stat-icon" style="background:#fffbeb;color:#d97706;"><i class="fas fa-eye"></i></div>
      <div><strong id="pv-published">0</strong><span>Published</span></div>
    </div>
  </div>

  <section class="pv-panel">
    <div class="pv-toolbar">
      <input type="search" class="pv-search" id="pv-search" placeholder="Search videos, projects, categories..." oninput="PortfolioVideos.render()">
      <button class="pv-btn pv-btn-green" type="button" onclick="PortfolioVideos.load()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
    <div class="pv-table-wrap">
      <table class="pv-table">
        <thead>
          <tr>
            <th>Cover</th>
            <th>Video</th>
            <th>Associated Project</th>
            <th>Format</th>
            <th>Category</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="pv-table-body">
          <tr><td colspan="7" class="pv-empty">Loading videos...</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</div>

<div class="pv-modal" id="pv-modal">
  <div class="pv-dialog">
    <div class="pv-modal-head">
      <h2 id="pv-modal-title">Add New Video</h2>
      <button class="pv-close" type="button" onclick="PortfolioVideos.closeModal()" aria-label="Close"><i class="fas fa-times"></i></button>
    </div>

    <form id="pv-form" onsubmit="PortfolioVideos.save(event)" enctype="multipart/form-data">
      <div class="pv-modal-body">
        <div class="pv-form-grid">
          <div>
            <input type="hidden" id="pv-id" name="id">
            <input type="hidden" name="action" value="save">

            <div class="pv-form-card">
              <h3>Core Details</h3>
              <div class="pv-field">
                <label for="pv-title-input">Video Title</label>
                <input class="pv-input" id="pv-title-input" name="title" type="text" placeholder="e.g. 12kW Hybrid Setup Walkthrough" required oninput="PortfolioVideos.updatePreview()">
              </div>
              <div class="pv-field">
                <label for="pv-project">Associated Project (Optional)</label>
                <select class="pv-select" id="pv-project" name="project_id" onchange="PortfolioVideos.updatePreview()">
                  <option value="">No associated project</option>
                </select>
              </div>
              <div class="pv-row">
                <div class="pv-field">
                  <label for="pv-format">Video Format</label>
                  <select class="pv-select" id="pv-format" name="video_format" onchange="PortfolioVideos.onFormatChange()">
                    <option value="landscape">Landscape 16:9 Video Tour</option>
                    <option value="vertical">Vertical 9:16 Short/Reel</option>
                  </select>
                </div>
                <div class="pv-field">
                  <label for="pv-category">Category Tag</label>
                  <select class="pv-select" id="pv-category" name="category_tag" onchange="PortfolioVideos.updatePreview()">
                    <option value="Residential">Residential</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Showcase">Showcase</option>
                  </select>
                </div>
              </div>
              <div class="pv-field">
                <label for="pv-status">Status</label>
                <select class="pv-select" id="pv-status" name="status">
                  <option value="Published">Published</option>
                  <option value="Draft">Draft</option>
                </select>
              </div>
            </div>

            <div class="pv-form-card">
              <h3>Media Source</h3>
              <div class="pv-source-tabs">
                <label class="pv-source-option">
                  <input type="radio" name="media_type" value="file" onchange="PortfolioVideos.onMediaTypeChange()"> File Upload (.mp4)
                </label>
                <label class="pv-source-option">
                  <input type="radio" name="media_type" value="url" checked onchange="PortfolioVideos.onMediaTypeChange()"> Video URL
                </label>
              </div>

              <div class="pv-field" id="pv-file-field" style="display:none;">
                <label>Upload Video File</label>
                <div class="pv-upload">
                  <input type="file" id="pv-video-file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime" onchange="PortfolioVideos.setFileLabel(this, 'pv-video-file-label')">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <div id="pv-video-file-label"><strong>Click or drag</strong> to upload MP4/WebM video<br>Recommended max 250MB</div>
                </div>
              </div>

              <div class="pv-field" id="pv-url-field">
                <label for="pv-url">YouTube / Vimeo / Direct Video URL</label>
                <input class="pv-input" id="pv-url" name="media_url" type="url" placeholder="https://www.youtube.com/watch?v=..." oninput="PortfolioVideos.updatePreview()">
              </div>
            </div>

            <div class="pv-form-card">
              <h3>Thumbnail Cover</h3>
              <div class="pv-upload">
                <input type="file" id="pv-thumb-file" name="thumbnail_file" accept="image/*" onchange="PortfolioVideos.handleThumbnail(this)">
                <i class="fas fa-image"></i>
                <div id="pv-thumb-file-label"><strong>Click or drag</strong> to upload thumbnail cover<br>JPG, PNG, WebP - max 20MB</div>
              </div>
            </div>
          </div>

          <aside class="pv-preview-card">
            <div class="pv-preview-frame" id="pv-preview-frame">
              <img id="pv-preview-image" src="../../assets/img/product-placeholder.png" alt="Video preview">
              <span class="pv-preview-play"><i class="fas fa-play"></i></span>
            </div>
            <div class="pv-preview-meta">
              <strong id="pv-preview-title">Video Title</strong>
              <span id="pv-preview-details">Landscape Video Tour - Showcase</span>
            </div>
          </aside>
        </div>
      </div>

      <div class="pv-modal-actions">
        <button type="button" class="pv-btn pv-btn-muted" onclick="PortfolioVideos.closeModal()">Cancel</button>
        <button type="submit" class="pv-btn pv-btn-green" id="pv-save-btn"><i class="fas fa-save"></i> Save Video</button>
      </div>
    </form>
  </div>
</div>

<script>
window.PortfolioVideos = {
  videos: [],
  projects: [],

  async load() {
    try {
      const res = await fetch(new URL('../../controllers/portfolio_video_api.php', (typeof STAFF_BASE_URL !== 'undefined' ? STAFF_BASE_URL : window.location.href)).href);
      const json = await res.json();
      if (json.status !== 'success') {
        throw new Error(json.message || 'Unable to load videos.');
      }
      this.videos = json.data || [];
      this.projects = json.projects || [];
      this.populateProjects();
      this.render();
    } catch (error) {
      console.error(error);
      const tbody = document.getElementById('pv-table-body');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="pv-empty">Unable to load videos. Please refresh.</td></tr>';
      }
    }
  },

  escape(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  },

  asset(path) {
    if (!path) return '../../assets/img/product-placeholder.png';
    if (/^https?:\/\//i.test(path)) return path;
    return '../../' + path.replace(/^\/+/, '');
  },

  populateProjects() {
    const select = document.getElementById('pv-project');
    if (!select) return;
    const current = select.value;
    select.innerHTML = '<option value="">No associated project</option>';
    this.projects.forEach(project => {
      const option = document.createElement('option');
      option.value = project.id;
      option.textContent = project.project_name;
      select.appendChild(option);
    });
    select.value = current;
  },

  render() {
    const tbody = document.getElementById('pv-table-body');
    const query = (document.getElementById('pv-search')?.value || '').toLowerCase().trim();
    if (!tbody) return;

    const filtered = this.videos.filter(video => {
      const haystack = [
        video.title,
        video.project_name,
        video.video_format,
        video.category_tag,
        video.status
      ].join(' ').toLowerCase();
      return !query || haystack.includes(query);
    });

    document.getElementById('pv-total').textContent = this.videos.length;
    document.getElementById('pv-landscape').textContent = this.videos.filter(v => v.video_format === 'landscape').length;
    document.getElementById('pv-vertical').textContent = this.videos.filter(v => v.video_format === 'vertical').length;
    document.getElementById('pv-published').textContent = this.videos.filter(v => v.status === 'Published').length;

    if (!filtered.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="pv-empty">No videos found. Add your first Solar Reel or Video Tour.</td></tr>';
      return;
    }

    tbody.innerHTML = filtered.map(video => {
      const isVertical = video.video_format === 'vertical';
      const thumb = this.asset(video.thumbnail_url);
      const projectName = video.project_name || 'Not linked';
      const formatLabel = isVertical ? '9:16 Reel' : '16:9 Tour';
      return `
        <tr>
          <td><img class="pv-thumb ${isVertical ? 'vertical' : ''}" src="${this.escape(thumb)}" alt=""></td>
          <td>
            <div class="pv-video-title">${this.escape(video.title)}</div>
            <div class="pv-sub">${this.escape(video.media_type === 'file' ? 'Uploaded video file' : 'External video URL')}</div>
          </td>
          <td>${this.escape(projectName)}</td>
          <td><span class="pv-badge format">${this.escape(formatLabel)}</span></td>
          <td>${this.escape(video.category_tag)}</td>
          <td><span class="pv-badge ${video.status === 'Published' ? 'published' : 'draft'}">${this.escape(video.status)}</span></td>
          <td style="text-align:right;">
            <button class="pv-action" type="button" onclick="PortfolioVideos.edit('${this.escape(video.id)}')" title="Edit"><i class="fas fa-edit"></i></button>
            <button class="pv-action delete" type="button" onclick="PortfolioVideos.delete('${this.escape(video.id)}')" title="Delete"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
      `;
    }).join('');
  },

  openModal(video = null) {
    const form = document.getElementById('pv-form');
    form.reset();
    document.getElementById('pv-id').value = '';
    document.getElementById('pv-modal-title').textContent = video ? 'Edit Video' : 'Add New Video';
    document.getElementById('pv-preview-image').src = '../../assets/img/product-placeholder.png';
    document.getElementById('pv-video-file-label').innerHTML = '<strong>Click or drag</strong> to upload MP4/WebM video<br>Recommended max 250MB';
    document.getElementById('pv-thumb-file-label').innerHTML = '<strong>Click or drag</strong> to upload thumbnail cover<br>JPG, PNG, WebP - max 20MB';

    if (video) {
      document.getElementById('pv-id').value = video.id;
      document.getElementById('pv-title-input').value = video.title || '';
      document.getElementById('pv-project').value = video.project_id || '';
      document.getElementById('pv-format').value = video.video_format || 'landscape';
      document.getElementById('pv-category').value = video.category_tag || 'Showcase';
      document.getElementById('pv-status').value = video.status || 'Draft';
      form.querySelector(`input[name="media_type"][value="${video.media_type || 'url'}"]`).checked = true;
      document.getElementById('pv-url').value = video.media_type === 'url' ? (video.media_url || '') : '';
      document.getElementById('pv-preview-image').src = this.asset(video.thumbnail_url);
    } else {
      document.getElementById('pv-status').value = 'Published';
      form.querySelector('input[name="media_type"][value="url"]').checked = true;
    }

    this.onMediaTypeChange();
    this.onFormatChange();
    this.updatePreview();
    document.getElementById('pv-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
  },

  closeModal() {
    document.getElementById('pv-modal').classList.remove('active');
    document.body.style.overflow = '';
  },

  edit(id) {
    const video = this.videos.find(item => String(item.id) === String(id));
    if (video) this.openModal(video);
  },

  async delete(id) {
    if (!confirm('Delete this video? This cannot be undone.')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);

    try {
      const res = await fetch(new URL('../../controllers/portfolio_video_api.php', (typeof STAFF_BASE_URL !== 'undefined' ? STAFF_BASE_URL : window.location.href)).href, { method: 'POST', body: fd });
      const json = await res.json();
      if (json.status === 'success') {
        await this.load();
      } else {
        alert(json.message || 'Unable to delete video.');
      }
    } catch (error) {
      console.error(error);
      alert('Network error while deleting video.');
    }
  },

  onMediaTypeChange() {
    const mediaType = document.querySelector('input[name="media_type"]:checked')?.value || 'url';
    document.getElementById('pv-file-field').style.display = mediaType === 'file' ? 'block' : 'none';
    document.getElementById('pv-url-field').style.display = mediaType === 'url' ? 'block' : 'none';
    document.getElementById('pv-url').required = mediaType === 'url';
    document.getElementById('pv-video-file').required = mediaType === 'file' && !document.getElementById('pv-id').value;
  },

  onFormatChange() {
    const frame = document.getElementById('pv-preview-frame');
    frame.classList.toggle('vertical', document.getElementById('pv-format').value === 'vertical');
    this.updatePreview();
  },

  updatePreview() {
    const title = document.getElementById('pv-title-input').value || 'Video Title';
    const format = document.getElementById('pv-format').value === 'vertical' ? 'Vertical Short/Reel' : 'Landscape Video Tour';
    const category = document.getElementById('pv-category').value || 'Showcase';
    document.getElementById('pv-preview-title').textContent = title;
    document.getElementById('pv-preview-details').textContent = `${format} - ${category}`;
  },

  setFileLabel(input, labelId) {
    const label = document.getElementById(labelId);
    if (!label || !input.files.length) return;
    label.innerHTML = `<strong>${this.escape(input.files[0].name)}</strong><br>${Math.round(input.files[0].size / 1024 / 1024)}MB selected`;
  },

  handleThumbnail(input) {
    this.setFileLabel(input, 'pv-thumb-file-label');
    if (!input.files.length) return;
    const reader = new FileReader();
    reader.onload = event => {
      document.getElementById('pv-preview-image').src = event.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  },

  async save(event) {
    event.preventDefault();
    const btn = document.getElementById('pv-save-btn');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
      const fd = new FormData(document.getElementById('pv-form'));
      const res = await fetch(new URL('../../controllers/portfolio_video_api.php', (typeof STAFF_BASE_URL !== 'undefined' ? STAFF_BASE_URL : window.location.href)).href, { method: 'POST', body: fd });
      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (error) {
        console.error(text);
        throw new Error('Invalid server response.');
      }

      if (json.status !== 'success') {
        throw new Error(json.message || 'Unable to save video.');
      }

      btn.innerHTML = '<i class="fas fa-check"></i> Saved';
      setTimeout(async () => {
        this.closeModal();
        await this.load();
        btn.disabled = false;
        btn.innerHTML = original;
      }, 600);
    } catch (error) {
      console.error(error);
      alert(error.message || 'Unable to save video.');
      btn.disabled = false;
      btn.innerHTML = original;
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  PortfolioVideos.load();
  const modal = document.getElementById('pv-modal');
  if (modal) {
    modal.addEventListener('click', event => {
      if (event.target === modal) {
        PortfolioVideos.closeModal();
      }
    });
  }
});
</script>
</div>
