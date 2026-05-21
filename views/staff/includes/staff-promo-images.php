<?php
/**
 * staff-promo-images.php
 * Staff-facing Promotional Image Manager
 * Include in your staff panel or access directly (add auth as needed).
 */

date_default_timezone_set('Asia/Manila');

$configFile = __DIR__ . '/promo-images.json';

function format_start_display(string $value): string {
  $value = trim($value);
  if ($value === '') {
    return date('m/d/Y h:i a');
  }

  // Already in desired display format.
  $dt = DateTime::createFromFormat('m/d/Y h:i a', $value);
  if ($dt instanceof DateTime) {
    return $dt->format('m/d/Y h:i a');
  }

  // Backward compatibility with older datetime-local format.
  $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
  if ($dt instanceof DateTime) {
    return $dt->format('m/d/Y h:i a');
  }

  // Fallback for any parseable datetime string.
  try {
    return (new DateTime($value))->format('m/d/Y h:i a');
  } catch (Exception $e) {
    return date('m/d/Y h:i a');
  }
}


// Load current config

$defaults = [
    'image' => '',
    'link'  => '',
  'start' => date('m/d/Y h:i a'),
];



$config = [
    'main'   => array_merge($defaults, ['image' => 'assets/img/go-solar.jpg']),
    'top'    => array_merge($defaults, ['image' => 'assets/img/installnow.jpg']),
    'bottom' => array_merge($defaults, ['image' => 'assets/img/occular.jpg']),
];

if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if ($saved) {
        foreach (['main', 'top', 'bottom'] as $s) {
            if (isset($saved[$s])) {
                // Handle both old string format and new object format
                if (is_string($saved[$s])) {
                    $config[$s]['image'] = $saved[$s];
                } else {
                    $config[$s] = array_merge($config[$s], $saved[$s]);
                }
            }
        }
    }
}

foreach (['main', 'top', 'bottom'] as $s) {
  $config[$s]['start'] = format_start_display((string)($config[$s]['start'] ?? ''));
  
  $endVal = (string)($config[$s]['end'] ?? 'indefinite');
  if ($endVal !== 'indefinite' && trim($endVal) !== '') {
    $config[$s]['end'] = format_start_display($endVal);
  } else {
    $config[$s]['end'] = 'indefinite';
  }
  
  // Prepend relative prefix so it resolves correctly inside the staff directory
  $img = $config[$s]['image'] ?? '';
  if (!empty($img) && strpos($img, 'http') !== 0 && strpos($img, '../../') !== 0) {
      $config[$s]['image'] = '../../' . $img;
  }
}


$success = $_GET['saved'] ?? false;
$error   = $_GET['error']  ?? false;
?>
<div id="promo-images" class="page-content promo-images-page bg-slate-50 min-h-screen">
  <!-- Tailwind CSS CDN Integration and Core Plugins Configuration -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      corePlugins: {
        preflight: false,
        container: false,
      }
    }
  </script>
  
  <!-- Custom Scoped Styles for animations and popover -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Syne:wght@700;800&display=swap');
    
    .promo-images-page {
      font-family: 'DM Sans', sans-serif;
    }
    .promo-images-page .font-syne {
      font-family: 'Syne', sans-serif;
    }
    
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-slideup {
      animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    .active-tab-border {
      position: relative;
    }
    .active-tab-border::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 16px;
      right: 16px;
      height: 3px;
      background-color: #f59e0b;
      border-radius: 99px;
    }
    
    /* Calendar popover button highlights */
    .cal-day:hover {
      background-color: #f1f5f9;
    }
    .cal-day.selected {
      background-color: #0f172a !important;
      color: #ffffff !important;
    }
    .cal-day.today {
      border: 1.5px solid #d97706;
    }
    .cal-day.muted {
      color: #cbd5e1;
    }
    
    /* Pulse effects */
    .live-dot {
      animation: livePulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes livePulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .4; }
    }
  </style>

  <div class="max-w-[1360px] mx-auto p-4 sm:p-6 lg:p-8 animate-slideup">
    
    <!-- Top Alert Messages -->
    <?php if ($success): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 text-sm shadow-sm">
      <i class="fas fa-check-circle text-lg text-emerald-500"></i>
      <div>
        <span class="font-bold">Success!</span> Promotional banners have been updated and are now live on the public website.
      </div>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 text-sm shadow-sm">
      <i class="fas fa-exclamation-circle text-lg text-rose-500"></i>
      <div>
        <span class="font-bold">Error:</span> <?= htmlspecialchars(urldecode($error)) ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- LEFT COLUMN (60% Width) - ACTIVE CONFIGURATOR -->
      <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-xl font-bold text-slate-900 font-syne tracking-tight">Promo Banners Configurator</h1>
            <p class="text-xs text-slate-500 mt-1">Upload and manage banners for the public homepage hero grid</p>
          </div>
          
          <!-- Unified Slot Tab Selectors -->
          <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200/50 self-start sm:self-auto shadow-inner">
            <button type="button" onclick="switchSlotTab('main')" id="tab-btn-main" class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5 shadow-sm bg-white text-slate-800">
              <i class="fas fa-star text-amber-500"></i> Main
            </button>
            <button type="button" onclick="switchSlotTab('top')" id="tab-btn-top" class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5 text-slate-500 hover:text-slate-900">
              <i class="fas fa-arrow-up text-blue-500"></i> Top Right
            </button>
            <button type="button" onclick="switchSlotTab('bottom')" id="tab-btn-bottom" class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5 text-slate-500 hover:text-slate-900">
              <i class="fas fa-arrow-down text-emerald-500"></i> Bottom Right
            </button>
          </div>
        </div>

        <!-- Inner Configurator Content -->
        <div class="p-6">
          
          <!-- SLOT: MAIN BANNER FORM -->
          <form method="POST" action="includes/save-promo-images.php" enctype="multipart/form-data" id="form-slot-main" class="slot-form-block flex flex-col gap-6">
            <input type="hidden" name="slot" value="main">
            
            <!-- Header slot info & Live indicator status -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs"><i class="fas fa-star"></i></span>
                <div>
                  <h3 class="font-bold text-slate-900 text-sm">Main Left Banner</h3>
                  <p class="text-xs text-slate-500">Largest banner slot — primary focus</p>
                </div>
              </div>
              <span id="badge-main" class="px-2.5 py-1 rounded-full text-[11px] font-bold border flex items-center gap-1.5 bg-slate-100 text-slate-600 border-slate-200">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Calculating Status
              </span>
            </div>

            <!-- Upload drag & drop zone -->
            <div class="flex flex-col md:flex-row gap-6 items-center">
              <div class="w-full md:w-1/3 flex flex-col gap-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Current Banner Cover</label>
                <div class="relative w-full aspect-[16/9] md:aspect-square bg-slate-100 rounded-xl overflow-hidden border border-slate-200 group">
                  <img src="<?= htmlspecialchars($config['main']['image']) ?>?cb=<?= time() ?>" id="preview-main" class="w-full h-full object-cover" onerror="mgenPromoError(this)">
                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-xs gap-1 transition duration-200">
                    <i class="fas fa-camera text-base"></i>
                    <span>Currently Active</span>
                  </div>
                </div>
              </div>
              
              <div class="w-full md:w-2/3 flex flex-col gap-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Upload New Banner Image</label>
                <div id="dropzone-main" class="dropzone-container relative border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-slate-50/50 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group min-h-[144px]">
                  <input type="file" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFileSelect(this, 'main')">
                  <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-500 transition duration-200">
                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                  </div>
                  <div>
                    <p class="text-xs text-slate-700 font-bold"><span class="text-blue-600">Click to upload</span> or drag image here</p>
                    <p class="text-[10px] text-slate-400 mt-1">Recommended dimension: 1920x600px (JPG, PNG, WebP up to 5MB)</p>
                  </div>
                </div>
                <!-- Selection reset state -->
                <div id="file-select-banner-main" class="hidden items-center justify-between p-2.5 rounded-lg border border-emerald-100 bg-emerald-50/50 text-xs text-emerald-800">
                  <span class="flex items-center gap-1.5 font-semibold"><i class="fas fa-check-circle text-emerald-500"></i> <span id="filename-main">No file</span></span>
                  <button type="button" onclick="clearFileSelection('main')" class="px-2 py-1 rounded bg-white hover:bg-rose-50 hover:text-rose-600 font-bold text-[10px] text-slate-500 border border-slate-200 transition">Remove</button>
                </div>
              </div>
            </div>

            <!-- Inputs -->
            <div class="flex flex-col gap-4">
              <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-link text-slate-400"></i> Destination Redirect Link</label>
                <input type="url" name="link" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all" placeholder="e.g. https://facebook.com/solarpowercorp/posts/..." value="<?= htmlspecialchars($config['main']['link']) ?>" oninput="syncLiveText('main', this.value)">
              </div>
              
              <!-- Date Range Schedulers -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">
                  <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-clock text-slate-400"></i> Start Posting Date & Time</label>
                  <div class="relative cursor-pointer" onclick="triggerCalendarPicker('start-main')">
                    <input type="text" name="start" id="start-main" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all cursor-pointer" placeholder="MM/DD/YYYY HH:MM am/pm" value="<?= htmlspecialchars($config['main']['start']) ?>" readonly>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                      <i class="fas fa-calendar-alt"></i>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-ban text-slate-400"></i> Expiration Date & Time</label>
                  <div class="relative cursor-pointer" id="end-container-main" onclick="triggerCalendarPicker('end-main')">
                    <input type="text" name="end" id="end-main" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all cursor-pointer" placeholder="MM/DD/YYYY HH:MM am/pm" value="<?= htmlspecialchars($config['main']['end'] ?? 'indefinite') ?>" readonly>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                      <i class="fas fa-calendar-times"></i>
                    </div>
                  </div>
                  <!-- Run Indefinitely Toggle -->
                  <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="run_indefinitely" value="1" id="indefinite-main" class="w-4.5 h-4.5 accent-amber-500 rounded border-slate-300 focus:ring-0 focus:ring-offset-0" onchange="toggleIndefinite('main', this.checked)" <?= ($config['main']['end'] === 'indefinite' || empty($config['main']['end'])) ? 'checked' : '' ?>>
                    <label for="indefinite-main" class="text-xs text-slate-500 font-semibold cursor-pointer select-none">Run Indefinitely (Never Expire)</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mt-4 w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl text-sm transition-all duration-200 shadow-sm flex items-center justify-center gap-2 font-syne uppercase tracking-wider">
              <i class="fas fa-save text-amber-400"></i> Save Main Banner Configuration
            </button>
          </form>

          <!-- SLOT: TOP RIGHT FORM -->
          <form method="POST" action="includes/save-promo-images.php" enctype="multipart/form-data" id="form-slot-top" class="slot-form-block flex flex-col gap-6 hidden">
            <input type="hidden" name="slot" value="top">
            
            <!-- Header slot info & Live indicator status -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs"><i class="fas fa-arrow-up"></i></span>
                <div>
                  <h3 class="font-bold text-slate-900 text-sm">Top Right Banner Card</h3>
                  <p class="text-xs text-slate-500">Upper small card banner — supporting focus</p>
                </div>
              </div>
              <span id="badge-top" class="px-2.5 py-1 rounded-full text-[11px] font-bold border flex items-center gap-1.5 bg-slate-100 text-slate-600 border-slate-200">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Calculating Status
              </span>
            </div>

            <!-- Upload drag & drop zone -->
            <div class="flex flex-col md:flex-row gap-6 items-center">
              <div class="w-full md:w-1/3 flex flex-col gap-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Current Banner Cover</label>
                <div class="relative w-full aspect-[16/9] md:aspect-square bg-slate-100 rounded-xl overflow-hidden border border-slate-200 group">
                  <img src="<?= htmlspecialchars($config['top']['image']) ?>?cb=<?= time() ?>" id="preview-top" class="w-full h-full object-cover" onerror="mgenPromoError(this)">
                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-xs gap-1 transition duration-200">
                    <i class="fas fa-camera text-base"></i>
                    <span>Currently Active</span>
                  </div>
                </div>
              </div>
              
              <div class="w-full md:w-2/3 flex flex-col gap-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Upload New Banner Image</label>
                <div id="dropzone-top" class="dropzone-container relative border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-slate-50/50 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group min-h-[144px]">
                  <input type="file" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFileSelect(this, 'top')">
                  <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-500 transition duration-200">
                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                  </div>
                  <div>
                    <p class="text-xs text-slate-700 font-bold"><span class="text-blue-600">Click to upload</span> or drag image here</p>
                    <p class="text-[10px] text-slate-400 mt-1">Recommended dimension: 800x800px (JPG, PNG, WebP up to 5MB)</p>
                  </div>
                </div>
                <!-- Selection reset state -->
                <div id="file-select-banner-top" class="hidden items-center justify-between p-2.5 rounded-lg border border-emerald-100 bg-emerald-50/50 text-xs text-emerald-800">
                  <span class="flex items-center gap-1.5 font-semibold"><i class="fas fa-check-circle text-emerald-500"></i> <span id="filename-top">No file</span></span>
                  <button type="button" onclick="clearFileSelection('top')" class="px-2 py-1 rounded bg-white hover:bg-rose-50 hover:text-rose-600 font-bold text-[10px] text-slate-500 border border-slate-200 transition">Remove</button>
                </div>
              </div>
            </div>

            <!-- Inputs -->
            <div class="flex flex-col gap-4">
              <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-link text-slate-400"></i> Destination Redirect Link</label>
                <input type="url" name="link" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all" placeholder="e.g. https://facebook.com/solarpowercorp/posts/..." value="<?= htmlspecialchars($config['top']['link']) ?>" oninput="syncLiveText('top', this.value)">
              </div>
              
              <!-- Date Range Schedulers -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">
                  <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-clock text-slate-400"></i> Start Posting Date & Time</label>
                  <div class="relative cursor-pointer" onclick="triggerCalendarPicker('start-top')">
                    <input type="text" name="start" id="start-top" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all cursor-pointer" placeholder="MM/DD/YYYY HH:MM am/pm" value="<?= htmlspecialchars($config['top']['start']) ?>" readonly>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                      <i class="fas fa-calendar-alt"></i>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-ban text-slate-400"></i> Expiration Date & Time</label>
                  <div class="relative cursor-pointer" id="end-container-top" onclick="triggerCalendarPicker('end-top')">
                    <input type="text" name="end" id="end-top" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all cursor-pointer" placeholder="MM/DD/YYYY HH:MM am/pm" value="<?= htmlspecialchars($config['top']['end'] ?? 'indefinite') ?>" readonly>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                      <i class="fas fa-calendar-times"></i>
                    </div>
                  </div>
                  <!-- Run Indefinitely Toggle -->
                  <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="run_indefinitely" value="1" id="indefinite-top" class="w-4.5 h-4.5 accent-amber-500 rounded border-slate-300 focus:ring-0 focus:ring-offset-0" onchange="toggleIndefinite('top', this.checked)" <?= ($config['top']['end'] === 'indefinite' || empty($config['top']['end'])) ? 'checked' : '' ?>>
                    <label for="indefinite-top" class="text-xs text-slate-500 font-semibold cursor-pointer select-none">Run Indefinitely (Never Expire)</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mt-4 w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl text-sm transition-all duration-200 shadow-sm flex items-center justify-center gap-2 font-syne uppercase tracking-wider">
              <i class="fas fa-save text-amber-400"></i> Save Top Right Card Configuration
            </button>
          </form>

          <!-- SLOT: BOTTOM RIGHT FORM -->
          <form method="POST" action="includes/save-promo-images.php" enctype="multipart/form-data" id="form-slot-bottom" class="slot-form-block flex flex-col gap-6 hidden">
            <input type="hidden" name="slot" value="bottom">
            
            <!-- Header slot info & Live indicator status -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs"><i class="fas fa-arrow-down"></i></span>
                <div>
                  <h3 class="font-bold text-slate-900 text-sm">Bottom Right Banner Card</h3>
                  <p class="text-xs text-slate-500">Lower small card banner — supporting focus</p>
                </div>
              </div>
              <span id="badge-bottom" class="px-2.5 py-1 rounded-full text-[11px] font-bold border flex items-center gap-1.5 bg-slate-100 text-slate-600 border-slate-200">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Calculating Status
              </span>
            </div>

            <!-- Upload drag & drop zone -->
            <div class="flex flex-col md:flex-row gap-6 items-center">
              <div class="w-full md:w-1/3 flex flex-col gap-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Current Banner Cover</label>
                <div class="relative w-full aspect-[16/9] md:aspect-square bg-slate-100 rounded-xl overflow-hidden border border-slate-200 group">
                  <img src="<?= htmlspecialchars($config['bottom']['image']) ?>?cb=<?= time() ?>" id="preview-bottom" class="w-full h-full object-cover" onerror="mgenPromoError(this)">
                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-xs gap-1 transition duration-200">
                    <i class="fas fa-camera text-base"></i>
                    <span>Currently Active</span>
                  </div>
                </div>
              </div>
              
              <div class="w-full md:w-2/3 flex flex-col gap-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Upload New Banner Image</label>
                <div id="dropzone-bottom" class="dropzone-container relative border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-slate-50/50 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group min-h-[144px]">
                  <input type="file" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFileSelect(this, 'bottom')">
                  <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-500 transition duration-200">
                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                  </div>
                  <div>
                    <p class="text-xs text-slate-700 font-bold"><span class="text-blue-600">Click to upload</span> or drag image here</p>
                    <p class="text-[10px] text-slate-400 mt-1">Recommended dimension: 800x800px (JPG, PNG, WebP up to 5MB)</p>
                  </div>
                </div>
                <!-- Selection reset state -->
                <div id="file-select-banner-bottom" class="hidden items-center justify-between p-2.5 rounded-lg border border-emerald-100 bg-emerald-50/50 text-xs text-emerald-800">
                  <span class="flex items-center gap-1.5 font-semibold"><i class="fas fa-check-circle text-emerald-500"></i> <span id="filename-bottom">No file</span></span>
                  <button type="button" onclick="clearFileSelection('bottom')" class="px-2 py-1 rounded bg-white hover:bg-rose-50 hover:text-rose-600 font-bold text-[10px] text-slate-500 border border-slate-200 transition">Remove</button>
                </div>
              </div>
            </div>

            <!-- Inputs -->
            <div class="flex flex-col gap-4">
              <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-link text-slate-400"></i> Destination Redirect Link</label>
                <input type="url" name="link" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all" placeholder="e.g. https://facebook.com/solarpowercorp/posts/..." value="<?= htmlspecialchars($config['bottom']['link']) ?>" oninput="syncLiveText('bottom', this.value)">
              </div>
              
              <!-- Date Range Schedulers -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">
                  <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-clock text-slate-400"></i> Start Posting Date & Time</label>
                  <div class="relative cursor-pointer" onclick="triggerCalendarPicker('start-bottom')">
                    <input type="text" name="start" id="start-bottom" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all cursor-pointer" placeholder="MM/DD/YYYY HH:MM am/pm" value="<?= htmlspecialchars($config['bottom']['start']) ?>" readonly>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                      <i class="fas fa-calendar-alt"></i>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5 flex items-center gap-1"><i class="fas fa-ban text-slate-400"></i> Expiration Date & Time</label>
                  <div class="relative cursor-pointer" id="end-container-bottom" onclick="triggerCalendarPicker('end-bottom')">
                    <input type="text" name="end" id="end-bottom" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all cursor-pointer" placeholder="MM/DD/YYYY HH:MM am/pm" value="<?= htmlspecialchars($config['bottom']['end'] ?? 'indefinite') ?>" readonly>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                      <i class="fas fa-calendar-times"></i>
                    </div>
                  </div>
                  <!-- Run Indefinitely Toggle -->
                  <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="run_indefinitely" value="1" id="indefinite-bottom" class="w-4.5 h-4.5 accent-amber-500 rounded border-slate-300 focus:ring-0 focus:ring-offset-0" onchange="toggleIndefinite('bottom', this.checked)" <?= ($config['bottom']['end'] === 'indefinite' || empty($config['bottom']['end'])) ? 'checked' : '' ?>>
                    <label for="indefinite-bottom" class="text-xs text-slate-500 font-semibold cursor-pointer select-none">Run Indefinitely (Never Expire)</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mt-4 w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl text-sm transition-all duration-200 shadow-sm flex items-center justify-center gap-2 font-syne uppercase tracking-wider">
              <i class="fas fa-save text-amber-400"></i> Save Bottom Right Card Configuration
            </button>
          </form>

        </div>
        
        <!-- Bottom Tips -->
        <div class="p-6 border-t border-slate-100 bg-slate-50/30 text-xs text-slate-500 flex flex-col gap-1.5">
          <span class="font-bold text-slate-700 flex items-center gap-1"><i class="fas fa-info-circle text-amber-500"></i> Helpful CMS Tips:</span>
          <p>• Banner redirects are linked immediately when users click them on the homepage.</p>
          <p>• The active status determines if the banner is visible right now, queued for later, or past its expiration range.</p>
        </div>

      </div>

      <!-- RIGHT COLUMN (40% Width) - STICKY LIVE PREVIEW -->
      <div class="lg:col-span-5 lg:sticky lg:top-8 flex flex-col gap-6">
        
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden font-sans">
          
          <!-- Viewport Header -->
          <div class="bg-slate-900 p-4 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <span class="w-3 h-3 rounded-full bg-rose-500"></span>
              <span class="w-3 h-3 rounded-full bg-amber-500"></span>
              <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            </div>
            
            <div class="bg-slate-800 rounded-lg px-6 py-1.5 text-[10px] text-slate-400 font-medium select-none text-center min-w-[200px] border border-slate-700/50 truncate mx-2">
              solarpower.com.ph/preview
            </div>
            
            <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-400/20 font-syne uppercase tracking-wider select-none flex-shrink-0">Live Mock</span>
          </div>

          <!-- Mock Homepage Promo Section -->
          <div class="p-4 bg-[#f8fafc] flex flex-col gap-4">
            
            <div class="text-center pb-2 border-b border-slate-200/50">
              <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest font-syne">Homepage Banner Preview</h2>
              <p class="text-[10px] text-slate-400">Click a banner card below to instantly edit it</p>
            </div>
            
            <!-- Real-time Responsive Homepage Grid layout -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 min-h-[300px]">
              
              <!-- Left Big Banner card -->
              <div onclick="switchSlotTab('main')" id="mock-main" class="md:col-span-2 cursor-pointer relative overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col select-none">
                <img src="<?= htmlspecialchars($config['main']['image']) ?>?cb=<?= time() ?>" id="preview-panel-main" class="w-full h-full object-cover min-h-[180px] md:min-h-full transition duration-300 group-hover:scale-[1.02]" onerror="mgenPromoError(this)">
                
                <!-- Info Overlay mimics main banner tags -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/10 to-transparent p-4 flex flex-col justify-end">
                  <div class="flex items-center justify-between">
                    <span class="bg-amber-400 text-slate-950 text-[9px] font-extrabold uppercase px-2 py-0.5 rounded tracking-wide font-syne">Main Banner</span>
                    <span id="mock-status-main" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500 text-white">Live</span>
                  </div>
                  <p id="mock-link-main" class="text-[9px] text-slate-300 font-mono mt-1 break-all truncate max-w-full"><i class="fas fa-link mr-1"></i><?= htmlspecialchars($config['main']['link']) ?: 'No link set' ?></p>
                </div>
                
                <!-- Hover outline/edit banner -->
                <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-200">
                  <span class="bg-white/95 text-slate-900 text-xs font-bold py-1.5 px-3.5 rounded-lg shadow-sm border border-slate-200 flex items-center gap-1.5"><i class="fas fa-edit text-amber-500"></i> Configure Slot</span>
                </div>
              </div>

              <!-- Right stacked column -->
              <div class="grid grid-rows-2 gap-3 md:col-span-1">
                
                <!-- Top Right Card -->
                <div onclick="switchSlotTab('top')" id="mock-top" class="cursor-pointer relative overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col select-none h-[142px]">
                  <img src="<?= htmlspecialchars($config['top']['image']) ?>?cb=<?= time() ?>" id="preview-panel-top" class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]" onerror="mgenPromoError(this)">
                  
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/10 to-transparent p-3.5 flex flex-col justify-end">
                    <div class="flex items-center justify-between">
                      <span class="bg-blue-500 text-white text-[9px] font-extrabold uppercase px-2 py-0.5 rounded tracking-wide font-syne">Top Right</span>
                      <span id="mock-status-top" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500 text-white">Live</span>
                    </div>
                    <p id="mock-link-top" class="text-[9px] text-slate-300 font-mono mt-1 break-all truncate max-w-full"><i class="fas fa-link mr-1"></i><?= htmlspecialchars($config['top']['link']) ?: 'No link set' ?></p>
                  </div>
                  
                  <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-200">
                    <span class="bg-white/95 text-slate-900 text-xs font-bold py-1.5 px-3.5 rounded-lg shadow-sm border border-slate-200 flex items-center gap-1.5"><i class="fas fa-edit text-blue-500"></i> Configure</span>
                  </div>
                </div>

                <!-- Bottom Right Card -->
                <div onclick="switchSlotTab('bottom')" id="mock-bottom" class="cursor-pointer relative overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col select-none h-[142px]">
                  <img src="<?= htmlspecialchars($config['bottom']['image']) ?>?cb=<?= time() ?>" id="preview-panel-bottom" class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]" onerror="mgenPromoError(this)">
                  
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/10 to-transparent p-3.5 flex flex-col justify-end">
                    <div class="flex items-center justify-between">
                      <span class="bg-emerald-500 text-white text-[9px] font-extrabold uppercase px-2 py-0.5 rounded tracking-wide font-syne">Bottom Right</span>
                      <span id="mock-status-bottom" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500 text-white">Live</span>
                    </div>
                    <p id="mock-link-bottom" class="text-[9px] text-slate-300 font-mono mt-1 break-all truncate max-w-full"><i class="fas fa-link mr-1"></i><?= htmlspecialchars($config['bottom']['link']) ?: 'No link set' ?></p>
                  </div>
                  
                  <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-200">
                    <span class="bg-white/95 text-slate-900 text-xs font-bold py-1.5 px-3.5 rounded-lg shadow-sm border border-slate-200 flex items-center gap-1.5"><i class="fas fa-edit text-emerald-500"></i> Configure</span>
                  </div>
                </div>

              </div>
              
            </div>

            <!-- Preview indicator -->
            <div class="text-center py-2 text-[10px] text-slate-500 font-semibold flex items-center justify-center gap-1.5">
              <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 live-dot"></span> Reactive Live Preview active
            </div>
            
          </div>
          
        </div>

      </div>

    </div>

  </div>

  <!-- ==========================================
       GLOBAL REUSABLE CALENDAR DATE-TIME PICKER
       ========================================== -->
  <div id="promo-datepicker-popover" class="absolute z-50 bg-white border border-slate-200 rounded-xl shadow-xl p-4 w-[280px] hidden text-slate-800 flex flex-col gap-3 font-sans animate-slideup border border-slate-200/80">
    
    <!-- Popover Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
      <button type="button" onclick="navigateCalendarMonth(-1)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 transition"><i class="fas fa-chevron-left text-xs"></i></button>
      <span id="cal-month-year-label" class="font-bold text-slate-800 text-sm">Month Year</span>
      <button type="button" onclick="navigateCalendarMonth(1)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 transition"><i class="fas fa-chevron-right text-xs"></i></button>
    </div>

    <!-- Calendar Weekday names -->
    <div class="grid grid-cols-7 gap-1 text-[10px] font-bold text-slate-400 text-center uppercase tracking-wider">
      <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
    </div>

    <!-- Days Grid -->
    <div id="cal-days-grid" class="grid grid-cols-7 gap-1 text-xs">
      <!-- Generated Day Buttons dynamically -->
    </div>

    <!-- Time Selection Row -->
    <div class="flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-200/50 mt-1">
      <div class="flex items-center gap-1 text-slate-700">
        <i class="fas fa-clock text-xs text-slate-400 mr-1"></i>
        <!-- Hours -->
        <select id="cal-hour-select" class="bg-white border border-slate-200 rounded px-1.5 py-1 text-xs font-semibold focus:outline-none">
          <?php for($h=1; $h<=12; $h++): ?>
            <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?></option>
          <?php endfor; ?>
        </select>
        <span class="font-bold text-xs">:</span>
        <!-- Minutes -->
        <select id="cal-minute-select" class="bg-white border border-slate-200 rounded px-1.5 py-1 text-xs font-semibold focus:outline-none">
          <?php for($m=0; $m<=59; $m+=5): ?>
            <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
          <?php endfor; ?>
        </select>
        <!-- AM/PM -->
        <select id="cal-meridiem-select" class="bg-white border border-slate-200 rounded px-1 py-1 text-xs font-semibold focus:outline-none ml-1">
          <option value="am">am</option>
          <option value="pm">pm</option>
        </select>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-2 mt-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="closeCalendarPicker()" class="flex-1 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-lg transition border border-slate-200">Cancel</button>
      <button type="button" onclick="confirmCalendarSelection()" class="flex-1 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition">Apply</button>
    </div>

  </div>

  <!-- Reactivity, Calendars and Dropzones scripts -->
  <script>
  let activeTabSlot = 'main';
  
  // Date Picker Globals
  let activeDatePickerInputId = null;
  let pickerCurrentMonth = new Date().getMonth();
  let pickerCurrentYear = new Date().getFullYear();
  let pickerSelectedDate = new Date();
  
  // Initial configs from PHP
  const slotsConfigData = {
    main: {
      image: "<?= htmlspecialchars($config['main']['image']) ?>",
      link: "<?= htmlspecialchars($config['main']['link']) ?>",
      start: "<?= htmlspecialchars($config['main']['start']) ?>",
      end: "<?= htmlspecialchars($config['main']['end'] ?? 'indefinite') ?>"
    },
    top: {
      image: "<?= htmlspecialchars($config['top']['image']) ?>",
      link: "<?= htmlspecialchars($config['top']['link']) ?>",
      start: "<?= htmlspecialchars($config['top']['start']) ?>",
      end: "<?= htmlspecialchars($config['top']['end'] ?? 'indefinite') ?>"
    },
    bottom: {
      image: "<?= htmlspecialchars($config['bottom']['image']) ?>",
      link: "<?= htmlspecialchars($config['bottom']['link']) ?>",
      start: "<?= htmlspecialchars($config['bottom']['start']) ?>",
      end: "<?= htmlspecialchars($config['bottom']['end'] ?? 'indefinite') ?>"
    }
  };

  function nowInPHT() {
    const now = new Date();
    const phtNowText = new Intl.DateTimeFormat('en-US', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    }).format(now);

    const parts = phtNowText.match(/^(\d{2})\/(\d{2})\/(\d{4}),?\s(\d{2}):(\d{2})\s([AP]M)$/i);
    if (!parts) return '';
    return `${parts[1]}/${parts[2]}/${parts[3]} ${parts[4]}:${parts[5]} ${parts[6].toLowerCase()}`;
  }

  function parseHumanDateTimePHT(value) {
    if (!value || value === 'indefinite') return null;
    const m = value.trim().match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s(\d{1,2}):(\d{2})\s([ap]m)$/i);
    if (!m) return null;

    const month = parseInt(m[1], 10);
    const day = parseInt(m[2], 10);
    const year = parseInt(m[3], 10);
    let hour = parseInt(m[4], 10);
    const minute = parseInt(m[5], 10);
    const meridiem = m[6].toLowerCase();

    if (month < 1 || month > 12 || day < 1 || day > 31 || hour < 1 || hour > 12 || minute < 0 || minute > 59) {
      return null;
    }

    if (meridiem === 'pm' && hour !== 12) hour += 12;
    if (meridiem === 'am' && hour === 12) hour = 0;

    return new Date(year, month - 1, day, hour, minute, 0);
  }

  // 1. Reactive Status Indicator & Badges Generator
  function recalculatePromoState(slot) {
    const startStr = document.getElementById(`start-${slot}`).value.trim();
    const isIndefinite = document.getElementById(`indefinite-${slot}`).checked;
    const endStr = isIndefinite ? 'indefinite' : document.getElementById(`end-${slot}`).value.trim();
    
    const now = new Date();
    const startDate = parseHumanDateTimePHT(startStr);
    const endDate = isIndefinite ? null : parseHumanDateTimePHT(endStr);
    
    let statusText = 'Live';
    let badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
    let dotClass = 'bg-emerald-500 live-dot';
    
    if (startDate && now < startDate) {
      statusText = 'Scheduled';
      badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
      dotClass = 'bg-amber-500';
    } else if (!isIndefinite && endDate && now > endDate) {
      statusText = 'Expired';
      badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
      dotClass = 'bg-slate-400';
    }
    
    // Configurator Badge
    const badgeEl = document.getElementById(`badge-${slot}`);
    if (badgeEl) {
      badgeEl.className = `px-2.5 py-1 rounded-full text-[11px] font-bold border flex items-center gap-1.5 ${badgeClass}`;
      badgeEl.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span> ${statusText}`;
    }
    
    // Live Mock Card Badge
    const mockBadgeEl = document.getElementById(`mock-status-${slot}`);
    if (mockBadgeEl) {
      mockBadgeEl.innerText = statusText;
      let bg = 'bg-emerald-500';
      if (statusText === 'Scheduled') bg = 'bg-amber-500';
      if (statusText === 'Expired') bg = 'bg-slate-500';
      mockBadgeEl.className = `text-[9px] font-bold px-1.5 py-0.5 rounded ${bg} text-white`;
    }
  }

  // Unified Tab Switching Controller
  function switchSlotTab(slot) {
    activeTabSlot = slot;
    
    // 1. Reset forms visibility
    document.querySelectorAll('.slot-form-block').forEach(el => el.classList.add('hidden'));
    document.getElementById(`form-slot-${slot}`).classList.remove('hidden');
    
    // 2. Tab button UI toggle
    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
      btn.className = "px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5 text-slate-500 hover:text-slate-900";
    });
    
    const activeBtn = document.getElementById(`tab-btn-${slot}`);
    activeBtn.className = `px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5 shadow-sm bg-white text-slate-800 active-tab-border`;
    
    // 3. Highlight Mock Card on the right
    document.querySelectorAll('[id^="mock-"]').forEach(el => {
      el.classList.remove('border-amber-400', 'ring-4', 'ring-amber-400/20');
      el.classList.add('border-slate-200');
    });
    
    const activeMock = document.getElementById(`mock-${slot}`);
    if (activeMock) {
      activeMock.classList.remove('border-slate-200');
      activeMock.classList.add('border-amber-400', 'ring-4', 'ring-amber-400/20');
    }
    
    recalculatePromoState(slot);
  }

  // Real-time link description sync
  function syncLiveText(slot, val) {
    const el = document.getElementById(`mock-link-${slot}`);
    if (el) {
      el.innerHTML = `<i class="fas fa-link mr-1"></i>${val.trim() || 'No link set'}`;
    }
  }

  // Toggle Expiration input enabled/disabled
  function toggleIndefinite(slot, checked) {
    const container = document.getElementById(`end-container-${slot}`);
    const input = document.getElementById(`end-${slot}`);
    
    if (checked) {
      input.value = 'indefinite';
      container.style.opacity = '0.5';
      container.style.pointerEvents = 'none';
    } else {
      input.value = nowInPHT();
      container.style.opacity = '1';
      container.style.pointerEvents = 'auto';
    }
    recalculatePromoState(slot);
  }

  // Handles drag zone styling during dragover
  document.querySelectorAll('.dropzone-container').forEach(dz => {
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dz-dragover'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('dz-dragover'));
    dz.addEventListener('drop',      e => { e.preventDefault(); dz.classList.remove('dz-dragover'); });
  });

  // Local File Upload Handlers (Previews immediately)
  function handleFileSelect(input, slot) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
      alert('File is too large. Maximum allowed size is 5MB.');
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = e => {
      const src = e.target.result;
      document.getElementById(`preview-${slot}`).style.display = 'block';
      document.getElementById(`preview-${slot}`).src = src;
      document.getElementById(`preview-panel-${slot}`).style.display = 'block';
      document.getElementById(`preview-panel-${slot}`).src = src;

      // Remove existing vector placeholders if any
      const parentCard = document.getElementById(`preview-${slot}`).parentElement;
      const parentMock = document.getElementById(`preview-panel-${slot}`).parentElement;
      parentCard.querySelectorAll('.mgen-promo-placeholder').forEach(el => el.remove());
      parentMock.querySelectorAll('.mgen-promo-placeholder').forEach(el => el.remove());

      // Show indicator
      document.getElementById(`filename-${slot}`).innerText = file.name;
      document.getElementById(`file-select-banner-${slot}`).classList.remove('hidden');
      document.getElementById(`file-select-banner-${slot}`).classList.add('flex');
    };
    reader.readAsDataURL(file);
  }

  function clearFileSelection(slot) {
    const form = document.getElementById(`form-slot-${slot}`);
    const fileInput = form.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
    
    // Restore original saved image
    const origImage = slotsConfigData[slot].image;
    document.getElementById(`preview-${slot}`).src = origImage;
    document.getElementById(`preview-panel-${slot}`).src = origImage;
    
    // Hide indicator
    document.getElementById(`file-select-banner-${slot}`).classList.add('hidden');
    document.getElementById(`file-select-banner-${slot}`).classList.remove('flex');
  }

  // ===============================================
  // CALENDAR WIDGET IMPLEMENTATION
  // ===============================================
  
  function triggerCalendarPicker(inputId) {
    activeDatePickerInputId = inputId;
    const input = document.getElementById(inputId);
    const popover = document.getElementById('promo-datepicker-popover');
    
    // Parse current date
    const parsed = parseHumanDateTimePHT(input.value);
    pickerSelectedDate = parsed || new Date();
    pickerCurrentMonth = pickerSelectedDate.getMonth();
    pickerCurrentYear = pickerSelectedDate.getFullYear();
    
    // Populate selectors
    let h = pickerSelectedDate.getHours();
    let m = pickerSelectedDate.getMinutes();
    let meridiem = 'am';
    
    if (h >= 12) {
      meridiem = 'pm';
      if (h > 12) h -= 12;
    }
    if (h === 0) h = 12;
    
    // Round minutes to closest 5
    m = Math.round(m / 5) * 5;
    if (m >= 60) m = 55;
    
    document.getElementById('cal-hour-select').value = String(h).padStart(2, '0');
    document.getElementById('cal-minute-select').value = String(m).padStart(2, '0');
    document.getElementById('cal-meridiem-select').value = meridiem;

    // Render calendar grid
    renderCalendarUI();

    // Position popover
    const rect = input.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
    
    popover.style.top = `${rect.bottom + scrollTop + 6}px`;
    popover.style.left = `${rect.left + scrollLeft}px`;
    popover.classList.remove('hidden');
    
    // Click outside handler
    setTimeout(() => {
      document.addEventListener('click', closeOnOutsideClick);
    }, 10);
  }

  function renderCalendarUI() {
    const grid = document.getElementById('cal-days-grid');
    grid.innerHTML = '';

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    document.getElementById('cal-month-year-label').innerText = `${monthNames[pickerCurrentMonth]} ${pickerCurrentYear}`;

    // First day of current month
    const firstDay = new Date(pickerCurrentYear, pickerCurrentMonth, 1).getDay();
    // Total days in current month
    const totalDays = new Date(pickerCurrentYear, pickerCurrentMonth + 1, 0).getDate();
    // Total days in previous month
    const prevMonthDays = new Date(pickerCurrentYear, pickerCurrentMonth, 0).getDate();

    // 1. Render previous month padded days
    for (let x = firstDay; x > 0; x--) {
      const dayNum = prevMonthDays - x + 1;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'cal-day p-2 text-center rounded-lg text-slate-300 pointer-events-none';
      btn.innerText = dayNum;
      grid.appendChild(btn);
    }

    // 2. Render current month days
    const today = new Date();
    for (let d = 1; d <= totalDays; d++) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.innerText = d;
      
      let classes = 'cal-day p-2 text-center rounded-lg font-semibold transition ';
      
      // Highlight selected
      if (pickerSelectedDate.getDate() === d && pickerSelectedDate.getMonth() === pickerCurrentMonth && pickerSelectedDate.getFullYear() === pickerCurrentYear) {
        classes += 'selected bg-slate-900 text-white ';
      } else if (today.getDate() === d && today.getMonth() === pickerCurrentMonth && today.getFullYear() === pickerCurrentYear) {
        classes += 'today border border-amber-500 text-amber-600 ';
      } else {
        classes += 'text-slate-700 hover:bg-slate-100 ';
      }

      btn.className = classes;
      btn.onclick = () => {
        pickerSelectedDate.setDate(d);
        pickerSelectedDate.setMonth(pickerCurrentMonth);
        pickerSelectedDate.setYear(pickerCurrentYear);
        renderCalendarUI();
      };
      grid.appendChild(btn);
    }
  }

  function navigateCalendarMonth(dir) {
    pickerCurrentMonth += dir;
    if (pickerCurrentMonth > 11) {
      pickerCurrentMonth = 0;
      pickerCurrentYear++;
    } else if (pickerCurrentMonth < 0) {
      pickerCurrentMonth = 11;
      pickerCurrentYear--;
    }
    renderCalendarUI();
  }

  function confirmCalendarSelection() {
    let hour = parseInt(document.getElementById('cal-hour-select').value, 10);
    const min = document.getElementById('cal-minute-select').value;
    const meridiem = document.getElementById('cal-meridiem-select').value;
    
    // Format to PHT human string: MM/DD/YYYY hh:mm am/pm
    const m = String(pickerSelectedDate.getMonth() + 1).padStart(2, '0');
    const d = String(pickerSelectedDate.getDate()).padStart(2, '0');
    const y = pickerSelectedDate.getFullYear();
    
    const formatted = `${m}/${d}/${y} ${String(hour).padStart(2, '0')}:${min} ${meridiem}`;
    
    if (activeDatePickerInputId) {
      document.getElementById(activeDatePickerInputId).value = formatted;
      recalculatePromoState(activeDatePickerInputId.split('-')[1]);
    }
    closeCalendarPicker();
  }

  function closeCalendarPicker() {
    document.getElementById('promo-datepicker-popover').classList.add('hidden');
    document.removeEventListener('click', closeOnOutsideClick);
  }

  function closeOnOutsideClick(e) {
    const popover = document.getElementById('promo-datepicker-popover');
    const activeInput = document.getElementById(activeDatePickerInputId);
    
    if (!popover.contains(e.target) && !activeInput.contains(e.target)) {
      closeCalendarPicker();
    }
  }

  // Pre-load all status badges
  setTimeout(() => {
    switchSlotTab('main');
    recalculatePromoState('main');
    recalculatePromoState('top');
    recalculatePromoState('bottom');
  }, 100);

  // Dynamic Promo Image Error Handler (Swaps broken images for vector icon)
  function mgenPromoError(img) {
    const parent = img.parentElement;
    if (!parent) return;
    
    img.style.display = 'none';
    
    if (parent.querySelector('.mgen-promo-placeholder')) return;
    
    const placeholder = document.createElement('div');
    placeholder.className = 'mgen-promo-placeholder flex flex-col items-center justify-center bg-slate-100/50 border border-dashed border-slate-300 rounded-xl w-full h-full text-slate-400 gap-1.5 p-4';
    placeholder.innerHTML = `<i class="fas fa-images text-xl"></i><span class="text-[10px] font-bold uppercase tracking-wider">No Image Set</span>`;
    
    parent.insertBefore(placeholder, parent.firstChild);
  }
  </script>
</div><!-- /promo-images -->
