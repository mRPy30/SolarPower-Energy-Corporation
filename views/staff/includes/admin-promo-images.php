<?php
/**
 * admin-promo-images.php
 * Staff-facing Promotional Image Manager
 * Include in your admin panel or access directly (add auth as needed).
 */

$configFile = __DIR__ . '/promo-images.json';

// Load current config
$config = [
    'main'   => 'assets/img/go-solar.jpg',
    'top'    => 'assets/img/installnow.jpg',
    'bottom' => 'assets/img/occular.jpg',
];
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if ($saved) $config = array_merge($config, $saved);
}

$success = $_GET['saved'] ?? false;
$error   = $_GET['error']  ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Promo Image Manager — SolarPower Energy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
  :root {
    --sun: #F5A623;
    --sun-light: #FFF3DC;
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
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--surface);
    color: var(--text);
    min-height: 100vh;
  }

  /* ── Top bar ── */
  .topbar {
    background: var(--solar);
    padding: 0 32px;
    height: 60px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 12px rgba(0,0,0,.18);
  }
  .topbar-logo {
    width: 30px;
    height: 30px;
    background: var(--sun);
    border-radius: 8px;
    display: grid;
    place-items: center;
    font-size: 14px;
    color: var(--solar);
  }
  .topbar-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    color: #fff;
    letter-spacing: .3px;
  }
  .topbar-badge {
    margin-left: auto;
    background: rgba(255,255,255,.12);
    color: rgba(255,255,255,.75);
    font-size: .72rem;
    font-weight: 500;
    padding: 4px 12px;
    border-radius: 20px;
    letter-spacing: .4px;
  }

  /* ── Layout ── */
  .workspace {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 28px;
    max-width: 1180px;
    margin: 36px auto;
    padding: 0 24px 48px;
  }
  @media (max-width: 900px) {
    .workspace { grid-template-columns: 1fr; }
  }

  /* ── Section heading ── */
  .section-label {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .72rem;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 18px;
  }

  /* ── Alert ── */
  .alert-strip {
    border-radius: var(--radius);
    padding: 14px 20px;
    font-size: .88rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 22px;
  }
  .alert-strip.success { background:#F0FFF4; border:1px solid #9AE6B4; color: var(--success); }
  .alert-strip.error   { background:#FFF5F5; border:1px solid #FC8181; color: var(--danger); }

  /* ── Upload card ── */
  .upload-card {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 20px;
    transition: box-shadow .25s;
  }
  .upload-card:hover { box-shadow: 0 8px 32px rgba(26,60,94,.13); }

  .upload-card-header {
    padding: 16px 22px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .slot-badge {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: grid; place-items: center;
    font-size: .8rem; font-weight: 700;
    flex-shrink: 0;
  }
  .slot-badge.main   { background: var(--sun-light); color: var(--sun); }
  .slot-badge.top    { background: #EBF8FF; color: #2B6CB0; }
  .slot-badge.bottom { background: #F0FFF4; color: #276749; }

  .upload-card-title { font-family:'Syne',sans-serif; font-weight:700; font-size:.95rem; }
  .upload-card-desc  { font-size:.78rem; color:var(--muted); margin-top:1px; }

  .upload-card-body { padding: 20px 22px; }

  /* Drop zone */
  .drop-zone {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 20px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
    background: var(--surface);
  }
  .drop-zone:hover, .drop-zone.dragover {
    border-color: var(--solar-mid);
    background: #EBF4FF;
  }
  .drop-zone input[type=file] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer; width:100%; height:100%;
  }
  .drop-icon { font-size: 1.6rem; color: var(--muted); margin-bottom: 6px; }
  .drop-text { font-size: .82rem; color: var(--muted); line-height: 1.5; }
  .drop-text strong { color: var(--solar); font-weight:600; }

  /* Thumb preview */
  .thumb-wrap {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #eee;
    margin-bottom: 14px;
  }
  .thumb-wrap img {
    width: 100%; display: block;
    object-fit: cover;
    transition: opacity .3s;
  }
  .thumb-wrap.main-thumb   { height: 160px; }
  .thumb-wrap.small-thumb  { height: 100px; }

  .thumb-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.55);
    display: flex; flex-direction:column;
    align-items: center; justify-content: center;
    gap: 6px; opacity: 0;
    transition: opacity .22s;
  }
  .thumb-wrap:hover .thumb-overlay { opacity: 1; }
  .thumb-overlay span {
    color: #fff; font-size: .78rem; font-weight:500;
  }
  .thumb-overlay i { color: #fff; font-size:1.2rem; }

  /* Save button */
  .btn-save {
    width: 100%;
    background: var(--solar);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 11px;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .9rem;
    letter-spacing: .3px;
    cursor: pointer;
    display: flex; align-items:center; justify-content:center; gap:8px;
    transition: background .2s, transform .15s;
    margin-top: 12px;
  }
  .btn-save:hover { background: #0f2540; transform: translateY(-1px); }
  .btn-save:active { transform: translateY(0); }
  .btn-save.loading { opacity:.7; pointer-events:none; }

  .save-all-wrap {
    margin-top: 6px;
    padding-top: 24px;
    border-top: 1px dashed var(--border);
  }
  .btn-save-all {
    width: 100%;
    background: linear-gradient(135deg, var(--sun), #e0921b);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 13px;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: .95rem;
    letter-spacing: .5px;
    cursor: pointer;
    display: flex; align-items:center; justify-content:center; gap:8px;
    transition: filter .2s, transform .15s;
    box-shadow: 0 4px 14px rgba(245,166,35,.35);
  }
  .btn-save-all:hover { filter: brightness(1.06); transform: translateY(-2px); }

  /* ── Live preview panel ── */
  .preview-panel {
    position: sticky;
    top: 88px;
    align-self: start;
  }
  .preview-shell {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
  }
  .preview-topbar {
    background: #f0f2f5;
    padding: 10px 16px;
    display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid var(--border);
  }
  .preview-topbar-dot { width:8px;height:8px;border-radius:50%; }
  .preview-topbar-label {
    margin-left: auto;
    font-size:.7rem; font-weight:600;
    color:var(--muted); letter-spacing:.8px; text-transform:uppercase;
  }
  .preview-body { padding: 16px; }

  /* Mini promo layout replicating the actual section */
  .mini-promo {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .mini-card {
    border-radius: 8px; overflow:hidden; position:relative;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
  }
  .mini-card img {
    width:100%; display:block; object-fit:cover;
  }
  .mini-card.large { grid-row: span 2; }
  .mini-card.large img { height: 200px; }
  .mini-card:not(.large) img { height: 96px; }
  .mini-card-label {
    position:absolute; bottom:0; left:0; right:0;
    padding: 6px 8px;
    background: linear-gradient(transparent, rgba(0,0,0,.65));
    color:#fff; font-size:.65rem; font-weight:600;
  }

  .preview-hint {
    margin-top: 12px;
    font-size: .73rem;
    color: var(--muted);
    text-align: center;
    line-height: 1.5;
  }

  /* ── Info box ── */
  .info-box {
    background: var(--sun-light);
    border: 1px solid #F6D07A;
    border-radius: 10px;
    padding: 14px 16px;
    font-size: .8rem;
    color: #7B5E00;
    line-height: 1.6;
    margin-top: 20px;
  }
  .info-box strong { font-weight:600; }

  /* ── Filename indicator ── */
  .file-indicator {
    font-size: .75rem;
    color: var(--muted);
    margin-top: 8px;
    display: flex; align-items:center; gap:5px;
  }
  .file-indicator.chosen { color: var(--success); }
  .file-indicator i { font-size:.75rem; }
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-logo"><i class="fas fa-solar-panel"></i></div>
  <span class="topbar-title">Promotional Image Manager</span>
  <span class="topbar-badge"><i class="fas fa-lock" style="font-size:.6rem;margin-right:4px;"></i> Staff Only</span>
</div>

<div class="workspace">

  <!-- Left: Upload Forms -->
  <div>
    <div class="section-label">Manage Promotional Images</div>

    <?php if ($success): ?>
    <div class="alert-strip success">
      <i class="fas fa-check-circle"></i> Images updated successfully! Changes are now live on the website.
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert-strip error">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(urldecode($error)) ?>
    </div>
    <?php endif; ?>

    <!-- Card 1: Main (Large Left) -->
    <form method="POST" action="save-promo-images.php" enctype="multipart/form-data" id="form-main">
      <input type="hidden" name="slot" value="main">
      <div class="upload-card" id="card-main">
        <div class="upload-card-header">
          <div class="slot-badge main"><i class="fas fa-star"></i></div>
          <div>
            <div class="upload-card-title">Main Banner</div>
            <div class="upload-card-desc">Large left image — highest visual impact</div>
          </div>
        </div>
        <div class="upload-card-body">
          <div class="thumb-wrap main-thumb" id="thumb-main">
            <img src="<?= htmlspecialchars($config['main']) ?>?cb=<?= time() ?>" alt="Main promo" id="preview-main">
            <div class="thumb-overlay">
              <i class="fas fa-camera"></i>
              <span>Current image</span>
            </div>
          </div>
          <div class="drop-zone" id="dz-main">
            <input type="file" name="image" accept="image/*" onchange="previewImage(this,'preview-main','preview-panel-main','indicator-main')">
            <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="drop-text"><strong>Click or drag</strong> to replace<br>JPG, PNG, WebP — max 5MB</div>
          </div>
          <div class="file-indicator" id="indicator-main"><i class="fas fa-image"></i> No file chosen</div>
          <button type="submit" class="btn-save" onclick="setLoading(this)">
            <i class="fas fa-upload"></i> Upload &amp; Save Main Banner
          </button>
        </div>
      </div>
    </form>

    <!-- Card 2: Top Right -->
    <form method="POST" action="save-promo-images.php" enctype="multipart/form-data" id="form-top">
      <input type="hidden" name="slot" value="top">
      <div class="upload-card" id="card-top">
        <div class="upload-card-header">
          <div class="slot-badge top"><i class="fas fa-arrow-up"></i></div>
          <div>
            <div class="upload-card-title">Top Right Card</div>
            <div class="upload-card-desc">Upper smaller card on the right column</div>
          </div>
        </div>
        <div class="upload-card-body">
          <div class="thumb-wrap small-thumb" id="thumb-top">
            <img src="<?= htmlspecialchars($config['top']) ?>?cb=<?= time() ?>" alt="Top right promo" id="preview-top">
            <div class="thumb-overlay">
              <i class="fas fa-camera"></i>
              <span>Current image</span>
            </div>
          </div>
          <div class="drop-zone" id="dz-top">
            <input type="file" name="image" accept="image/*" onchange="previewImage(this,'preview-top','preview-panel-top','indicator-top')">
            <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="drop-text"><strong>Click or drag</strong> to replace<br>JPG, PNG, WebP — max 5MB</div>
          </div>
          <div class="file-indicator" id="indicator-top"><i class="fas fa-image"></i> No file chosen</div>
          <button type="submit" class="btn-save" onclick="setLoading(this)">
            <i class="fas fa-upload"></i> Upload &amp; Save Top Card
          </button>
        </div>
      </div>
    </form>

    <!-- Card 3: Bottom Right -->
    <form method="POST" action="save-promo-images.php" enctype="multipart/form-data" id="form-bottom">
      <input type="hidden" name="slot" value="bottom">
      <div class="upload-card" id="card-bottom">
        <div class="upload-card-header">
          <div class="slot-badge bottom"><i class="fas fa-arrow-down"></i></div>
          <div>
            <div class="upload-card-title">Bottom Right Card</div>
            <div class="upload-card-desc">Lower smaller card on the right column</div>
          </div>
        </div>
        <div class="upload-card-body">
          <div class="thumb-wrap small-thumb" id="thumb-bottom">
            <img src="<?= htmlspecialchars($config['bottom']) ?>?cb=<?= time() ?>" alt="Bottom right promo" id="preview-bottom">
            <div class="thumb-overlay">
              <i class="fas fa-camera"></i>
              <span>Current image</span>
            </div>
          </div>
          <div class="drop-zone" id="dz-bottom">
            <input type="file" name="image" accept="image/*" onchange="previewImage(this,'preview-panel-bottom-img','preview-panel-bottom','indicator-bottom')">
            <input type="file" name="image" accept="image/*" onchange="previewImage(this,'preview-bottom','preview-panel-bottom','indicator-bottom')" style="display:block;position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
            <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="drop-text"><strong>Click or drag</strong> to replace<br>JPG, PNG, WebP — max 5MB</div>
          </div>
          <div class="file-indicator" id="indicator-bottom"><i class="fas fa-image"></i> No file chosen</div>
          <button type="submit" class="btn-save" onclick="setLoading(this)">
            <i class="fas fa-upload"></i> Upload &amp; Save Bottom Card
          </button>
        </div>
      </div>
    </form>

    <div class="info-box">
      <strong><i class="fas fa-lightbulb"></i> Tips for best results:</strong><br>
      • Main banner: landscape ratio (16:9 or wider) works best<br>
      • Right cards: square or portrait (1:1 to 4:5) recommended<br>
      • Keep file size under 5MB for fast page loads<br>
      • Images replace immediately — no backup is made automatically
    </div>
  </div>

  <!-- Right: Live Preview Panel -->
  <div class="preview-panel">
    <div class="section-label">Live Preview</div>
    <div class="preview-shell">
      <div class="preview-topbar">
        <div class="preview-topbar-dot" style="background:#ff5f57"></div>
        <div class="preview-topbar-dot" style="background:#febc2e"></div>
        <div class="preview-topbar-dot" style="background:#28c840"></div>
        <span class="preview-topbar-label">Section Preview</span>
      </div>
      <div class="preview-body">
        <div class="mini-promo">
          <div class="mini-card large">
            <img src="<?= htmlspecialchars($config['main']) ?>?cb=<?= time() ?>" id="preview-panel-main" alt="Main">
            <div class="mini-card-label">Main Banner</div>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <div class="mini-card">
              <img src="<?= htmlspecialchars($config['top']) ?>?cb=<?= time() ?>" id="preview-panel-top" alt="Top">
              <div class="mini-card-label">Top Right</div>
            </div>
            <div class="mini-card">
              <img src="<?= htmlspecialchars($config['bottom']) ?>?cb=<?= time() ?>" id="preview-panel-bottom" alt="Bottom">
              <div class="mini-card-label">Bottom Right</div>
            </div>
          </div>
        </div>
        <p class="preview-hint"><i class="fas fa-eye" style="margin-right:4px;"></i>Preview updates instantly when you choose a file</p>
      </div>
    </div>
  </div>

</div><!-- /workspace -->

<script>
function previewImage(input, cardImgId, panelImgId, indicatorId) {
  const file = input.files[0];
  if (!file) return;

  // Validate size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('File is too large. Please choose an image under 5MB.');
    input.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = e => {
    const src = e.target.result;
    document.getElementById(cardImgId).src  = src;
    document.getElementById(panelImgId).src = src;

    const ind = document.getElementById(indicatorId);
    ind.innerHTML = `<i class="fas fa-check-circle"></i> ${file.name}`;
    ind.className = 'file-indicator chosen';
  };
  reader.readAsDataURL(file);
}

function setLoading(btn) {
  // Make sure a file was chosen
  const form = btn.closest('form');
  const fileInput = form.querySelector('input[type=file]');
  if (!fileInput.files.length) {
    event.preventDefault();
    alert('Please choose an image file first.');
    return;
  }
  btn.classList.add('loading');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
}

// Drag-and-drop visual feedback
document.querySelectorAll('.drop-zone').forEach(dz => {
  dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dragover'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('dragover'));
  dz.addEventListener('drop',      e => { e.preventDefault(); dz.classList.remove('dragover'); });
});

// Fix bottom card: only one file input needed — clean up the duplicate
(function() {
  const dz = document.getElementById('dz-bottom');
  const inputs = dz.querySelectorAll('input[type=file]');
  // Remove the first (hidden absolutely positioned duplicate was unintentional)
  if (inputs.length > 1) inputs[0].remove();
  // Re-attach correct handler
  inputs[inputs.length - 1].onchange = function() {
    previewImage(this, 'preview-bottom', 'preview-panel-bottom', 'indicator-bottom');
  };
  inputs[inputs.length - 1].style = '';
})();
</script>

</body>
</html>
