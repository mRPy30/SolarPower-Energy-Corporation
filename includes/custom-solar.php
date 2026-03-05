<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solar System Builder – SolarPower Energy Corporation</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --yellow:   #ffc107;
      --yellow-d: #D99200;
      --yellow-l: #FFF8E1;
      --green:    #3A5C1A;
      --green-d:  #2C4713;
      --green-l:  #EEF3E8;
      --bg:       #F7F9F4;
      --border:   #DDE3D4;
      --text:     #1A2308;
      --muted:    #6B7C52;
      --white:    #ffffff;
      --shadow:   0 2px 12px rgba(58,92,26,0.09);
    }

    body { font-family: 'Montserrat', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    /* ── Top bar ── */
    .top-bar {
      background: var(--green);
      color: #fff;
      display: flex; align-items: center; justify-content: space-between;
      padding: 8px 40px;
      font-size: 0.74rem; font-weight: 500;
    }
    .top-bar-left { display: flex; align-items: center; gap: 10px; }
    .doe-badge {
      background: var(--yellow); color: var(--green-d);
      font-weight: 800; font-size: 0.67rem; padding: 3px 9px; border-radius: 3px; letter-spacing: 0.05em;
    }
    .top-bar-right { font-weight: 700; font-size: 0.8rem; }

    /* ── Header ── */
    .site-header {
      background: var(--white); border-bottom: 2px solid var(--border);
      padding: 0 40px; display: flex; align-items: center; justify-content: space-between;
      height: 68px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .logo-mark {
      width: 46px; height: 46px; background: var(--green); border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; position: relative;
    }
    .logo-sun {
      position: absolute; top: -5px; right: -5px;
      width: 18px; height: 18px; background: var(--yellow);
      border-radius: 50%; border: 2.5px solid white;
      display: flex; align-items: center; justify-content: center; font-size: 0.6rem;
    }
    .logo-text .brand { font-weight: 900; font-size: 1.1rem; color: var(--green); letter-spacing: 0.05em; display: block; }
    .logo-text .brand em { color: var(--yellow); font-style: normal; }
    .logo-text .sub { font-size: 0.58rem; color: var(--muted); font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; }

    .hdr-btns { display: flex; gap: 8px; }
    .hbtn {
      border-radius: 6px; padding: 8px 15px; font-size: 0.76rem; font-weight: 700;
      font-family: 'Montserrat', sans-serif; cursor: pointer;
      display: flex; align-items: center; gap: 6px; transition: all .2s;
    }
    .hbtn-ghost { background: transparent; border: 1.5px solid var(--border); color: var(--muted); }
    .hbtn-ghost:hover { border-color: var(--green); color: var(--green); }
    .hbtn-solid { background: var(--yellow); border: none; color: var(--green-d); }
    .hbtn-solid:hover { background: var(--yellow-d); }

    /* ── Steps ── */
    .step-nav {
      border-bottom: 2px solid var(--border);
      display: flex; padding: 0 40px;
    }
    .step-tab {
      padding: 14px 28px; font-size: 0.77rem; font-weight: 700;
      color: var(--muted); cursor: pointer; border-bottom: 3px solid transparent;
      margin-bottom: -2px; transition: all .2s; letter-spacing: 0.06em; text-transform: uppercase;
      display: flex; align-items: center; gap: 8px;
    }
    .step-tab:hover { color: var(--green); }
    .step-num {
      width: 24px; height: 24px; border-radius: 50%;
      background: var(--border); color: var(--muted);
      font-size: 0.67rem; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      transition: all .2s;
    }
    .step-tab:hover .step-num { background: var(--green-l); color: var(--green); }
    .step-tab.active { color: var(--green); border-bottom-color: var(--yellow); }
    .step-tab.active .step-num { background: var(--yellow); color: var(--green-d); }
    .step-tab.completed .step-num { background: var(--green); color: #fff; }

    /* ── Pages ── */
    .page { display: none; }
    .page.active { display: block; }

    /* ── Layout ── */
    .builder {
      display: grid; grid-template-columns: 320px 1fr 360px;
      min-height: calc(100vh - 36px - 68px - 50px - 60px);
    }

    /* ── Monthly Production Chart Panel ── */
    .radar-panel {
      border-right: 1.5px solid var(--border);
      padding: 22px 18px; display: flex; flex-direction: column; align-items: center;
      overflow-y: auto;
    }
    .panel-lbl {
      font-size: 0.7rem; font-weight: 800; text-transform: uppercase;
      letter-spacing: 0.13em; color: var(--green); margin-bottom: 14px; align-self: flex-start;
      display: flex; align-items: center; gap: 6px;
    }
    .panel-lbl::before { content: ''; display: inline-block; width: 3px; height: 14px; background: var(--yellow); border-radius: 2px; }
    .chart-wrap { width: 100%; margin-bottom: 10px; position: relative; }
    .chart-wrap canvas { width: 100% !important; height: 200px !important; }
    .chart-location-row {
      display: flex; align-items: center; gap: 6px; margin-bottom: 10px; width: 100%;
    }
    .chart-location-row label {
      font-size: 0.62rem; font-weight: 700; color: var(--muted); text-transform: uppercase;
      letter-spacing: 0.08em; white-space: nowrap;
    }
    .chart-location-row select {
      flex: 1; background: var(--bg); border: 1.5px solid var(--border); border-radius: 5px;
      color: var(--text); font-family: 'Montserrat', sans-serif; font-size: 0.68rem;
      font-weight: 600; padding: 5px 8px; outline: none; cursor: pointer;
    }
    .chart-location-row select:focus { border-color: var(--green); }
    .chart-legend {
      display: flex; flex-wrap: wrap; gap: 6px 10px; justify-content: flex-start; margin-bottom: 10px;
      font-size: 0.62rem; font-weight: 700; color: var(--muted); width: 100%;
      background: var(--bg); border-radius: 8px; padding: 8px 10px;
      border: 1px solid var(--border);
    }
    .chart-legend span { display: flex; align-items: center; gap: 5px; white-space: nowrap; }
    .chart-legend .dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
    .chart-kwh-row {
      display: flex; justify-content: space-between; width: 100%; padding: 4px 2px;
      font-size: 0.6rem; font-weight: 800; color: var(--green);
      background: var(--green-l); border-radius: 6px; margin-bottom: 6px;
    }
    .chart-kwh-row span { width: calc(100%/12); text-align: center; padding: 2px 0; }
    .seasonal-badges {
      display: flex; flex-wrap: wrap; gap: 5px; width: 100%; margin-top: 10px; margin-bottom: 4px;
    }
    .seasonal-badge {
      font-size: 0.62rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;
      display: inline-flex; align-items: center; gap: 4px; line-height: 1.3;
      transition: transform 0.15s;
    }
    .seasonal-badge:hover { transform: translateY(-1px); }
    .seasonal-badge.peak { background: #E8F5E9; color: #2E7D32; border: 1px solid #A5D6A7; }
    .seasonal-badge.low { background: #FFF3E0; color: #E65100; border: 1px solid #FFCC80; }
    .seasonal-badge.info { background: #E3F2FD; color: #1565C0; border: 1px solid #90CAF9; }

    .build-box {
      width: 100%; background: linear-gradient(135deg, var(--green) 0%, #2C4713 100%);
      border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; color: #fff;
      box-shadow: 0 4px 16px rgba(58,92,26,0.18);
    }
    .build-lbl { font-size: 0.64rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.65; margin-bottom: 3px; }
    .build-val { font-weight: 900; font-size: 1.4rem; color: var(--yellow); transition: all 0.4s; }
    .tier-bar { width: 100%; height: 7px; background: rgba(255,255,255,0.18); border-radius: 4px; margin-top: 10px; overflow: hidden; }
    .tier-fill { height: 100%; background: linear-gradient(90deg, var(--yellow), #82C820); border-radius: 4px; transition: width 0.6s cubic-bezier(.4,0,.2,1); }
    .tier-lbls { display: flex; justify-content: space-between; margin-top: 5px; font-size: 0.6rem; opacity: 0.55; }

    .stats { width: 100%; background: var(--bg); border-radius: 10px; overflow: hidden; border: 1px solid var(--border); }
    .stat-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 9px 12px; border-top: 1px solid var(--border); font-size: 0.77rem;
      transition: background 0.15s;
    }
    .stat-row:first-child { border-top: none; }
    .stat-row:hover { background: var(--green-l); }
    .stat-n { color: var(--muted); font-weight: 500; }
    .stat-v { color: var(--green); font-weight: 800; transition: all 0.4s; }

    /* ── Components ── */
    .comp-panel { display: flex; flex-direction: column; background: #fafbf8; }
    .comp-head {
      padding: 18px 28px;
      border-bottom: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      
    }
    .comp-head h2 { font-size: 1.15rem; font-weight: 900; color: var(--green); }
    .clear-btn {
      background: transparent; border: 1.5px solid #F0C8C8; color: #C0392B;
      font-family: 'Montserrat', sans-serif; font-size: 0.72rem; font-weight: 700;
      padding: 7px 13px; border-radius: 6px; cursor: pointer; transition: all .2s;
    }
    .clear-btn:hover { background: #FFF0EE; }

    .comp-list { padding: 18px 24px; display: flex; flex-direction: column; gap: 11px; overflow-y: auto; }

    .comp-card {
      background: #fff; border: 1.5px solid var(--border); border-radius: 12px;
      padding: 14px 16px; display: flex; align-items: center; gap: 14px;
      cursor: pointer; transition: all .22s; box-shadow: 0 1px 6px rgba(58,92,26,0.06);
    }
    .comp-card:hover { border-color: var(--yellow); transform: translateY(-2px); box-shadow: 0 6px 22px rgba(58,92,26,0.14); }
    .comp-card.active-card { border-color: var(--yellow); border-left: 4px solid var(--yellow); background: var(--yellow-l); box-shadow: 0 4px 16px rgba(255,193,7,0.15); }
    .comp-card.done { border-left: 4px solid var(--green); border-color: var(--green); background: #f6fdf0; }
    .comp-card.warn { border-left: 4px solid #E8A020; background: #FFFBF0; border-color: #E8A020; }
    .comp-card.warn .comp-tag { background: #FFF5E0; color: #9A6A00; border: 1px solid #EDD080; }

    /* Compatibility Warnings Panel */
    .compat-warnings { padding: 0 22px 10px; display: none; }
    .compat-warn {
      background: #FFF8E1; border: 1.5px solid #E8A020; border-radius: 8px;
      padding: 9px 13px; margin-bottom: 6px; font-size: 0.72rem; font-weight: 600;
      color: #7A5200; display: flex; align-items: flex-start; gap: 7px; line-height: 1.4;
    }
    .compat-warn .warn-icon { font-size: 1rem; flex-shrink: 0; }
    .compat-ok {
      background: #F0F9E8; border: 1.5px solid var(--green); border-radius: 8px;
      padding: 9px 13px; font-size: 0.72rem; font-weight: 600;
      color: var(--green); display: flex; align-items: center; gap: 7px;
    }

    .comp-icon {
      width: 54px; height: 54px; border-radius: 10px;
      background: var(--green-l); border: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.45rem; flex-shrink: 0; transition: all 0.3s;
      overflow: hidden;
    }
    .comp-card:hover .comp-icon { border-color: var(--yellow); }
    .comp-card.done .comp-icon { background: var(--green); border-color: var(--green); }
    .comp-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
    .comp-info { flex: 1; }
    .comp-type { font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em; color: var(--yellow-d); margin-bottom: 3px; }
    .comp-name { font-size: 0.85rem; font-weight: 700; color: var(--text); line-height: 1.35; }
    .comp-tag {
      font-size: 0.67rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;
      letter-spacing: 0.04em; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;
    }
    .tag-g { background: var(--green-l); color: var(--green); border: 1px solid #C5DCA0; }
    .tag-w { background: #FFF5E0; color: #9A6A00; border: 1px solid #EDD080; }
    .tag-a { background: #F2F4EE; color: var(--muted); border: 1px solid var(--border); }

    /* ── Selector ── */
    .sel-panel { 
       border-left: 1.5px solid var(--border); 
      display: flex; flex-direction: column; 
      height: calc(100vh - 156px);
      position: sticky; top: 0;
    }
    .sel-head { padding: 18px 20px 14px; border-bottom: 1.5px solid var(--border); flex-shrink: 0; background: #fafbf8; }
    .sel-head h3 { font-size: 0.95rem; font-weight: 900; color: var(--green); display: flex; align-items: center; gap: 7px; }
    .sel-head h3 .arr { color: var(--yellow); font-size: 1.1rem; }
    .filter-row { display: flex; gap: 8px; margin-top: 11px; }
    .f-sel {
      flex: 1; background: var(--bg); border: 1.5px solid var(--border); border-radius: 6px;
      color: var(--muted); font-family: 'Montserrat', sans-serif; font-size: 0.73rem; font-weight: 600;
      padding: 8px 9px; outline: none; cursor: pointer;
    }
    .f-sel:focus { border-color: var(--green); }

    .search-box { position: relative; margin: 13px 20px 8px; }
    .search-box input {
      width: 100%; background: var(--bg); border: 1.5px solid var(--border);
      border-radius: 8px; color: var(--text); font-family: 'Montserrat', sans-serif;
      font-size: 0.8rem; padding: 10px 13px 10px 36px; outline: none; transition: border .2s;
    }
    .search-box input:focus { border-color: var(--green); }
    .search-box input::placeholder { color: #B0BC9A; }
    .search-box .ico { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); }
    .search-box { flex-shrink: 0; }

    .prod-list { 
      flex: 1; 
      overflow-y: auto; 
      padding: 4px 20px 14px; 
      display: flex; 
      flex-direction: column; 
      gap: 8px;
      min-height: 0; /* Required for flex child scrolling */
    }
    /* Custom scrollbar for product list */
    .prod-list::-webkit-scrollbar { width: 8px; }
    .prod-list::-webkit-scrollbar-track { background: var(--bg); border-radius: 4px; }
    .prod-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    .prod-list::-webkit-scrollbar-thumb:hover { background: var(--muted); }
    .prod-card {
      border: 1.5px solid var(--border); border-radius: 10px; padding: 12px;
      display: flex; gap: 11px; cursor: pointer; transition: all .2s;
      box-shadow: 0 1px 4px rgba(58,92,26,0.04);
    }
    .prod-card:hover { border-color: var(--yellow); background: var(--yellow-l); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(255,193,7,0.12); }
    .prod-card.active { border-left: 4px solid var(--yellow); border-color: var(--green); background: var(--green-l); }
    .prod-card.hidden { display: none; }
    .prod-img {
      width: 44px; height: 44px; background: var(--bg); border-radius: 6px;
      border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
      font-size: 1.25rem; flex-shrink: 0;
    }
    .prod-inf { flex: 1; }
    .prod-name { font-size: 0.77rem; font-weight: 700; color: var(--text); margin-bottom: 2px; line-height: 1.3; }
    .prod-spec { font-size: 0.67rem; color: var(--muted); font-weight: 500; }
    .prod-price { font-size: 0.8rem; font-weight: 800; color: var(--green); margin-top: 5px; }
    .prod-price span { color: var(--yellow-d); }
    .prod-sale { font-size: 0.62rem; font-weight: 700; color: #C0392B; background: #FDECEA; border-radius: 3px; padding: 1px 5px; margin-left: 6px; }
    .prod-old-price { font-size: 0.7rem; color: var(--muted); text-decoration: line-through; font-weight: 500; }
    .select-btn {
      margin-top: 6px; background: var(--yellow); border: none; color: var(--green-d);
      font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 0.68rem;
      padding: 5px 12px; border-radius: 5px; cursor: pointer; display: none;
      transition: background .2s;
    }
    .prod-card:hover .select-btn, .prod-card.active .select-btn { display: inline-block; }
    .prod-card.active .select-btn { background: var(--green); color: #fff; }
    .select-btn:hover { filter: brightness(0.92); }
    .no-results { text-align: center; color: var(--muted); font-size: 0.78rem; padding: 30px 0; display: none; }

    /* ── Peripherals Page ── */
    .periph-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 16px; padding: 28px;
    }
    .periph-card {
      background: var(--white); border: 1.5px solid var(--border); border-radius: 12px;
      padding: 20px 16px; cursor: pointer; transition: all .22s; text-align: center;
      box-shadow: var(--shadow);
    }
    .periph-card:hover { border-color: var(--yellow); transform: translateY(-2px); }
    .periph-card.done { border-color: var(--green); border-left: 4px solid var(--green); background: var(--green-l); }
    .periph-icon { font-size: 2rem; margin-bottom: 10px; }
    .periph-type { font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em; color: var(--yellow-d); margin-bottom: 5px; }
    .periph-name { font-size: 0.8rem; font-weight: 600; color: var(--text); line-height: 1.35; }
    .periph-price { font-size: 0.78rem; font-weight: 800; color: var(--green); margin-top: 8px; }
    .periph-check { display: none; font-size: 1rem; color: var(--green); font-weight: 900; margin-top: 6px; }
    .periph-card.done .periph-check { display: block; }
    .periph-toggle { font-size: 0.68rem; font-weight: 700; color: var(--muted); margin-top: 6px; }
    .periph-card.done .periph-toggle { color: #C0392B; }

    /* ── Summary Page ── */
    .summary-wrap { max-width: 820px; margin: 0 auto; padding: 32px 24px 80px; }
    .summary-title { font-size: 1.3rem; font-weight: 900; color: var(--green); margin-bottom: 6px; }
    .summary-sub { font-size: 0.78rem; color: var(--muted); margin-bottom: 28px; }
    .summary-section { margin-bottom: 24px; }
    .summary-section-title {
      font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em;
      color: var(--muted); margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1.5px solid var(--border);
    }
    .summary-item {
      display: flex; justify-content: space-between; align-items: center;
      padding: 11px 14px; background: var(--white); border-radius: 8px; margin-bottom: 6px;
      border: 1.5px solid var(--border);
    }
    .summary-item-left { display: flex; align-items: center; gap: 10px; }
    .summary-item-icon { font-size: 1.2rem; width: 44px; height: 44px; min-width: 44px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--bg); border: 1px solid var(--border); }
    .summary-item-icon img { width: 100%; height: 100%; object-fit: cover; }
    .summary-item-name { font-size: 0.82rem; font-weight: 600; }
    .summary-item-spec { font-size: 0.67rem; color: var(--muted); }
    .summary-item-price { font-size: 0.9rem; font-weight: 800; color: var(--green); }
    .summary-item.empty { opacity: 0.4; border-style: dashed; }
    .summary-total-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 14px 18px; background: var(--green); border-radius: 10px; margin-top: 14px;
    }
    .summary-total-lbl { color: rgba(255,255,255,0.75); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }
    .summary-total-val { color: var(--yellow); font-size: 1.6rem; font-weight: 900; }
    .summary-cta { margin-top: 24px; display: flex; gap: 12px; }
    .cta-primary {
      flex: 1; background: var(--yellow); border: none; color: var(--green-d);
      font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.85rem;
      letter-spacing: 0.08em; text-transform: uppercase;
      padding: 15px 28px; border-radius: 8px; cursor: pointer;
      transition: all .2s; box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    }
    .cta-primary:hover { background: var(--yellow-d); transform: translateY(-1px); }
    .cta-secondary {
      background: transparent; border: 2px solid var(--green); color: var(--green);
      font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.82rem;
      padding: 15px 22px; border-radius: 8px; cursor: pointer; transition: all .2s;
    }
    .cta-secondary:hover { background: var(--green-l); }

    /* ── Footer ── */
    .footer-bar {
      background: linear-gradient(90deg, var(--green-d) 0%, var(--green) 100%);
      padding: 13px 40px;
      display: flex; align-items: center; justify-content: space-between; gap: 20px;
      position: sticky; bottom: 0; z-index: 100;
      box-shadow: 0 -3px 16px rgba(58,92,26,0.22);
    }
    .ship { color: rgba(255,255,255,0.8); font-size: 0.77rem; }
    .ship strong { color: #fff; }
    .ship a { color: var(--yellow); font-size: 0.71rem; text-decoration: none; display: block; margin-top: 2px; }
    .total-block { text-align: right; }
    .total-lbl { font-size: 0.64rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.55); }
    .total-val { font-size: 1.5rem; font-weight: 900; color: var(--yellow); transition: all 0.4s; }
    .cart-btn {
      background: var(--yellow); border: none; color: var(--green-d);
      font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.83rem;
      letter-spacing: 0.09em; text-transform: uppercase;
      padding: 13px 28px; border-radius: 8px; cursor: pointer;
      display: flex; align-items: center; gap: 8px; white-space: nowrap;
      transition: all .2s; box-shadow: 0 4px 14px rgba(0,0,0,0.18);
    }
    .cart-btn:hover { background: var(--yellow-d); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.26); }

    /* ── Toast ── */
    .toast {
      position: fixed; bottom: 80px; right: 28px;
      background: var(--green); color: #fff;
      padding: 12px 18px; border-radius: 8px; font-size: 0.77rem; font-weight: 700;
      box-shadow: 0 6px 22px rgba(0,0,0,0.22); z-index: 999;
      transform: translateY(20px); opacity: 0; transition: all .35s cubic-bezier(.4,0,.2,1);
      display: flex; align-items: center; gap: 8px;
    }
    .toast.show { transform: translateY(0); opacity: 1; }

    /* ── Compatibility badge ── */
    .compat-badge {
      font-size: 0.65rem; font-weight: 700; padding: 3px 8px; border-radius: 12px;
      margin-left: 8px; vertical-align: middle;
    }
    .compat-ok { background: var(--green-l); color: var(--green); border: 1px solid #C5DCA0; }
    .compat-warn { background: #FFF5E0; color: #9A6A00; border: 1px solid #EDD080; }

    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

    /* ── Calculator Page (Step 0) ── */
    .calc-page {
      display: flex; align-items: center; justify-content: center;
      min-height: calc(100vh - 200px); padding: 40px 20px;
    }
    .calc-box {
      background: #fff; border: 3px solid var(--yellow); border-radius: 20px;
      padding: 50px 40px 40px; max-width: 680px; width: 100%; text-align: center;
      box-shadow: 0 8px 40px rgba(255,193,7,0.12);
      position: relative;
    }
    .calc-box .calc-bulb {
      width: 72px;
      height: 72px;
      background: radial-gradient(circle, #ffc107, #ff9800);
      border-radius: 50%; margin: -86px auto 18px;
      display: flex; align-items: center;
      justify-content: center;
      font-size: 2rem; box-shadow: 0 6px 18px rgba(255, 193, 7, 0.35);
      position: relative;
      transition: all 0.3s ease;
      animation: bulbGlow 2s ease-in-out infinite;
      cursor: pointer;
    }
    .calc-box .calc-bulb:hover {
      animation: bulbWiggle 0.5s ease-in-out, bulbGlow 2s ease-in-out infinite;
    }
    .calc-box .calc-bulb.active {
      animation: bulbWiggle 0.8s ease-in-out, bulbGlowActive 1.5s ease-in-out infinite;
    }
    .calc-box .calc-bulb::before {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255, 193, 7, 0.4), transparent);
      animation: pulseGlow 2s ease-in-out infinite;
      z-index: -1;
    }
    .calc-box h2 {
      font-size: 1.5rem; font-weight: 900; color: var(--text); margin-bottom: 6px;
    }
    .calc-box .calc-sub {
      font-size: 0.85rem; color: var(--muted); margin-bottom: 24px;
    }
    .calc-input-wrap {
      max-width: 340px; margin: 0 auto 8px;
    }
    .calc-input-wrap input {
      width: 100%; border: 2px solid var(--yellow); border-radius: 10px;
      padding: 14px 18px; font-family: 'Montserrat', sans-serif;
      font-size: 1.2rem; font-weight: 700; text-align: center;
      outline: none; color: var(--text); transition: border-color .2s;
    }
    .calc-input-wrap input:focus { border-color: var(--green); }
    .calc-input-wrap input::placeholder { color: #ccc; font-weight: 500; }
    .calc-input-label {
      font-size: 0.75rem; color: var(--muted); margin-bottom: 20px;
    }
    .calc-btn {
      background: var(--yellow); border: none; color: var(--green-d);
      font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.9rem;
      letter-spacing: 0.08em; text-transform: uppercase;
      padding: 14px 40px; border-radius: 30px; cursor: pointer;
      transition: all .2s; box-shadow: 0 4px 14px rgba(255,193,7,0.3);
    }
    .calc-btn:hover { background: var(--yellow-d); transform: translateY(-1px); }
    .calc-error {
      color: #C0392B; font-size: 0.78rem; font-weight: 600; margin-top: 10px; min-height: 20px;
    }
    .calc-results {
      display: none; margin-top: 28px;
      grid-template-columns: repeat(4, 1fr); gap: 14px;
    }
    .calc-results.show { display: grid; }
    .calc-result-card {
      background: var(--yellow-l); border: 1.5px solid var(--yellow);
      border-radius: 12px; padding: 18px 10px; text-align: center;
    }
    .calc-result-val {
      font-size: 1.5rem; font-weight: 900; color: var(--yellow-d);
    }
    .calc-result-lbl {
      font-size: 0.7rem; font-weight: 600; color: var(--muted); margin-top: 4px;
    }
    .calc-proceed {
      display: none; margin-top: 28px;
    }
    .calc-proceed.show { display: block; }
    .calc-proceed-btn {
      background: var(--green); border: none; color: #fff;
      font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.88rem;
      letter-spacing: 0.06em; text-transform: uppercase;
      padding: 15px 36px; border-radius: 10px; cursor: pointer;
      transition: all .2s; box-shadow: 0 4px 14px rgba(58,92,26,0.2);
      display: inline-flex; align-items: center; gap: 8px;
    }
    .calc-proceed-btn:hover { background: var(--green-d); transform: translateY(-1px); }
    .calc-skip {
      display: block; margin-top: 14px; font-size: 0.75rem; color: var(--muted);
      cursor: pointer; text-decoration: underline; transition: color .2s;
    }
    .calc-skip:hover { color: var(--green); }
    .calc-recommendation {
      display: none; margin-top: 22px; background: var(--green-l); border: 1.5px solid var(--green);
      border-radius: 10px; padding: 14px 18px; text-align: left;
    }
    .calc-recommendation.show { display: block; }
    .calc-recommendation .rec-title {
      font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;
      color: var(--green); margin-bottom: 6px;
    }
    .calc-recommendation .rec-item {
      font-size: 0.78rem; color: var(--text); font-weight: 600; padding: 3px 0;
      display: flex; align-items: center; gap: 6px;
    }
    .calc-recommendation .rec-item span { color: var(--muted); font-weight: 500; }

    @media (max-width: 767px) {
      .calc-box { padding: 40px 18px 28px; }
      .calc-box h2 { font-size: 1.2rem; }
      .calc-results { grid-template-columns: 1fr 1fr; }
      .calc-result-val { font-size: 1.2rem; }
    }
    @media (max-width: 400px) {
      .calc-results { grid-template-columns: 1fr; }
    }

    /* ── Mobile Drawer ── */
    .drawer-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.45); z-index: 200;
      opacity: 0; transition: opacity .3s;
    }
    .drawer-overlay.show { opacity: 1; }
    .drawer {
      position: fixed; bottom: 0; left: 0; right: 0;
      background: var(--white); border-radius: 18px 18px 0 0;
      z-index: 201; max-height: 82vh; display: flex; flex-direction: column;
      transform: translateY(100%); transition: transform .35s cubic-bezier(.4,0,.2,1);
      box-shadow: 0 -6px 32px rgba(0,0,0,0.18);
    }
    .drawer.open { transform: translateY(0); }
    .drawer-handle {
      width: 40px; height: 4px; background: var(--border);
      border-radius: 2px; margin: 12px auto 0;
    }
    .drawer-head {
      padding: 14px 18px 10px; border-bottom: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .drawer-head h3 { font-size: 0.88rem; font-weight: 800; color: var(--green); }
    .drawer-close {
      background: var(--bg); border: 1.5px solid var(--border); border-radius: 6px;
      width: 30px; height: 30px; cursor: pointer; font-size: 1rem;
      display: flex; align-items: center; justify-content: center;
    }
    .drawer-filters { display: flex; gap: 8px; padding: 10px 18px 0; }
    .drawer-search { margin: 10px 18px 6px; }
    .drawer-search input {
      width: 100%; background: var(--bg); border: 1.5px solid var(--border);
      border-radius: 8px; color: var(--text); font-family: 'Montserrat', sans-serif;
      font-size: 0.8rem; padding: 10px 13px 10px 36px; outline: none;
    }
    .drawer-search input:focus { border-color: var(--green); }
    .drawer-search input::placeholder { color: #B0BC9A; }
    .drawer-search-ico { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); }
    .drawer-search-wrap { position: relative; }
    .drawer-prod-list {
      flex: 1; overflow-y: auto; padding: 4px 18px 24px;
      display: flex; flex-direction: column; gap: 8px;
    }

    /* ── RESPONSIVE ── */

    /* Tablet: 768px–1024px — collapse chart into top bar, 2-col layout */
    @media (max-width: 1024px) {
      .top-bar-right { display: none; }
      .top-bar { padding: 8px 20px; }
      .site-header { padding: 0 20px; }
      .hbtn-ghost:not(:last-child) { display: none; }
      .step-nav { padding: 0 20px; }
      .step-tab { padding: 12px 16px; font-size: 0.7rem; }

      .builder { grid-template-columns: 1fr 320px; grid-template-rows: auto 1fr; }
      .radar-panel {
        grid-column: 1 / -1; grid-row: 1;
        flex-direction: row; align-items: flex-start; gap: 16px;
        padding: 14px 20px; border-right: none; border-bottom: 1.5px solid var(--border);
        flex-wrap: wrap;
      }
      .panel-lbl { display: none; }
      .chart-wrap { flex: 1; min-width: 280px; margin-bottom: 0; }
      .chart-wrap canvas { height: 180px !important; }
      .chart-location-row { display: none; }
      .chart-kwh-row { display: none; }
      .seasonal-badges { display: none; }
      .chart-legend { display: none; }
      .build-box { flex: 0 0 180px; min-width: 160px; margin-bottom: 0; }
      .stats { flex: 1; min-width: 200px; display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px; }
      .stat-row { padding: 5px 0; font-size: 0.72rem; }

      .comp-panel { grid-column: 1; grid-row: 2; }
      .sel-panel { grid-column: 2; grid-row: 2; }

      .footer-bar { padding: 12px 20px; }
    }

    /* Mobile: ≤767px — full single column, drawer for product selector */
    @media (max-width: 767px) {
      .top-bar { display: none; }
      .site-header { padding: 0 14px; height: 58px; }
      .logo-text .sub { display: none; }
      .hdr-btns { gap: 5px; }
      .hbtn { padding: 7px 10px; font-size: 0.68rem; }
      .hbtn-ghost { display: none; }

      .step-nav { padding: 0 14px; overflow-x: auto; }
      .step-tab { padding: 10px 14px; font-size: 0.68rem; white-space: nowrap; }

      .builder { grid-template-columns: 1fr; grid-template-rows: auto auto; }

      /* Chart collapses to compact view */
      .radar-panel {
        grid-column: 1; grid-row: 1;
        flex-direction: column; align-items: center;
        padding: 14px 14px; border-right: none; border-bottom: 1.5px solid var(--border);
      }
      .panel-lbl { display: none; }
      .chart-wrap { width: 100%; }
      .chart-wrap canvas { height: 160px !important; }
      .chart-location-row { display: none; }
      .chart-kwh-row { font-size: 0.5rem; }
      .seasonal-badges { display: none; }
      .build-box { width: 100%; margin-bottom: 10px; }
      .stats { width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px; }
      .stat-row { padding: 5px 0; font-size: 0.72rem; }

      /* Component list takes full width */
      .comp-panel { grid-column: 1; grid-row: 2; }

      /* Desktop selector panel: hidden on mobile (replaced by drawer) */
      .sel-panel { display: none; }

      /* Show drawer instead */
      .drawer-overlay { display: block; pointer-events: none; }
      .drawer-overlay.show { pointer-events: all; }

      .comp-list { padding: 12px 14px; gap: 8px; }
      .comp-head { padding: 14px 14px; }
      .comp-card { padding: 11px 12px; gap: 10px; }
      .comp-icon { width: 42px; height: 42px; font-size: 1.2rem; }
      .comp-name { font-size: 0.78rem; }

      .footer-bar { padding: 10px 14px; gap: 10px; flex-wrap: wrap; }
      .ship { font-size: 0.68rem; flex: 1 1 100%; order: 3; display: none; }
      .total-block { order: 1; }
      .total-val { font-size: 1.2rem; }
      .cart-btn { order: 2; padding: 11px 18px; font-size: 0.75rem; }

      /* Peripherals */
      .periph-grid { grid-template-columns: 1fr 1fr; padding: 14px; gap: 10px; }
      .periph-card { padding: 14px 12px; }

      /* Summary */
      .summary-wrap { padding: 20px 14px 80px; }
      .summary-cta { flex-direction: column; }
    }

    /* Large desktop: ≥1440px — wider columns, bigger chart */
    @media (min-width: 1440px) {
      .builder { grid-template-columns: 360px 1fr 400px; }
      .radar-panel { padding: 26px 22px; }
      .chart-wrap canvas { height: 230px !important; }
      .comp-list { padding: 20px 28px; gap: 13px; }
      .comp-card { padding: 16px 18px; gap: 16px; }
      .comp-icon { width: 60px; height: 60px; }
      .comp-name { font-size: 0.9rem; }
      .comp-head { padding: 20px 32px; }
      .comp-head h2 { font-size: 1.25rem; }
      .sel-head { padding: 20px 24px 16px; }
      .prod-list { padding: 8px 24px 18px; gap: 10px; }
      .prod-card { padding: 14px; }
      .prod-img { width: 52px; height: 52px; }
      .prod-name { font-size: 0.82rem; }
      .stat-row { font-size: 0.82rem; padding: 10px 14px; }
      .seasonal-badge { font-size: 0.66rem; padding: 5px 11px; }
      .chart-kwh-row { font-size: 0.64rem; }
      .footer-bar { padding: 14px 60px; }
      .step-nav { padding: 0 60px; }
    }

    /* Very small: ≤400px */
    @media (max-width: 400px) {
      .stats { grid-template-columns: 1fr; }
      .periph-grid { grid-template-columns: 1fr; }
      .step-tab { padding: 10px 10px; font-size: 0.63rem; gap: 5px; }
      .hbtn { padding: 6px 8px; font-size: 0.63rem; }
    }

    /* Animate in */
    @keyframes fadeSlideUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .comp-card { animation: fadeSlideUp 0.3s ease both; }
    .comp-card:nth-child(1) { animation-delay: 0.0s; }
    .comp-card:nth-child(2) { animation-delay: 0.06s; }
    .comp-card:nth-child(3) { animation-delay: 0.12s; }
    .comp-card:nth-child(4) { animation-delay: 0.18s; }
    .comp-card:nth-child(5) { animation-delay: 0.24s; }
    .comp-card:nth-child(6) { animation-delay: 0.30s; }

    /* ── Recommendation Progress Bars ── */
    .rec-bar-section { margin-top: 10px; border-top: 1px solid rgba(255,193,7,0.35); padding-top: 9px; }
    .rec-bar-item { margin-bottom: 9px; }
    .rec-bar-item:last-child { margin-bottom: 0; }
    .rec-bar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
    .rec-bar-lbl { font-size: 0.61rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; }
    .rec-bar-val { font-size: 0.61rem; font-weight: 800; color: var(--green); }
    .rec-bar-track { width: 100%; height: 7px; background: rgba(0,0,0,0.10); border-radius: 4px; overflow: hidden; }
    .rec-bar-fill { height: 100%; border-radius: 4px; transition: width 0.55s cubic-bezier(.4,0,.2,1), background 0.35s; }
    .rec-bar-fill.bar-none { width: 0% !important; background: var(--border); }
    .rec-bar-fill.bar-low  { background: #E74C3C; }
    .rec-bar-fill.bar-mid  { background: #E8A020; }
    .rec-bar-fill.bar-ok   { background: #27AE60; }
    .rec-bar-status { font-size: 0.58rem; font-weight: 700; margin-top: 2px; text-align: right; }
    .rec-bar-status.s-none { color: var(--muted); }
    .rec-bar-status.s-low  { color: #C0392B; }
    .rec-bar-status.s-mid  { color: #9A6A00; }
    .rec-bar-status.s-ok   { color: #27AE60; }

    /* ── Quantity Stepper (inside product cards) ── */
    .qty-row { display: flex; align-items: center; gap: 6px; margin-top: 7px; }
    .qty-stepper { display: flex; align-items: center; gap: 0; border: 1.5px solid var(--green); border-radius: 6px; overflow: hidden; }
    .qty-btn { background: var(--green); border: none; color: #fff; width: 26px; height: 26px;
      font-size: 1.1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center;
      font-family: 'Montserrat', sans-serif; transition: background .15s; flex-shrink: 0; }
    .qty-btn:hover { background: var(--green-d); }
    .qty-display { min-width: 28px; text-align: center; font-size: 0.8rem; font-weight: 800;
      color: var(--green); padding: 0 4px; background: #fff; height: 26px; line-height: 26px; }
    .remove-item-btn { background: transparent; border: 1.5px solid #F0C8C8; color: #C0392B;
      font-family: 'Montserrat', sans-serif; font-size: 0.67rem; font-weight: 700;
      padding: 4px 8px; border-radius: 5px; cursor: pointer; transition: all .15s; white-space: nowrap; }
    .remove-item-btn:hover { background: #FFF0EE; }

    /* ── Mismatch Warning Popup ── */
    .mismatch-popup-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.6); z-index: 10000;
      display: none; align-items: center; justify-content: center;
      animation: fadeInOverlay 0.25s ease;
    }
    .mismatch-popup-overlay.show { display: flex; }
    @keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }
    .mismatch-popup {
      background: #fff; border-radius: 16px; max-width: 420px; width: 90%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;
      animation: popupSlideIn 0.3s cubic-bezier(.4,0,.2,1);
    }
    @keyframes popupSlideIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .mismatch-popup-img { width: 100%; height: 160px; object-fit: cover; }
    .mismatch-popup-content { padding: 20px 24px 24px; }
    .mismatch-popup-title { font-size: 1.1rem; font-weight: 800; color: #C0392B; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    .mismatch-popup-title .icon { font-size: 1.3rem; }
    .mismatch-popup-list { margin: 0 0 16px 0; padding: 0; list-style: none; }
    .mismatch-popup-list li {
      background: #FFF5F5; border-left: 3px solid #E53935; padding: 10px 12px;
      margin-bottom: 8px; border-radius: 0 8px 8px 0; font-size: 0.78rem;
      color: #7A1A1A; line-height: 1.4;
    }
    .mismatch-popup-list li:last-child { margin-bottom: 0; }
    .mismatch-popup-btn {
      width: 100%; padding: 12px 20px; background: var(--green); color: #fff;
      border: none; border-radius: 8px; font-family: 'Montserrat', sans-serif;
      font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: background 0.2s;
    }
    .mismatch-popup-btn:hover { background: var(--green-d); }

    /* ── Bulb animations (mirrors savings-icon from index.php) ── */
    @keyframes bulbGlow {
      0%, 100% { box-shadow: 0 6px 18px rgba(255, 193, 7, 0.35); transform: scale(1); }
      50%       { box-shadow: 0 8px 25px rgba(255, 193, 7, 0.5);  transform: scale(1.05); }
    }
    @keyframes bulbGlowActive {
      0%, 100% { box-shadow: 0 8px 25px rgba(255,193,7,0.6), 0 0 40px rgba(255,193,7,0.3); transform: scale(1); }
      50%       { box-shadow: 0 10px 35px rgba(255,193,7,0.8), 0 0 60px rgba(255,193,7,0.5); transform: scale(1.08); }
    }
    @keyframes pulseGlow {
      0%, 100% { transform: scale(1);   opacity: 0.5; }
      50%       { transform: scale(1.3); opacity: 0.8; }
    }
    @keyframes bulbWiggle {
      0%,100% { transform: rotate(0deg);  }
      10%     { transform: rotate(-10deg); }
      20%     { transform: rotate(10deg);  }
      30%     { transform: rotate(-10deg); }
      40%     { transform: rotate(10deg);  }
      50%     { transform: rotate(-5deg);  }
      60%     { transform: rotate(5deg);   }
      70%     { transform: rotate(-2deg);  }
      80%     { transform: rotate(2deg);   }
      90%     { transform: rotate(0deg);   }
    }
    @keyframes bulbLightUp {
      0%   { filter: brightness(0.7); transform: scale(0.9); }
      50%  { filter: brightness(1.3); transform: scale(1.1); }
      100% { filter: brightness(1);   transform: scale(1);   }
    }
  </style>
</head>
<body>

<!-- Mismatch Warning Popup -->
<div class="mismatch-popup-overlay" id="mismatchPopupOverlay" onclick="closeMismatchPopup(event)">
  <div class="mismatch-popup" onclick="event.stopPropagation()">
    <img src="assets/img/pop-up-error.jpg" alt="Warning" class="mismatch-popup-img" onerror="this.style.display='none'">
    <div class="mismatch-popup-content">
      <div class="mismatch-popup-title"><span class="icon">PUTANG INA!🚫</span> Component Mismatch Detected</div>
      <ul class="mismatch-popup-list" id="mismatchPopupList"></ul>
      <button class="mismatch-popup-btn" onclick="closeMismatchPopup()">I Understand — Continue Anyway</button>
    </div>
  </div>
</div>





<!-- Step Nav -->
<nav class="step-nav" id="stepNav">
  <div class="step-tab active" data-step="0" onclick="solarGoToStep(0)">
    <div class="step-num">01</div> Your Bill
  </div>
  <div class="step-tab" data-step="1" onclick="solarGoToStep(1)">
    <div class="step-num">02</div> Components
  </div>
  <div class="step-tab" data-step="3" onclick="solarGoToStep(3)">
    <div class="step-num">03</div> Summary
  </div>
</nav>

<!-- ══════════════════ PAGE 0: CALCULATOR ══════════════════ -->
<div class="page active" id="page0">
  <div class="calc-page">
    <div class="calc-box">
      <div class="calc-bulb" onclick="this.style.animation='none'; setTimeout(()=>{ this.style.animation=''; }, 10);">💡</div>
      <h2>Let's check how much you can save!</h2>
      <p class="calc-sub">What's your monthly electric bill?</p>
      <div class="calc-input-wrap">
        <input type="number" id="calcBillInput" placeholder="0" min="0" step="0.01" />
        <div class="calc-input-label">Monthly Electric Bill (₱)</div>
      </div>
      <button class="calc-btn" onclick="runCalculator()">Calculate</button>
      <div class="calc-error" id="calcError"></div>
      <div class="calc-results" id="calcResults">
        <div class="calc-result-card">
          <div class="calc-result-val" id="calcKwp">0.0</div>
          <div class="calc-result-lbl">Required System Size (kWp)</div>
        </div>
        <div class="calc-result-card">
          <div class="calc-result-val" id="calcPanels">0</div>
          <div class="calc-result-lbl">Solar Panels</div>
        </div>
        <div class="calc-result-card">
          <div class="calc-result-val" id="calcMonthlySavings">₱0</div>
          <div class="calc-result-lbl">Monthly Savings (₱)</div>
        </div>
        <div class="calc-result-card">
          <div class="calc-result-val" id="calcYearlySavings">₱0</div>
          <div class="calc-result-lbl">Yearly Savings (₱)</div>
        </div>
      </div>
      <div class="calc-recommendation" id="calcRecommendation">
        <div class="rec-title"> Recommended System</div>
        <div class="rec-item"> <strong id="recPanels">–</strong> <span>Solar Panels</span></div>
        <div class="rec-item"> <strong id="recInverter">–</strong> <span>Inverter Size</span></div>
        <div class="rec-item"> <strong id="recBattery">–</strong> <span>Battery Capacity</span></div>
      </div>

      <!-- Monthly Savings Preview Chart -->
      <div id="calcChartSection" style="display:none; margin-top:22px; text-align:left;">
        <div style="font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:var(--green); margin-bottom:8px;">
           Estimated Monthly Savings
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:center; margin-bottom:12px; font-size:0.55rem; font-weight:700; color:var(--muted);">
          <span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:2px;background:#C8C8C8;display:inline-block;"></span> Current Bill</span>
          <span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:2px;background:#2D5016;display:inline-block;"></span> Peak Dry</span>
          <span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:2px;background:#3A5C1A;display:inline-block;"></span> Cool Dry</span>
          <span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:2px;background:#D35400;display:inline-block;"></span> Wet/Typhoon</span>
          <span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:2px;background:#689F38;display:inline-block;"></span> Improving</span>
        </div>
        <div style="position:relative; width:100%; height:200px;">
          <canvas id="calcPreviewChart"></canvas>
        </div>
        <div style="font-size:0.58rem; color:var(--muted); margin-top:8px; text-align:center; font-style:italic;">
          📡 Data source: NASA POWER satellite irradiance for <span id="calcCityLabel">Manila</span> · Updated seasonally
        </div>
      </div>
      <div class="calc-proceed" id="calcProceed">
        <button class="calc-proceed-btn" onclick="solarGoToStep(1)">Start Building Your System →</button>
      </div>
      <span class="calc-skip" onclick="solarGoToStep(1)">Skip — I already know what I need</span>
    </div>
  </div>
</div>

<!-- ══════════════════ PAGE 1: COMPONENTS ══════════════════ -->
<div class="page" id="page1">
<div class="builder">

  <!-- LEFT: Monthly Production Chart + Stats -->
  <div class="radar-panel">
    <div class="panel-lbl">Monthly Solar Production</div>

    <!-- Location Selector -->
    <div class="chart-location-row">
      <label>📍 Location:</label>
      <select id="chartCitySelect" onchange="onCityChange()">
        <option value="manila" selected>Manila / NCR</option>
      </select>
    </div>

    <!-- Custom Legend -->
    <div class="chart-legend">
      <span><span class="dot" style="background:#C8C8C8"></span> Without Solar</span>
      <span><span class="dot" style="background:#2E7D32"></span> Peak Dry (Mar-May)</span>
      <span><span class="dot" style="background:#43A047"></span> Cool Dry (Jan-Feb)</span>
      <span><span class="dot" style="background:#E65100"></span> Wet / Typhoon (Jul-Aug)</span>
      <span><span class="dot" style="background:#F57C00"></span> Monsoon (Jun, Sep)</span>
      <span><span class="dot" style="background:#66BB6A"></span> Improving (Nov-Dec)</span>
    </div>

    <!-- Bar Chart Canvas -->
    <div class="chart-wrap">
      <canvas id="monthlyProductionChart"></canvas>
    </div>

    <!-- Seasonal Insight Badges -->
   
    <div class="build-box">
      <div class="build-lbl">Build Category</div>
      <div class="build-val" id="buildCategoryVal">–</div>
      <div class="tier-bar"><div class="tier-fill" id="tierFill" style="width:0%"></div></div>
      <div class="tier-lbls"><span>Entry Level</span><span>Mid-Range</span><span>High End</span></div>
    </div>

  

    <!-- Bill Info (shown when calculator was used) -->
    <div id="billInfoBox" style="display:none; width:100%; background:var(--yellow-l); border:1.5px solid var(--yellow); border-radius:10px; padding:12px 15px; margin-bottom:14px;">
      <div style="font-size:0.64rem; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:var(--yellow-d); margin-bottom:4px;"> Your Monthly Bill</div>
      <div style="font-weight:900; font-size:1.1rem; color:var(--green);" id="billInfoVal">₱ 0</div>
      <div style="font-size:0.65rem; color:var(--muted); margin-top:6px;"> <strong>Recommended System</strong></div>
      <div style="font-size:0.65rem; color:var(--muted); margin-top:3px;"> Total Required: <strong id="billInfoKw">–</strong></div>
      <div style="font-size:0.65rem; color:var(--muted); margin-top:2px;"> Panels: <strong id="billInfoRec">–</strong></div>
      <div style="font-size:0.65rem; color:var(--muted); margin-top:2px;"> Inverter: <strong id="billInfoInverter">–</strong></div>
      <div style="font-size:0.65rem; color:var(--muted); margin-top:2px;"> Battery: <strong id="billInfoBattery">–</strong></div>
      <!-- Progress Bars -->
      <div class="rec-bar-section">
        <div class="rec-bar-item">
          <div class="rec-bar-header">
            <span class="rec-bar-lbl"> Panel Output</span>
            <span class="rec-bar-val" id="barPanelsVal">–</span>
          </div>
          <div class="rec-bar-track"><div class="rec-bar-fill bar-none" id="barPanelsFill" style="width:0%"></div></div>
          <div class="rec-bar-status s-none" id="barPanelsStatus">Not selected</div>
        </div>
        <div class="rec-bar-item">
          <div class="rec-bar-header">
            <span class="rec-bar-lbl"> Inverter Size</span>
            <span class="rec-bar-val" id="barInverterVal">–</span>
          </div>
          <div class="rec-bar-track"><div class="rec-bar-fill bar-none" id="barInverterFill" style="width:0%"></div></div>
          <div class="rec-bar-status s-none" id="barInverterStatus">Not selected</div>
        </div>
        <div class="rec-bar-item">
          <div class="rec-bar-header">
            <span class="rec-bar-lbl"> Battery Capacity</span>
            <span class="rec-bar-val" id="barBatteryVal">–</span>
          </div>
          <div class="rec-bar-track"><div class="rec-bar-fill bar-none" id="barBatteryFill" style="width:0%"></div></div>
          <div class="rec-bar-status s-none" id="barBatteryStatus">Not selected</div>
        </div>
        <div class="rec-bar-item">
          <div class="rec-bar-header">
            <span class="rec-bar-lbl"> Mounting System</span>
            <span class="rec-bar-val" id="barMountingVal">–</span>
          </div>
          <div class="rec-bar-track"><div class="rec-bar-fill bar-none" id="barMountingFill" style="width:0%"></div></div>
          <div class="rec-bar-status s-none" id="barMountingStatus">Not selected</div>
        </div>
        <div class="rec-bar-item">
          <div class="rec-bar-header">
            <span class="rec-bar-lbl">🔌 Wiring &amp; Protection</span>
            <span class="rec-bar-val" id="barWiringVal">–</span>
          </div>
          <div class="rec-bar-track"><div class="rec-bar-fill bar-none" id="barWiringFill" style="width:0%"></div></div>
          <div class="rec-bar-status s-none" id="barWiringStatus">Not selected</div>
        </div>
      </div>
    </div>

    <div class="stats">
      <div class="stat-row"><span class="stat-n">Est. Output</span><span class="stat-v" id="statOutput">–</span></div>
      <div class="stat-row"><span class="stat-n">Home Coverage</span><span class="stat-v" id="statCoverage">–</span></div>
      <div class="stat-row"><span class="stat-n">Daily Savings</span><span class="stat-v" id="statSavings">–</span></div>
      <div class="stat-row"><span class="stat-n">ROI Period</span><span class="stat-v" id="statROI">–</span></div>
      <div class="stat-row"><span class="stat-n">CO₂ Reduced</span><span class="stat-v" id="statCO2">–</span></div>
    </div>
  </div>

  <!-- CENTER: Component List -->
  <div class="comp-panel">
    <div class="comp-head">
      <h2>Select Your Components</h2>
      <button class="clear-btn" onclick="clearAll()">✕ Clear All</button>
    </div>
    <div class="comp-list" id="compList">

      <div class="comp-card" id="card-panels" onclick="openSelector('panels')">
        <div class="comp-icon" id="icon-panels"><img src="includes/solar_svg.png" alt="sola"></div>
        <div class="comp-info">
          <div class="comp-type">Solar Panels</div>
          <div class="comp-name" id="name-panels">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-panels">+ Select</span>
      </div>

      <div class="comp-card" id="card-inverter" onclick="openSelector('inverter')">
        <div class="comp-icon" id="icon-inverter"><img src="includes/inverter.png" alt="inverter"></div>
        <div class="comp-info">
          <div class="comp-type">Inverter</div>
          <div class="comp-name" id="name-inverter">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-inverter">+ Select</span>
      </div>

      <div class="comp-card" id="card-battery" onclick="openSelector('battery')">
        <div class="comp-icon" id="icon-battery"><img src="includes/battery.png" alt="battery"></div>
        <div class="comp-info">
          <div class="comp-type">Battery Storage</div>
          <div class="comp-name" id="name-battery">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-battery">+ Select</span>
      </div>

      <div class="comp-card" id="card-mounting" onclick="openSelector('mounting')">
        <div class="comp-icon" id="icon-mounting"><img src="includes/mounting.png" alt="mounting"></div>
        <div class="comp-info">
          <div class="comp-type">Mounting System</div>
          <div class="comp-name" id="name-mounting">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-mounting">+ Select</span>
      </div>

      <div class="comp-card" id="card-wiring" onclick="openSelector('wiring')">
        <div class="comp-icon" id="icon-wiring"><img src="includes/wiring-protection.png" alt="wiring"></div>
        <div class="comp-info">
          <div class="comp-type">Wiring & Protection</div>
          <div class="comp-name" id="name-wiring">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-wiring">+ Select</span>
      </div>

    </div>

    <!-- Compatibility Warnings -->
    <div class="compat-warnings" id="compatWarnings"></div>

  </div>

  <!-- RIGHT: Product Selector Panel -->
  <div class="sel-panel" id="selPanel">
    <div class="sel-head">
      <h3 id="selTitle">Select a component <span class="arr">›</span></h3>
      <div class="filter-row">
        <select class="f-sel" id="filterBrand" onchange="filterProducts()">
          <option value="">Brand: All</option>
        </select>
        <select class="f-sel" id="filterType" onchange="filterProductss()">
          <option value="">Type: All</option>
        </select>
      </div>
    </div>
    <div class="search-box">
      <span class="ico">🔍</span>
      <input type="text" id="searchInput" placeholder="Search products…" oninput="filterProducts()" />
    </div>
    <div class="prod-list" id="prodList">
      <div style="text-align:center; color:var(--muted); font-size:0.78rem; padding:40px 0; opacity:0.6;">
        ← Click a component to browse products
      </div>
    </div>
  </div>

</div>
</div>

<!-- ══════════════════ PAGE 2: PERIPHERALS ══════════════════ -->
<div class="page" id="page2">
  <div style="background:var(--white); padding:18px 28px; border-bottom:1.5px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
    <h2 style="font-size:1.1rem; font-weight:800; color:var(--green);">Add-On Services & Accessories</h2>
    <span style="font-size:0.75rem; color:var(--muted);">Optional upgrades for your solar system</span>
  </div>
  <div class="periph-grid" id="periphGrid"></div>
</div>

<!-- ══════════════════ PAGE 3: SUMMARY ══════════════════ -->
<div class="page" id="page3">
  <div class="summary-wrap">
    <div class="summary-title" style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
      <img src="/SolarPower-Energy-Corporation/assets/img/logo_no_background.png" alt="Solarpower LOGO" style="height:48px;width:auto;border-radius:8px;box-shadow:0 2px 12px rgba(58,92,26,0.09);background:#fff;padding:4px;">
      <span style="font-size:1.35rem;font-weight:900;color:var(--green);letter-spacing:0.04em;">SolarPower Energy Corporation</span>
    </div>
    <div class="summary-sub">Review your solar system configuration before adding to cart.</div>

    <div class="summary-section">
      <div class="summary-section-title">Core Components</div>
      <div id="summaryComponents"></div>
    </div>

    <div class="summary-section" id="summaryCompatSection" style="display:none;">
      <div class="summary-section-title">Compatibility Check</div>
      <div id="summaryCompat"></div>
    </div>

    <div class="summary-section" id="summaryStatsSection" style="display:none;">
      <div class="summary-section-title">System Performance Estimate</div>
      <div id="summaryStats"></div>
    </div>

    <div class="summary-section" id="summaryPeriphSection">
      <div class="summary-section-title">Add-On Services</div>
      <div id="summaryPeripherals"></div>
    </div>

    <div class="summary-total-row">
      <span class="summary-total-lbl">Total Build Cost</span>
      <span class="summary-total-val" id="summaryTotal">₱ 0.00</span>
    </div>


    <!-- Savings comparison (shown when calculator was used) -->
    <div id="summarySavingsSection" style="display:none; margin-top:18px; background:var(--yellow-l); border:2px solid var(--yellow); border-radius:12px; padding:18px 20px;">
      <div style="font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:0.13em; color:var(--yellow-d); margin-bottom:12px;"> Savings Estimate (based on your ₱<span id="summaryBillAmt">0</span>/mo bill)</div>
      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
        <div style="text-align:center;">
          <div style="font-size:1.2rem; font-weight:900; color:var(--green);" id="summaryMonthlySave">₱0</div>
          <div style="font-size:0.67rem; color:var(--muted); font-weight:600;">Monthly Savings</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.2rem; font-weight:900; color:var(--green);" id="summaryYearlySave">₱0</div>
          <div style="font-size:0.67rem; color:var(--muted); font-weight:600;">Yearly Savings</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.2rem; font-weight:900; color:var(--green);" id="summaryROI">–</div>
          <div style="font-size:0.67rem; color:var(--muted); font-weight:600;">Est. ROI Period</div>
        </div>
      </div>
    </div>

    <div class="summary-cta">
      <button class="cta-secondary" onclick="solarGoToStep(1)">← Edit Build</button>
      <button class="cta-primary" onclick="printBuildPDF()" style="display:flex; align-items:center; gap:10px; font-size:1rem;justify-content:center; font-weight:700;">
        <img src="assets/img/pdf.png" alt="PDF" style="width:50px; height:50px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
        Print to PDF
      </button>
    </div>
  </div>
</div>

<!-- Mobile Drawer (product selector) -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="drawer" id="drawer">
  <div class="drawer-handle"></div>
  <div class="drawer-head">
    <h3 id="drawerTitle">Select Product</h3>
    <button class="drawer-close" onclick="closeDrawer()">✕</button>
  </div>
  <div class="drawer-filters">
    <select class="f-sel" id="drawerFilterBrand" onchange="drawerFilter()">
      <option value="">Brand: All</option>
    </select>
    <select class="f-sel" id="drawerFilterType" onchange="drawerFilter()">
      <option value="">Type: All</option>
    </select>
  </div>
  <div class="drawer-search">
    <div class="drawer-search-wrap" style="position:relative">
      <span class="drawer-search-ico" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted)">🔍</span>
      <input type="text" id="drawerSearchInput" placeholder="Search…" oninput="drawerFilter()"
        style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:Montserrat,sans-serif;font-size:0.8rem;padding:10px 13px 10px 36px;outline:none;" />
    </div>
  </div>
  <div class="drawer-prod-list" id="drawerProdList"></div>
</div>

<!-- Footer -->
<div class="footer-bar">
  <div class="ship">
    <strong>📦 Nationwide Delivery</strong> · Metro Manila & all provinces
    <a href="#">View 0% installment options & DOE incentives →</a>
  </div>
  <div class="total-block">
    <div class="total-lbl">Subtotal</div>
    <div class="total-val" id="footerTotal">₱ 0.00</div>
  </div>
  <button class="cart-btn" onclick="solarGoToStep(3)">🛒 Review Build</button>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
// ─── DATA (loaded dynamically from database) ─────────────────────────────────

// Category metadata (static config)
const CATEGORY_META = {
  panels: { title: 'Solar Panels', filterLabels: ['Brand', 'Type'], filterKeys: ['brand', 'spec_type'] },
  inverter: { title: 'Inverter', filterLabels: ['Brand', 'Type'], filterKeys: ['brand', 'spec_type'] },
  battery: { title: 'Battery Storage', filterLabels: ['Brand', 'Type'], filterKeys: ['brand', 'spec_type'] },
  mounting: { title: 'Mounting System', filterLabels: ['Brand', 'Type'], filterKeys: ['brand', 'spec_type'] },
  wiring: { title: 'Wiring & Protection', filterLabels: ['Brand', 'Type'], filterKeys: ['brand', 'spec_type'] },
  monitoring: { title: 'Monitoring System', filterLabels: ['Brand', 'Type'], filterKeys: ['brand', 'spec_type'] }
};

// Products cache - loaded from database
const PRODUCTS = {
  panels: { ...CATEGORY_META.panels, items: [], loaded: false },
  inverter: { ...CATEGORY_META.inverter, items: [], loaded: false },
  battery: { ...CATEGORY_META.battery, items: [], loaded: false },
  mounting: { ...CATEGORY_META.mounting, items: [], loaded: false },
  wiring: { ...CATEGORY_META.wiring, items: [], loaded: false },
  monitoring: { ...CATEGORY_META.monitoring, items: [], loaded: false }
};

// Peripherals removed (table deleted)
const PERIPHERALS = [];

// API base path (relative to current page location)
const API_BASE = '/SolarPower-Energy-Corporation/controllers/solar-builder';

// ─── FETCH PRODUCTS FROM DATABASE ─────────────────────────────────────────────

// Parse real specs from product name and description
// Based on actual manufacturer datasheets (Aiko, Trina, Jinko, Deye, LuxPower, Solax, SRNE, etc.)
function parseProductSpecs(category, product) {
  const name = (product.name || '').toLowerCase();
  const desc = (product.description || product.spec || '').toLowerCase();
  const combined = name + ' ' + desc;
  const specs = {};

  switch (category) {
    case 'panels': {
      // Extract wattage: "580W", "635W", "705W" etc.
      const wMatch = combined.match(/(\d{3,4})\s*w(?:att|p)?/i);
      specs.watts = wMatch ? parseInt(wMatch[1]) : 0;
      
      // Efficiency from description (real datasheet values)
      const effMatch = desc.match(/([\d.]+)[\s-]*(?:to\s*[\d.]+)?%?\s*efficiency/i);
      if (effMatch) {
        specs.efficiency = parseFloat(effMatch[1]);
      } else {
        // Real-world defaults by brand/wattage based on manufacturer data:
        // Aiko ABC: 23.6-24.2%, Trina TOPCon: 22.5-23.1%, Jinko TOPCon: 22.3-22.8%
        // Lvtopsun PERC: 21.2-22.5%, Nuuko TOPCon: 22.0-22.6%, AE Solar: 21.0-22.0%
        if (combined.includes('aiko')) specs.efficiency = 23.8;
        else if (combined.includes('trina')) specs.efficiency = 22.8;
        else if (combined.includes('jinko')) specs.efficiency = 22.5;
        else if (combined.includes('nuuko')) specs.efficiency = 22.3;
        else if (combined.includes('lvtopsun') && specs.watts >= 580) specs.efficiency = 22.5;
        else if (combined.includes('lvtopsun')) specs.efficiency = 21.3;
        else if (combined.includes('austra') || combined.includes('austa')) specs.efficiency = 22.0;
        else if (combined.includes('aerosolar') || combined.includes('ae solar')) specs.efficiency = 21.5;
        else specs.efficiency = 21.0;
      }
      
      // Cell type from description
      if (combined.includes('abc') || combined.includes('all-back contact')) specs.cell_type = 'N-Type ABC';
      else if (combined.includes('topcon') || combined.includes('n-type')) specs.cell_type = 'N-Type TOPCon';
      else if (combined.includes('perc')) specs.cell_type = 'PERC';
      else if (combined.includes('hjt') || combined.includes('heterojunction')) specs.cell_type = 'HJT';
      else specs.cell_type = 'Monocrystalline';
      
      // Bifacial
      specs.bifacial = combined.includes('bifacial') || combined.includes('bificial');
      break;
    }
    
    case 'inverter': {
      // Extract kW: "10kW", "6kW", "5kW", "14kW", "15kW"
      const kwMatch = combined.match(/(\d+(?:\.\d+)?)\s*k\s*w/i);
      specs.kw = kwMatch ? parseFloat(kwMatch[1]) : 0;
      
      // Phase
      if (combined.includes('three phase') || combined.includes('3-phase') || combined.includes('3 phase')) {
        specs.phase = 'Three Phase';
      } else {
        specs.phase = 'Single Phase';
      }
      
      // Type
      if (combined.includes('hybrid')) specs.type = 'Hybrid';
      else if (combined.includes('grid-tie') || combined.includes('grid tie') || combined.includes('gti')) specs.type = 'Grid-Tie';
      else specs.type = 'Hybrid'; // Default for Philippine market
      
      // Efficiency: Real datasheet values
      const invEffMatch = desc.match(/(9[\d.]+)%\s*efficiency/i);
      if (invEffMatch) {
        specs.efficiency = parseFloat(invEffMatch[1]);
      } else {
        // Real values: Deye 97.5%, LuxPower 97.0%, Solax 97.8%, SRNE 96.5%
        if (combined.includes('deye')) specs.efficiency = 97.5;
        else if (combined.includes('luxpower') || combined.includes('lux power')) specs.efficiency = 97.0;
        else if (combined.includes('solax')) specs.efficiency = 97.8;
        else if (combined.includes('srne')) specs.efficiency = 96.5;
        else specs.efficiency = 96.0;
      }
      
      // Voltage level (all Philippine residential inverters in your catalog are LV/48V)
      specs.battery_voltage = 48; // Low Voltage standard
      break;
    }
    
    case 'battery': {
      // Extract kWh: "5.12kWh", "10.24kwh", "16.07kwh"
      const kwhMatch = combined.match(/([\d.]+)\s*kwh/i);
      specs.kwh = kwhMatch ? parseFloat(kwhMatch[1]) : 0;
      
      // Voltage: "100V", "48V", "51.2V"
      const vMatch = combined.match(/([\d.]+)\s*v(?:olt)?(?:\/piece)?/i);
      specs.voltage = vMatch ? parseFloat(vMatch[1]) : 51.2; // Default LiFePO4 nominal
      
      // Normalize: 100V is actually a Hoymiles high-voltage module
      // 48-51.2V is standard LV, 100V+ is HV
      specs.voltage_class = specs.voltage >= 80 ? 'HV' : 'LV';
      
      // Chemistry (all products in catalog are LiFePO4)
      if (combined.includes('lifepo') || combined.includes('lfp') || combined.includes('lithium iron')) {
        specs.chemistry = 'LiFePO4';
      } else {
        specs.chemistry = 'LiFePO4'; // Standard for modern solar storage
      }
      
      // Cycle life: Real values ~6000 for LiFePO4
      const cycleMatch = desc.match(/([\d,]+)[\s-]*cycle/i);
      specs.cycle_life = cycleMatch ? parseInt(cycleMatch[1].replace(',', '')) : 6000;
      
      // DoD (Depth of Discharge): Standard 90-95% for LiFePO4
      specs.dod = 0.95;
      break;
    }
    
    case 'mounting': {
      // Extract max panels: "Up to 15 Panels", "Up to 25 Panels", "Up to 40 Panels"
      const panelMatch = combined.match(/(?:up\s*to\s*)?(\d+)\s*panel/i);
      specs.max_panels = panelMatch ? parseInt(panelMatch[1]) : 0;
      
      // Mount type
      if (combined.includes('ground')) specs.mount_type = 'Ground Mount';
      else if (combined.includes('flat roof') || combined.includes('ballast')) specs.mount_type = 'Flat Roof';
      else if (combined.includes('carport')) specs.mount_type = 'Carport';
      else if (combined.includes('roof')) specs.mount_type = 'Roof Mount';
      else specs.mount_type = 'Universal';
      
      // Material
      if (combined.includes('aluminum') || combined.includes('aluminium')) specs.material = 'Aluminum';
      else if (combined.includes('galvanized steel') || combined.includes('steel')) specs.material = 'Galvanized Steel';
      else specs.material = 'Aluminum';
      
      // Wind rating (Philippine typhoon standard)
      const windMatch = desc.match(/(\d+)\s*km\/h/i);
      specs.wind_rating_kmh = windMatch ? parseInt(windMatch[1]) : 150; // Default PH standard
      break;
    }
    
    case 'wiring': {
      // Extract max system kW: "Up to 5kW", "Up to 10kW", "Up to 15kW", "20kW+"
      const kwMatch = combined.match(/(?:up\s*to\s*)?(\d+)\s*kw/i);
      specs.max_kw = kwMatch ? parseInt(kwMatch[1]) : 0;
      
      // Cable size from description
      const cableMatch = desc.match(/(\d+)\s*mm/i);
      specs.cable_mm2 = cableMatch ? parseInt(cableMatch[1]) : 4;
      
      // Protection level
      if (combined.includes('industrial') || combined.includes('heavy')) specs.grade = 'Industrial';
      else if (combined.includes('premium')) specs.grade = 'Premium';
      else if (combined.includes('standard') || combined.includes('professional')) specs.grade = 'Standard';
      else specs.grade = 'Basic';
      
      // SPD type
      if (combined.includes('type i+ii') || combined.includes('type i &')) specs.spd = 'Type I+II';
      else if (combined.includes('type ii')) specs.spd = 'Type II';
      else specs.spd = 'Type II';
      break;
    }
    
    case 'monitoring': {
      // Connectivity
      if (combined.includes('ethernet') && combined.includes('wifi')) specs.connectivity = 'WiFi + Ethernet';
      else if (combined.includes('wifi') && combined.includes('cloud')) specs.connectivity = 'WiFi + Cloud';
      else if (combined.includes('wifi')) specs.connectivity = 'WiFi';
      else specs.connectivity = 'WiFi';
      
      // Has display
      specs.has_display = combined.includes('display') || combined.includes('touchscreen') || combined.includes('screen');
      
      // Has energy meter
      specs.has_energy_meter = combined.includes('energy meter') || combined.includes('smart meter');
      
      // Module-level monitoring
      specs.module_level = combined.includes('module-level') || combined.includes('optimizer');
      
      // API access
      specs.has_api = combined.includes('api') || combined.includes('modbus') || combined.includes('mqtt');
      
      // Monitoring score (0-1) based on features
      let score = 0.3; // base WiFi monitoring
      if (specs.has_display) score += 0.2;
      if (specs.has_energy_meter) score += 0.2;
      if (specs.module_level) score += 0.15;
      if (specs.has_api) score += 0.15;
      specs.score = Math.min(1, score);
      break;
    }
  }
  
  return specs;
}

async function fetchProducts(category) {
  if (PRODUCTS[category].loaded && PRODUCTS[category].items.length > 0) {
    return PRODUCTS[category];
  }

  const list = document.getElementById('prodList');
  list.innerHTML = '<div style="text-align:center;color:var(--muted);font-size:0.78rem;padding:40px 0;">Loading products...</div>';

  try {
    const response = await fetch(`${API_BASE}/get_products.php?category=${category}`);
    const data = await response.json();

    if (data.success && data.products) {
      // Transform API response to match expected format
      PRODUCTS[category].items = data.products.map(p => {
        const parsedSpecs = parseProductSpecs(category, {
          name: p.name,
          description: p.description || '',
          spec: p.spec_summary || '',
          brand: p.brand
        });
        return {
          id: String(p.id),
          name: p.name,
          spec: p.spec_summary || p.description?.substring(0, 80) || `${p.brand} · ${p.warranty || 'Standard warranty'}`,
          price: p.price,
          brand: p.brand,
          spec_type: p.specs?.inverter_type || p.specs?.chemistry || p.specs?.mount_type || p.specs?.kit_type || p.specs?.connectivity || p.specs?.cell_type || 'Standard',
          image: p.image,
          stock: p.stock,
          warranty: p.warranty,
          description: p.description || '',
          parsedSpecs: parsedSpecs
        };
      });
      PRODUCTS[category].loaded = true;

      // Update filter labels from API if available
      if (data.category) {
        PRODUCTS[category].filterLabels = data.category.filter_labels || PRODUCTS[category].filterLabels;
        // Keep filterKeys as ['brand', 'spec_type'] since that's how items are mapped
      }
    } else {
      console.error('Failed to load products:', data.error);
      PRODUCTS[category].items = [];
    }
  } catch (err) {
    console.error('Error fetching products:', err);
    PRODUCTS[category].items = [];
  }

  return PRODUCTS[category];
}

// ─── STATE ────────────────────────────────────────────────────────────────────

const selected = { panels: [], inverter: [], battery: [], mounting: [], wiring: [], monitoring: [] };
const selectedPeriphs = new Set();
let activeCategory = null;

// Helper: get first item in a selected category (for spec/stat lookups)
function selFirst(cat) { return selected[cat].length > 0 ? selected[cat][0].item : null; }
// Helper: total qty across a category
function selTotalQty(cat) { return selected[cat].reduce((s, e) => s + e.qty, 0); }
// Helper: whether any items are selected in a category
function selHas(cat) { return selected[cat].length > 0; }

// ─── MONTHLY PRODUCTION CHART (replaces radar) ───────────────────────────────

// Philippine monthly solar data — loaded from NASA POWER API with static fallback
const MONTH_LABELS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// Default Philippine monthly multipliers (vs annual average)
// Source: NASA POWER satellite data, averaged across PH (2020-2025)
const DEFAULT_MONTHLY_MULTIPLIERS = {
  1: 1.08, 2: 1.13, 3: 1.22, 4: 1.28, 5: 1.20, 6: 0.98,
  7: 0.84, 8: 0.82, 9: 0.86, 10: 0.93, 11: 1.02, 12: 1.04
};

// Default peak sun hours per month (Manila baseline)
const DEFAULT_PEAK_SUN_HOURS_MONTHLY = {
  1: 4.40, 2: 4.72, 3: 5.20, 4: 5.50, 5: 5.15, 6: 4.10,
  7: 3.50, 8: 3.40, 9: 3.60, 10: 3.90, 11: 4.20, 12: 4.30
};

// Season info for tooltips
const SEASON_INFO = {
  1:  { season: 'Cool Dry (Amihan)',      emoji: '', note: 'NE monsoon, mostly clear' },
  2:  { season: 'Cool Dry (Amihan)',      emoji: '',  note: 'Dry season starting' },
  3:  { season: 'Hot Dry Season',         emoji: '',  note: 'Excellent solar conditions' },
  4:  { season: 'Peak Dry Season',        emoji: '',  note: 'Best month for solar' },
  5:  { season: 'Hot Dry (Late)',         emoji: '',  note: 'Still excellent output' },
  6:  { season: 'Wet Season (Habagat)',   emoji: '', note: 'SW monsoon begins' },
  7:  { season: 'Wet Season (Peak)',      emoji: '', note: 'Heavy monsoon rains' },
  8:  { season: 'Wet + Typhoons',         emoji: '',  note: 'Typhoon season peak — lowest production' },
  9:  { season: 'Wet (Ber Month)',        emoji: '',  note: 'Rainy, typhoon risk continues' },
  10: { season: 'Transition (Ber Month)', emoji: '',  note: 'Monsoon weakening, improving slowly' },
  11: { season: 'Amihan Returns',         emoji: '', note: 'NE monsoon, drier — recovering output' },
  12: { season: 'Cool Dry (Ber Month)',   emoji: '', note: 'Good production, shorter daylight' },
  11: { season: 'Cool Dry (Amihan)',      emoji: '', note: 'Dry season returning' },
  12: { season: 'Cool Dry (Amihan)',      emoji: '',  note: 'Good but shorter days' }
};

// Current active solar data (may be updated by API)
let currentSolarData = {
  multipliers: { ...DEFAULT_MONTHLY_MULTIPLIERS },
  peakSunHours: { ...DEFAULT_PEAK_SUN_HOURS_MONTHLY },
  cityName: 'Manila / NCR',
  source: 'static'
};

// Chart.js instance
let monthlyChart = null;

/**
 * Calculate monthly production (kWh) for each month given system kW.
 * Formula per month: systemKW × peakSunHours[month] × systemEfficiency × daysInMonth
 * Applies seasonal multiplier from NASA data.
 */
function getMonthlyProduction(systemKW) {
  const SYSTEM_EFFICIENCY = 0.85;
  const DAYS_IN_MONTH = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  
  const production = [];
  for (let m = 1; m <= 12; m++) {
    const psh = currentSolarData.peakSunHours[m] || 4.5;
    const days = DAYS_IN_MONTH[m - 1];
    const kwhMonth = systemKW * psh * SYSTEM_EFFICIENCY * days;
    production.push(Math.round(kwhMonth));
  }
  return production;
}

/**
 * Calculate monthly savings (₱) from production.
 */
function getMonthlySavings(productionArray) {
  const ELECTRIC_RATE = 13.40; // ₱/kWh Meralco average
  return productionArray.map(kwh => Math.round(kwh * ELECTRIC_RATE));
}

/**
 * Get the "without solar" monthly consumption (from bill input).
 * Distributes the monthly bill's kWh evenly but applies seasonal demand patterns.
 * In Philippines, consumption is higher in summer (aircon) and lower in cool months.
 */
function getMonthlyConsumption(monthlyBill) {
  const ELECTRIC_RATE = 13.40; // ₱/kWh average rate
  const monthlyKwh = monthlyBill / ELECTRIC_RATE;
  
  // Philippine household consumption multipliers (aircon usage pattern)
  const CONSUMPTION_MULTIPLIERS = {
    1: 0.92, 2: 0.95, 3: 1.08, 4: 1.15, 5: 1.12,  6: 1.05,
    7: 1.00, 8: 0.98, 9: 0.95, 10: 0.93, 11: 0.90, 12: 0.92
  };
  
  const consumption = [];
  for (let m = 1; m <= 12; m++) {
    consumption.push(Math.round(monthlyKwh * CONSUMPTION_MULTIPLIERS[m]));
  }
  return consumption;
}

/**
 * Initialize or update the monthly production bar chart.
 */
function renderMonthlyChart(systemKW) {
  const canvas = document.getElementById('monthlyProductionChart');
  if (!canvas) return;
  
  // If no system selected yet, show placeholder
  if (!systemKW || systemKW <= 0) {
    if (monthlyChart) {
      monthlyChart.destroy();
      monthlyChart = null;
    }
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#B0BC9A';
    ctx.font = '12px Montserrat, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Select components to see monthly production', canvas.width / 2, canvas.height / 2);
    
    // Reset kWh row
    const kwhRow = document.getElementById('chartKwhRow');
    if (kwhRow) kwhRow.innerHTML = Array(12).fill('<span>–</span>').join('');
    return;
  }

  const production = getMonthlyProduction(systemKW);
  const savings = getMonthlySavings(production);
  
  // Get consumption data if bill was entered
  const bill = calcData?.bill || 0;
  const consumption = bill > 0 ? getMonthlyConsumption(bill) : null;
  
  // Color bars by 5 Philippine seasons for full-year visibility
  const barColors = production.map((_, i) => {
    const m = i + 1;
    if (m >= 3 && m <= 5) return '#2E7D32';    // Peak Dry (Mar-May) — deep green, best output
    if (m === 1 || m === 2) return '#43A047';   // Cool Dry Amihan (Jan-Feb) — medium green, good
    if (m === 11 || m === 12) return '#66BB6A'; // Ber-end / Amihan return (Nov-Dec) — light green, recovering
    if (m === 7 || m === 8) return '#E65100';   // Peak Wet + Typhoons (Jul-Aug) — burnt orange, lowest
    if (m === 6 || m === 9) return '#F57C00';   // Wet shoulders (Jun, Sep) — orange, low
    return '#FFA726';                            // Transition (Oct) — amber, moderate
  });
  
  const datasets = [];
  
  // Consumption bars (gray, behind) — only if bill was entered
  if (consumption) {
    datasets.push({
      label: 'Without Solar (kWh)',
      data: consumption,
      backgroundColor: '#C8C8C8',
      borderColor: '#AAAAAA',
      borderWidth: 1,
      borderRadius: 2,
      order: 2,
      barPercentage: 0.7,
      categoryPercentage: 0.8,
    });
  }
  
  // Production bars (green, in front)
  datasets.push({
    label: 'Solar Production (kWh)',
    data: production,
    backgroundColor: barColors,
    borderColor: barColors.map(c => c),
    borderWidth: 1,
    borderRadius: 3,
    order: 1,
    barPercentage: 0.7,
    categoryPercentage: 0.8,
  });

  if (monthlyChart) {
    // Update existing chart
    monthlyChart.data.datasets = datasets;
    monthlyChart.options.scales.y.max = Math.ceil(Math.max(...production, ...(consumption || [0])) / 50) * 50 + 50;
    monthlyChart.update('active');
  } else {
    // Create new chart
    const ctx = canvas.getContext('2d');
    monthlyChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: MONTH_LABELS,
        datasets: datasets
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600, easing: 'easeOutQuart' },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(58,92,26,0.95)',
            titleFont: { family: 'Montserrat', size: 11, weight: '700' },
            bodyFont: { family: 'Montserrat', size: 10 },
            padding: 10,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              title: function(items) {
                const idx = items[0].dataIndex;
                const m = idx + 1;
                const info = SEASON_INFO[m];
                const fullMonths = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                return info.emoji + ' ' + fullMonths[idx] + ' — ' + info.season;
              },
              afterTitle: function(items) {
                const m = items[0].dataIndex + 1;
                return SEASON_INFO[m].note;
              },
              label: function(context) {
                if (context.dataset.label.includes('Without')) {
                  return '  ⚡ Without Solar: ' + context.parsed.y + ' kWh';
                }
                const idx = context.dataIndex;
                const sav = savings[idx];
                return '   Solar Output: ' + context.parsed.y + ' kWh (₱' + sav.toLocaleString() + ' saved)';
              },
              afterBody: function(items) {
                const m = items[0].dataIndex + 1;
                const mult = currentSolarData.multipliers[m];
                const pct = ((mult - 1) * 100).toFixed(0);
                const prefix = pct >= 0 ? '+' : '';
                return '\n   ' + prefix + pct + '% vs annual average';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { family: 'Montserrat', size: 9, weight: '600' },
              color: '#6B7C52'
            }
          },
          y: {
            beginAtZero: true,
            max: Math.ceil(Math.max(...production, ...(consumption || [0])) / 50) * 50 + 50,
            grid: { color: 'rgba(221,227,212,0.5)', drawBorder: false },
            ticks: {
              font: { family: 'Montserrat', size: 9, weight: '600' },
              color: '#6B7C52',
              stepSize: 50,
              callback: function(value) { return value; }
            },
            title: {
              display: true,
              text: 'kWh',
              font: { family: 'Montserrat', size: 9, weight: '700' },
              color: '#6B7C52'
            }
          }
        }
      }
    });
  }

  // Update kWh row beneath chart
  const kwhRow = document.getElementById('chartKwhRow');
  if (kwhRow) {
    kwhRow.innerHTML = production.map(v => `<span>${v}</span>`).join('');
  }

  // Update seasonal badges — show all 5 season groups
  const badgesEl = document.getElementById('seasonalBadges');
  if (badgesEl) {
    const peakMonth = production.indexOf(Math.max(...production)) + 1;
    const lowMonth = production.indexOf(Math.min(...production)) + 1;
    const peakPct = ((currentSolarData.multipliers[peakMonth] - 1) * 100).toFixed(0);
    const lowPct = ((1 - currentSolarData.multipliers[lowMonth]) * 100).toFixed(0);
    
    const avgSavings = savings.reduce((a, b) => a + b, 0) / 12;

    // Jan-Feb average
    const janFebAvg = ((currentSolarData.multipliers[1] + currentSolarData.multipliers[2]) / 2 - 1) * 100;
    // Jun+Sep average
    const monsoonAvg = (1 - (currentSolarData.multipliers[6] + currentSolarData.multipliers[9]) / 2) * 100;
    // Nov-Dec average
    const novDecAvg = ((currentSolarData.multipliers[11] + currentSolarData.multipliers[12]) / 2 - 1) * 100;
    
    badgesEl.innerHTML = `
      <span class="seasonal-badge peak"> Peak: ${MONTH_LABELS[peakMonth-1]} (+${peakPct}%)</span>
      <span class="seasonal-badge" style="background:rgba(67,160,71,0.12);color:#43A047"> Jan-Feb: +${janFebAvg.toFixed(0)}% (Amihan)</span>
      <span class="seasonal-badge low"> Lowest: ${MONTH_LABELS[lowMonth-1]} (-${lowPct}%)</span>
      <span class="seasonal-badge" style="background:rgba(245,124,0,0.12);color:#E65100"> Jun & Sep: -${monsoonAvg.toFixed(0)}% (Monsoon)</span>
      <span class="seasonal-badge" style="background:rgba(102,187,106,0.12);color:#558B2F"> Nov-Dec: +${novDecAvg.toFixed(0)}% (Recovering)</span>
      <span class="seasonal-badge info"> Avg savings: ₱${Math.round(avgSavings).toLocaleString()}/mo</span>
    `;
  }
}

/**
 * Fetch solar data from API for selected city.
 */
async function fetchSolarData(cityKey) {
  try {
    const res = await fetch(API_BASE + '/get_solar_data.php?city=' + encodeURIComponent(cityKey));
    const data = await res.json();
    
    if (data.success && data.monthly) {
      const multipliers = {};
      const peakSunHours = {};
      
      for (let m = 1; m <= 12; m++) {
        const md = data.monthly[m];
        if (md) {
          multipliers[m] = md.multiplier;
          peakSunHours[m] = md.peak_sun_hours;
        }
      }
      
      currentSolarData = {
        multipliers,
        peakSunHours,
        cityName: data.city?.name || cityKey,
        source: 'nasa-power'
      };
      
      // Populate city dropdown if cities available
      if (data.cities && data.cities.length > 0) {
        const sel = document.getElementById('chartCitySelect');
        if (sel && sel.options.length <= 1) {
          sel.innerHTML = data.cities.map(c =>
            `<option value="${c.value}" ${c.value === cityKey ? 'selected' : ''}>${c.label}</option>`
          ).join('');
        }
      }
      
      return true;
    }
  } catch (err) {
    console.warn('[SolarData] API unavailable, using static data:', err.message);
  }
  return false;
}

/**
 * Handle city dropdown change.
 */
async function onCityChange() {
  const sel = document.getElementById('chartCitySelect');
  const cityKey = sel?.value || 'manila';
  
  await fetchSolarData(cityKey);
  
  // Re-render chart with current system
  const systemKW = getCurrentSystemKW();
  renderMonthlyChart(systemKW);
  updateStats();
}

/**
 * Get current system KW from selected components.
 */
function getCurrentSystemKW() {
  let systemKW = 0;
  if (selected.inverter.length > 0) {
    systemKW = selected.inverter.reduce((s, e) => s + e.qty * (e.item.parsedSpecs.kw || 0), 0);
  } else if (selected.panels.length > 0) {
    const totalPanelW = selected.panels.reduce((s, e) => s + e.qty * (e.item.parsedSpecs.watts || 0), 0);
    systemKW = totalPanelW / 1000;
  }
  return systemKW;
}

/**
 * Init: Load solar data for Manila on page load.
 */
(async function initSolarData() {
  await fetchSolarData('manila');
  renderMonthlyChart(0); // Show placeholder until components chosen
})();

// ─── STATS ────────────────────────────────────────────────────────────────────

function updateStats() {
  const panelItem = selFirst('panels');
  const invItem   = selFirst('inverter');
  const battItem  = selFirst('battery');

  // Determine system capacity (kW)
  // For multiple inverters: sum their kW
  let systemKW = getCurrentSystemKW();

  if (systemKW > 0) {
    // ── Philippine Solar Constants (sourced from NREL, DOE PH, Meralco) ──
    const ELECTRIC_RATE = 11.50;
    const CO2_FACTOR_PER_KWP = 1.12;
    const SYSTEM_EFFICIENCY = 0.80;

    // Use monthly data for accurate annual calculation
    const monthlyProduction = getMonthlyProduction(systemKW);
    const annualKwh = monthlyProduction.reduce((a, b) => a + b, 0);
    const dailyAvgKwh = annualKwh / 365;
    const dailySavings = dailyAvgKwh * ELECTRIC_RATE;
    const annualSavings = annualKwh * ELECTRIC_RATE;
    
    // ROI period (simple payback)
    const totalCost = getSubtotal();
    const roiYears = annualSavings > 0 ? (totalCost / annualSavings) : 0;
    // CO₂ reduction (tons/year)
    const co2Reduced = systemKW * CO2_FACTOR_PER_KWP;

    // Home coverage category (based on Philippine household averages)
    let coverage = '–';
    if (systemKW < 2) coverage = 'Partial (Lights & Fans)';
    else if (systemKW < 3) coverage = 'Basic Home';
    else if (systemKW < 5) coverage = 'Standard Home';
    else if (systemKW < 8) coverage = 'Large Home';
    else if (systemKW < 12) coverage = 'Full Home + AC';
    else coverage = 'Commercial Grade';

    document.getElementById('statOutput').textContent = systemKW.toFixed(1) + ' kWp';
    document.getElementById('statCoverage').textContent = coverage;
    document.getElementById('statSavings').textContent = '~₱' + Math.round(dailySavings).toLocaleString() + ' / day';
    document.getElementById('statROI').textContent = roiYears > 0 ? roiYears.toFixed(1) + ' yrs' : '–';
    document.getElementById('statCO2').textContent = co2Reduced.toFixed(1) + ' t / yr';
    
    // Update monthly production chart
    renderMonthlyChart(systemKW);
  } else {
    ['statOutput','statCoverage','statSavings','statROI','statCO2'].forEach(id => {
      document.getElementById(id).textContent = '–';
    });
    renderMonthlyChart(0);
  }
  checkCompatibility();
  updateBuildCategory();
  updateTotal();
}

// ─── COMPATIBILITY VALIDATION ─────────────────────────────────────────────────
// Checks for component mismatches based on real solar engineering rules

function checkCompatibility() {
  const warnings = getCompatibilityWarnings();

  // ── Update UI: Component card warning states ──
  Object.keys(selected).forEach(cat => {
    const card = document.getElementById('card-' + cat);
    const tag  = document.getElementById('tag-' + cat);
    if (card) card.classList.remove('warn');
    if (tag && selHas(cat)) {
      const tq = selTotalQty(cat);
      tag.textContent = tq > 1 ? `✓ ${tq} added` : '✓ Added';
      tag.className = 'comp-tag tag-g';
    }
  });

  const errorCats = new Set();
  const warnCats  = new Set();
  warnings.forEach(w => {
    if (w.severity === 'error') w.cats.forEach(cat => errorCats.add(cat));
    else if (w.severity === 'warn') w.cats.forEach(cat => warnCats.add(cat));
  });

  errorCats.forEach(cat => {
    const card = document.getElementById('card-' + cat);
    const tag  = document.getElementById('tag-' + cat);
    if (card && selHas(cat)) {
      card.classList.add('warn');
      if (tag) { tag.textContent = '⚠ Mismatch'; tag.className = 'comp-tag tag-w'; }
    }
  });
  warnCats.forEach(cat => {
    if (errorCats.has(cat)) return;
    const card = document.getElementById('card-' + cat);
    const tag  = document.getElementById('tag-' + cat);
    if (card && selHas(cat)) {
      card.classList.add('warn');
      if (tag) { tag.textContent = '⚠ Caution'; tag.className = 'comp-tag tag-w'; }
    }
  });

  // ── Show Popup for Error-level Mismatches ──
  const errorWarnings = warnings.filter(w => w.severity === 'error');
  if (errorWarnings.length > 0) {
    showMismatchPopup(errorWarnings);
  }

  // ── Update Compatibility Warnings Panel ──
  const warnPanel = document.getElementById('compatWarnings');
  if (!warnPanel) return;

  if (warnings.length > 0) {
    const icons  = { error: '🚫', warn: '⚠️', info: 'ℹ️' };
    const colors = {
      error: 'background:#FFF0F0;border-color:#E53935;color:#B71C1C;',
      warn:  'background:#FFF8E1;border-color:#E8A020;color:#7A5200;',
      info:  'background:#E3F2FD;border-color:#1976D2;color:#0D47A1;'
    };
    warnPanel.innerHTML = warnings.map(w =>
      `<div class="compat-warn" style="${colors[w.severity]}">
        <span class="warn-icon">${icons[w.severity]}</span>
        <span>${w.msg}</span>
      </div>`
    ).join('');
    warnPanel.style.display = 'block';
  } else {
    const selectedCount = Object.values(selected).filter(a => a.length > 0).length;
    if (selectedCount >= 2) {
      warnPanel.innerHTML = '<div class="compat-ok">✅ All selected components are compatible</div>';
      warnPanel.style.display = 'block';
    } else {
      warnPanel.style.display = 'none';
    }
  }
}

function updateBuildCategory() {
  const count = Object.values(selected).filter(a => a.length > 0).length;
  const total = getSubtotal();
  let cat = '–', pct = 0;
  if (count === 0) { cat = '–'; pct = 0; }
  else if (total < 150000) { cat = 'ENTRY LEVEL'; pct = 20; }
  else if (total < 300000) { cat = 'MID-RANGE'; pct = 52; }
  else { cat = 'HIGH END'; pct = 88; }
  document.getElementById('buildCategoryVal').textContent = cat;
  document.getElementById('tierFill').style.width = pct + '%';
}

function getSubtotal() {
  let t = 0;
  Object.entries(selected).forEach(([cat, entries]) => {
    entries.forEach(e => { t += e.qty * e.item.price; });
  });
  selectedPeriphs.forEach(id => {
    const p = PERIPHERALS.find(x => x.id === id);
    if (p) t += p.price;
  });
  return t;
}

function updateTotal() {
  const t = getSubtotal();
  const str = t > 0 ? '₱ ' + t.toLocaleString('en-PH', {minimumFractionDigits:2}) : '₱ 0.00';
  document.getElementById('footerTotal').textContent = str;
}

// ─── COMPONENT CARDS ─────────────────────────────────────────────────────────

function updateCompCard(cat) {
  const entries = selected[cat];
  const card   = document.getElementById('card-' + cat);
  const nameEl = document.getElementById('name-' + cat);
  const tagEl  = document.getElementById('tag-' + cat);
  const iconEl = document.getElementById('icon-' + cat);

  if (!iconEl.dataset.defaultIcon) iconEl.dataset.defaultIcon = iconEl.innerHTML;

  card.classList.remove('done', 'warn', 'active-card');

  if (entries.length > 0) {
    const first = entries[0].item;
    const totalQty = selTotalQty(cat);
    nameEl.textContent = entries.length === 1
      ? (totalQty > 1 ? `${first.name} ×${totalQty}` : first.name)
      : `${entries.length} items selected (qty ${totalQty})`;
    tagEl.textContent = totalQty > 1 ? `✓ ${totalQty} added` : '✓ Added';
    tagEl.className = 'comp-tag tag-g';
    card.classList.add('done');
    if (first.image) {
      iconEl.innerHTML = `<img src="/SolarPower-Energy-Corporation/${first.image}" alt="${first.name}" onerror="this.parentElement.innerHTML=this.parentElement.dataset.defaultIcon">`;
      iconEl.style.background = 'var(--green)';
    } else {
      iconEl.style.background = 'var(--green)';
    }
  } else {
    nameEl.textContent = 'Select item';
    tagEl.textContent = '+ Select';
    tagEl.className = 'comp-tag tag-a';
    iconEl.innerHTML = iconEl.dataset.defaultIcon;
    iconEl.style.background = '';
  }
}


function renderProducts(items) {
  const cat = activeCategory;
  const list = document.getElementById('prodList');
  if (!items.length) {
    list.innerHTML = '<div class="no-results" style="display:block">No products match your filters.</div>';
    return;
  }
  list.innerHTML = items.map(item => {
    const entry    = selected[cat].find(e => e.item.id === item.id);
    const isActive = !!entry;
    const qty      = entry ? entry.qty : 0;
    const saleTag  = item.oldPrice ? `<span class="prod-sale">SALE</span> <span class="prod-old-price">₱${item.oldPrice.toLocaleString()}</span>` : '';
    const imgHtml  = item.image
      ? `<img src="/SolarPower-Energy-Corporation/${item.image}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;" onerror="applyFallbackIcon(this.parentElement, '${cat}')">`
      : getIcon(cat);
    const qtyControls = isActive ? `
      <div class="qty-row">
        <div class="qty-stepper">
          <button class="qty-btn" onclick="event.stopPropagation();changeQty('${cat}','${item.id}',-1)">−</button>
          <span class="qty-display">${qty}</span>
          <button class="qty-btn" onclick="event.stopPropagation();changeQty('${cat}','${item.id}',1)">+</button>
        </div>
        <button class="remove-item-btn" onclick="event.stopPropagation();removeItem('${cat}','${item.id}')">Remove</button>
      </div>` : `
      <button class="select-btn" onclick="event.stopPropagation(); selectProduct('${cat}','${item.id}')">SELECT</button>`;
    return `
      <div class="prod-card ${isActive ? 'active' : ''}" id="prod-${item.id}" onclick="selectProduct('${cat}', '${item.id}')">
        <div class="prod-img">${imgHtml}</div>
        <div class="prod-inf">
          <div class="prod-name">${item.name}</div>
          <div class="prod-spec">${item.spec}</div>
          <div class="prod-price"><span>₱</span> ${item.price.toLocaleString()} ${saleTag}</div>
          ${qtyControls}
        </div>
      </div>`;
  }).join('');
}

function getIcon(cat) {
  const icons = {
    panels: '<img src="includes/solar_svg.png" alt="Solar Panels" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">',
    inverter: '<img src="includes/inverter.png" alt="Inverter" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">',
    battery: '<img src="includes/battery.png" alt="Battery" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">',
    mounting: '<img src="includes/mounting.png" alt="Mounting" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">',
    wiring: '<img src="includes/wiring-protection.png" alt="Wiring" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">',
    monitoring: '📡'
  };
  return icons[cat] || '⚙️';
}

function applyFallbackIcon(el, cat) {
  el.innerHTML = getIcon(cat);
}

function applySummaryFallback(el, cat) {
  const icons = {
    panels: '<img src="includes/solar_svg.png" alt="Solar Panels" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">',
    inverter: '<img src="includes/inverter.png" alt="Inverter" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">',
    battery: '<img src="includes/battery.png" alt="Battery" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">',
    mounting: '<img src="includes/mounting.png" alt="Mounting" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">',
    wiring: '<img src="includes/wiring-protection.png" alt="Wiring" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">',
    monitoring: '📡'
  };
  el.innerHTML = icons[cat] || '⚙️';
}

function filterProducts() {
  if (!activeCategory) return;
  const catData = PRODUCTS[activeCategory];
  const brand = document.getElementById('filterBrand').value;
  const type  = document.getElementById('filterType').value;
  const search = document.getElementById('searchInput').value.toLowerCase();

  const filtered = catData.items.filter(item => {
    const matchBrand = !brand || item[catData.filterKeys[0]] === brand;
    const matchType  = !type  || item[catData.filterKeys[1]] === type;
    const matchSearch = !search || item.name.toLowerCase().includes(search) || item.spec.toLowerCase().includes(search);
    return matchBrand && matchType && matchSearch;
  });
  renderProducts(filtered);
}

function selectProduct(cat, itemId) {
  const item = PRODUCTS[cat].items.find(i => i.id === itemId);
  if (!item) return;

  const existingIdx = selected[cat].findIndex(e => e.item.id === itemId);
  if (existingIdx >= 0) {
    // Already selected — increment qty
    selected[cat][existingIdx].qty++;
    showToast(`➕ ${item.name.split(' ').slice(0,4).join(' ')} qty: ${selected[cat][existingIdx].qty}`);
  } else {
    // New item — add with qty 1
    selected[cat].push({ item, qty: 1 });
    showToast(`✅ ${item.name.split(' ').slice(0,4).join(' ')} added!`);
  }

  updateCompCard(cat);
  updateStats();
  renderProducts(PRODUCTS[cat].items);

  if (['panels','inverter','battery','mounting','wiring'].includes(cat)) updateRecommendationBars();
}

function changeQty(cat, itemId, delta) {
  const idx = selected[cat].findIndex(e => e.item.id === itemId);
  if (idx < 0) return;
  selected[cat][idx].qty = Math.max(1, selected[cat][idx].qty + delta);
  updateCompCard(cat);
  updateStats();
  renderProducts(PRODUCTS[cat].items);
  if (['panels','inverter','battery','mounting','wiring'].includes(cat)) updateRecommendationBars();
}

function removeItem(cat, itemId) {
  selected[cat] = selected[cat].filter(e => e.item.id !== itemId);
  resetMismatchPopupState();
  updateCompCard(cat);
  updateStats();
  renderProducts(PRODUCTS[cat].items);
  if (['panels','inverter','battery','mounting','wiring'].includes(cat)) updateRecommendationBars();
  showToast(`🗑️ Item removed.`);
}

// ─── CLEAR ALL ────────────────────────────────────────────────────────────────

function clearAll() {
  Object.keys(selected).forEach(cat => {
    selected[cat] = null;
    updateCompCard(cat, null);
  });
  selectedPeriphs.clear();
  resetMismatchPopupState();
  updateStats();
  if (activeCategory) renderProducts(PRODUCTS[activeCategory].items);
  showToast('🗑️ Build cleared.');
}

// ─── PERIPHERALS ─────────────────────────────────────────────────────────────

function renderPeripherals() {
  const grid = document.getElementById('periphGrid');
  grid.innerHTML = PERIPHERALS.map(p => {
    const active = selectedPeriphs.has(p.id);
    return `
      <div class="periph-card ${active ? 'done' : ''}" id="pcard-${p.id}" onclick="togglePeriph('${p.id}')">
        <div class="periph-icon">${p.icon}</div>
        <div class="periph-type">${p.type}</div>
        <div class="periph-name">${p.name}</div>
        <div style="font-size:0.67rem; color:var(--muted); margin-top:4px;">${p.desc}</div>
        <div class="periph-price">₱ ${p.price.toLocaleString()}</div>
        <div class="periph-check">✓ Added</div>
        <div class="periph-toggle">${active ? '✕ Remove' : '+ Add to Build'}</div>
      </div>`;
  }).join('');
}

function togglePeriph(id) {
  if (selectedPeriphs.has(id)) {
    selectedPeriphs.delete(id);
  } else {
    selectedPeriphs.add(id);
    const p = PERIPHERALS.find(x => x.id === id);
    showToast(`✅ ${p.name} added!`);
  }
  renderPeripherals();
  updateTotal();
}

// ─── SUMMARY ─────────────────────────────────────────────────────────────────

function renderSummary() {
  const compCategories = [
    { key: 'panels', label: 'Solar Panels', icon: '<img src="includes/solar_svg.png" alt="Solar Panels" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">' },
    { key: 'inverter', label: 'Inverter', icon: '<img src="includes/inverter.png" alt="Inverter" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">' },
    { key: 'battery', label: 'Battery Storage', icon: '<img src="includes/battery.png" alt="Battery" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">' },
    { key: 'mounting', label: 'Mounting System', icon: '<img src="includes/mounting.png" alt="Mounting" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">' },
    { key: 'wiring', label: 'Wiring & Protection', icon: '<img src="includes/wiring-protection.png" alt="Wiring" style="width:28px;height:28px;object-fit:cover;border-radius:4px;">' },
    
  ];

  const compHtml = compCategories.map(({ key, label, icon }) => {
    const entries = selected[key];
    if (entries.length > 0) {
      return entries.map(({ item, qty }) => {
        const lineTotal = qty * item.price;
        const imgHtml = item.image
          ? `<img src="/SolarPower-Energy-Corporation/${item.image}" alt="${item.name}" onerror="applySummaryFallback(this.parentElement, '${key}')">`
          : icon;
        return `<div class="summary-item">
          <div class="summary-item-left">
            <div class="summary-item-icon">${imgHtml}</div>
            <div>
              <div class="summary-item-name">${item.name}${qty > 1 ? ` <span style="color:var(--yellow-d);font-weight:900">×${qty}</span>` : ''}</div>
              <div class="summary-item-spec">${item.spec}</div>
            </div>
          </div>
          <div class="summary-item-price">₱ ${lineTotal.toLocaleString()}</div>
        </div>`;
      }).join('');
    } else {
      return `<div class="summary-item empty">
        <div class="summary-item-left">
          <div class="summary-item-icon">${icon}</div>
          <div class="summary-item-name" style="color:var(--muted)">${label} — not selected</div>
        </div>
        <div class="summary-item-price" style="color:var(--muted)">—</div>
      </div>`;
    }
  }).join('');

  document.getElementById('summaryComponents').innerHTML = compHtml;

  if (selectedPeriphs.size > 0) {
    const periphHtml = [...selectedPeriphs].map(id => {
      const p = PERIPHERALS.find(x => x.id === id);
      return `<div class="summary-item">
        <div class="summary-item-left">
          <div class="summary-item-icon">${p.icon}</div>
          <div>
            <div class="summary-item-name">${p.name}</div>
            <div class="summary-item-spec">${p.desc}</div>
          </div>
        </div>
        <div class="summary-item-price">₱ ${p.price.toLocaleString()}</div>
      </div>`;
    }).join('');
    document.getElementById('summaryPeripherals').innerHTML = periphHtml;
    document.getElementById('summaryPeriphSection').style.display = 'block';
  } else {
    document.getElementById('summaryPeriphSection').style.display = 'none';
  }

  const total = getSubtotal();
  document.getElementById('summaryTotal').textContent = '₱ ' + total.toLocaleString('en-PH', {minimumFractionDigits:2});

  // ── Summary: Savings estimate (from calculator) ──
  const savingsSection = document.getElementById('summarySavingsSection');
  if (calcData.bill > 0 && savingsSection) {
    document.getElementById('summaryBillAmt').textContent = calcData.bill.toLocaleString('en-PH', {maximumFractionDigits:0});
    document.getElementById('summaryMonthlySave').textContent = '₱' + calcData.monthlySavings.toLocaleString('en-PH', {maximumFractionDigits:0});
    document.getElementById('summaryYearlySave').textContent = '₱' + calcData.yearlySavings.toLocaleString('en-PH', {maximumFractionDigits:0});
    if (total > 0 && calcData.monthlySavings > 0) {
      const roiMonths = Math.ceil(total / calcData.monthlySavings);
      const roiYears = (roiMonths / 12).toFixed(1);
      document.getElementById('summaryROI').textContent = roiYears + ' yrs';
    } else {
      document.getElementById('summaryROI').textContent = '–';
    }
    savingsSection.style.display = '';
  } else if (savingsSection) {
    savingsSection.style.display = 'none';
  }

  // ── Summary: Compatibility Check ──
  renderSummaryCompat();

  // ── Summary: System Performance Stats ──
  renderSummaryStats();
}

function renderSummaryCompat() {
  const compatSection = document.getElementById('summaryCompatSection');
  const compatDiv = document.getElementById('summaryCompat');
  if (!compatSection || !compatDiv) return;

  // Re-run compatibility check to get warnings
  const warnings = getCompatibilityWarnings();
  const selectedCount = Object.values(selected).filter(a => a.length > 0).length;

  if (selectedCount >= 2) {
    compatSection.style.display = 'block';
    if (warnings.length > 0) {
      const icons = { error: '🚫', warn: '⚠️', info: 'ℹ️' };
      const colors = {
        error: 'background:#FFF0F0;border-color:#E53935;color:#B71C1C;',
        warn: 'background:#FFF8E1;border-color:#E8A020;color:#7A5200;',
        info: 'background:#E3F2FD;border-color:#1976D2;color:#0D47A1;'
      };
      compatDiv.innerHTML = warnings.map(w =>
        `<div class="compat-warn" style="${colors[w.severity]}">
          <span class="warn-icon">${icons[w.severity]}</span>
          <span>${w.msg}</span>
        </div>`
      ).join('');
    } else {
      compatDiv.innerHTML = '<div class="compat-ok">✅ All selected components are compatible</div>';
    }
  } else {
    compatSection.style.display = 'none';
  }
}

function renderSummaryStats() {
  const statsSection = document.getElementById('summaryStatsSection');
  const statsDiv = document.getElementById('summaryStats');
  if (!statsSection || !statsDiv) return;

  const invItem   = selFirst('inverter');
  const panelItem = selFirst('panels');
  let systemKW = 0;
  if (invItem && invItem.parsedSpecs?.kw) {
    systemKW = selected.inverter.reduce((s, e) => s + e.qty * (e.item.parsedSpecs.kw || 0), 0);
  } else if (panelItem && panelItem.parsedSpecs?.watts) {
    systemKW = selected.panels.reduce((s, e) => s + e.qty * (e.item.parsedSpecs.watts || 0), 0) / 1000;
  }

  if (systemKW > 0) {
    statsSection.style.display = 'block';
    const dailyKWH = systemKW * 4.5 * 0.80;
    const dailySavings = dailyKWH * 11.50;
    const annualSavings = dailySavings * 365;
    const totalCost = getSubtotal();
    const roiYears = annualSavings > 0 ? totalCost / annualSavings : 0;
    const co2 = systemKW * 1.12;

    statsDiv.innerHTML = `
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div class="summary-item" style="flex-direction:column;align-items:flex-start;padding:10px 14px;">
          <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.08em;">System Capacity</div>
          <div style="font-size:1rem;font-weight:800;color:var(--green);">${systemKW.toFixed(1)} kWp</div>
        </div>
        <div class="summary-item" style="flex-direction:column;align-items:flex-start;padding:10px 14px;">
          <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.08em;">Daily Output</div>
          <div style="font-size:1rem;font-weight:800;color:var(--green);">${dailyKWH.toFixed(1)} kWh</div>
        </div>
        <div class="summary-item" style="flex-direction:column;align-items:flex-start;padding:10px 14px;">
          <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.08em;">Est. Daily Savings</div>
          <div style="font-size:1rem;font-weight:800;color:var(--green);">₱${Math.round(dailySavings).toLocaleString()}</div>
        </div>
        <div class="summary-item" style="flex-direction:column;align-items:flex-start;padding:10px 14px;">
          <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.08em;">ROI Period</div>
          <div style="font-size:1rem;font-weight:800;color:var(--green);">${roiYears > 0 ? roiYears.toFixed(1) + ' yrs' : '–'}</div>
        </div>
        <div class="summary-item" style="flex-direction:column;align-items:flex-start;padding:10px 14px;">
          <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.08em;">CO₂ Reduced</div>
          <div style="font-size:1rem;font-weight:800;color:var(--green);">${co2.toFixed(1)} t/yr</div>
        </div>
        <div class="summary-item" style="flex-direction:column;align-items:flex-start;padding:10px 14px;">
          <div style="font-size:0.62rem;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:0.08em;">Annual Savings</div>
          <div style="font-size:1rem;font-weight:800;color:var(--green);">₱${Math.round(annualSavings).toLocaleString()}</div>
        </div>
      </div>
      <div style="font-size:0.6rem;color:var(--muted);margin-top:8px;font-style:italic;">
        Based on PH avg 4.5 peak sun hours (NREL), ₱11.50/kWh Meralco rate, 80% system efficiency, 0.68 tCO₂/MWh grid factor.
      </div>
    `;
  } else {
    statsSection.style.display = 'none';
  }
}

// Helper: returns warnings array (shared between checkCompatibility and renderSummaryCompat)
function getCompatibilityWarnings() {
  const warnings = [];
  const p    = selFirst('panels');
  const inv  = selFirst('inverter');
  const batt = selFirst('battery');
  const wir = selFirst('wiring');
  const mnt = selFirst('mounting');

  const panelW = p?.parsedSpecs?.watts || 0;
  const invKW = inv?.parsedSpecs?.kw || 0;
  const battKWH = batt?.parsedSpecs?.kwh || 0;
  const battVClass = batt?.parsedSpecs?.voltage_class || '';
  const wireMaxKW = wir?.parsedSpecs?.max_kw || 0;
  const mountMaxPanels = mnt?.parsedSpecs?.max_panels || 0;
  const invType = inv?.parsedSpecs?.type || '';

  if (inv && wir && wireMaxKW > 0 && invKW > wireMaxKW) {
    warnings.push({ cats: ['wiring','inverter'], severity: 'error', msg: `Wiring kit rated for ${wireMaxKW}kW but inverter is ${invKW}kW — risk of overload. Upgrade wiring to ${invKW}kW+ kit.` });
  }
  if (p && inv && mnt && mountMaxPanels > 0 && panelW > 0 && invKW > 0) {
    const panelsNeeded = Math.ceil((invKW * 1000 * 1.1) / panelW);
    if (panelsNeeded > mountMaxPanels) {
      warnings.push({ cats: ['mounting'], severity: 'error', msg: `A ${invKW}kW system needs ~${panelsNeeded}× ${panelW}W panels, but mount only supports ${mountMaxPanels}.` });
    }
  }
  if (inv && batt && battVClass) {
    const invVoltage = inv.parsedSpecs?.battery_voltage || 48;
    if (battVClass === 'HV' && invVoltage <= 60) {
      warnings.push({ cats: ['battery','inverter'], severity: 'error', msg: `Battery is high-voltage (${batt.parsedSpecs.voltage}V) but inverter is low-voltage (${invVoltage}V). Not compatible.` });
    }
  }
  if (inv && batt && battKWH > 0 && invKW > 0) {
    const dailyOutput = invKW * 4.5 * 0.80;
    if (battKWH < dailyOutput * 0.25) {
      warnings.push({ cats: ['battery'], severity: 'warn', msg: `Battery (${battKWH}kWh) is small for a ${invKW}kW system (~${dailyOutput.toFixed(0)}kWh/day). Consider ${Math.ceil(dailyOutput * 0.25)}kWh+ for backup.` });
    }
  }
  if (inv && batt && invType === 'Grid-Tie') {
    warnings.push({ cats: ['battery','inverter'], severity: 'info', msg: `Grid-Tie inverters don't use batteries. Switch to Hybrid if you want battery storage.` });
  }
  if (inv && wir && wireMaxKW > 0 && invKW > 0 && invKW <= wireMaxKW && invKW > wireMaxKW * 0.8) {
    warnings.push({ cats: ['wiring'], severity: 'info', msg: `Wiring kit (${wireMaxKW}kW) is tight for ${invKW}kW inverter. Consider higher-rated kit for future expansion.` });
  }
  if (p && mnt && panelW >= 700) {
    if (mnt.parsedSpecs?.mount_type === 'Flat Roof') {
      warnings.push({ cats: ['panels','mounting'], severity: 'warn', msg: `${panelW}W panels are large/heavy. Verify flat roof mount can handle the extra weight.` });
    }
  }
  return warnings;
}

// ─── RECOMMENDATION PROGRESS BARS ───────────────────────────────────────────

function updateRecommendationBars() {
  if (!calcData || !calcData.bill || calcData.bill <= 0) return;
  if (!document.getElementById('billInfoBox') || document.getElementById('billInfoBox').style.display === 'none') return;

  // Panel bar: total selected wattage vs required total watts from calculator
  const reqW = calcData.requiredTotalWatts || 0;
  const selPanelW = selected.panels.reduce((s, e) => s + e.qty * (e.item.parsedSpecs?.watts || 0), 0);
  setRecBar('barPanels', selPanelW, reqW,
    selPanelW > 0 ? (selPanelW >= 1000 ? (selPanelW/1000).toFixed(1)+' kW' : selPanelW+' W') : '–',
    reqW >= 1000 ? (reqW/1000).toFixed(1)+' kW required' : Math.round(reqW)+' W required');

  // Inverter bar: total selected inverter kW vs recommended inverter kW
  const recInvKw = calcData.recInverterKw || 0;
  const selInvKw = selected.inverter.reduce((s, e) => s + e.qty * (e.item.parsedSpecs?.kw || 0), 0);
  setRecBar('barInverter', selInvKw, recInvKw,
    selInvKw > 0 ? selInvKw + ' kW' : '–', recInvKw + ' kW required');

  // Battery bar: total selected kWh vs recommended kWh
  const recBatKwh = calcData.recBatteryKwh || 0;
  const selBatKwh = selected.battery.reduce((s, e) => s + e.qty * (e.item.parsedSpecs?.kwh || 0), 0);
  setRecBar('barBattery', selBatKwh, recBatKwh,
    selBatKwh > 0 ? selBatKwh + ' kWh' : '–', recBatKwh + ' kWh required');

  // Mounting bar: mount max_panels vs required panel count from calculator
  const reqPanels = calcData.panels || 0;
  const mntItem = selected.mounting.length > 0 ? selected.mounting[0].item : null;
  const mntMaxPanels = mntItem ? (mntItem.parsedSpecs?.max_panels || 0) : 0;
  setRecBarCapable('barMounting', mntMaxPanels, reqPanels,
    mntMaxPanels > 0 ? mntMaxPanels + ' panels supported' : '–',
    reqPanels + ' panels required');

  // Wiring bar: wire max_kw vs recommended inverter kW
  const recWireKw = calcData.recInverterKw || 0;
  const wirItem = selected.wiring.length > 0 ? selected.wiring[0].item : null;
  const wirMaxKw = wirItem ? (wirItem.parsedSpecs?.max_kw || 0) : 0;
  setRecBarCapable('barWiring', wirMaxKw, recWireKw,
    wirMaxKw > 0 ? wirMaxKw + ' kW rated' : '–',
    recWireKw + ' kW required');
}

function setRecBar(id, current, target, currentLabel, targetLabel) {
  const fill   = document.getElementById(id + 'Fill');
  const val    = document.getElementById(id + 'Val');
  const status = document.getElementById(id + 'Status');
  if (!fill || !val || !status) return;

  if (!current || current <= 0 || !target || target <= 0) {
    fill.style.width = '0%';
    fill.className   = 'rec-bar-fill bar-none';
    val.textContent  = '– / ' + targetLabel;
    status.textContent = 'Not selected';
    status.className   = 'rec-bar-status s-none';
    return;
  }

  const pct = Math.min(100, (current / target) * 100);
  fill.style.width = pct + '%';
  val.textContent  = currentLabel + ' / ' + targetLabel;

  if (pct >= 100) {
    fill.className     = 'rec-bar-fill bar-ok';
    status.textContent = '✓ Meets requirement (' + Math.round(pct) + '%)';
    status.className   = 'rec-bar-status s-ok';
  } else if (pct >= 70) {
    fill.className     = 'rec-bar-fill bar-mid';
    status.textContent = '⚠ ' + Math.round(pct) + '% of requirement — consider higher capacity';
    status.className   = 'rec-bar-status s-mid';
  } else {
    fill.className     = 'rec-bar-fill bar-low';
    status.textContent = '✗ Only ' + Math.round(pct) + '% of requirement — insufficient';
    status.className   = 'rec-bar-status s-low';
  }
}

// Capable/not-capable bar variant (used for mounting & wiring)
// current >= target means capable; partial coverage shown if below
function setRecBarCapable(id, current, target, currentLabel, targetLabel) {
  const fill   = document.getElementById(id + 'Fill');
  const val    = document.getElementById(id + 'Val');
  const status = document.getElementById(id + 'Status');
  if (!fill || !val || !status) return;

  if (!current || current <= 0 || !target || target <= 0) {
    fill.style.width = '0%';
    fill.className   = 'rec-bar-fill bar-none';
    val.textContent  = '– / ' + targetLabel;
    status.textContent = 'Not selected';
    status.className   = 'rec-bar-status s-none';
    return;
  }

  const pct = Math.min(100, (current / target) * 100);
  fill.style.width = pct + '%';
  val.textContent  = currentLabel + ' / ' + targetLabel;

  if (current >= target) {
    fill.className     = 'rec-bar-fill bar-ok';
    status.textContent = '✓ Capable — supports ' + current + (id === 'barMounting' ? ' panels' : ' kW') + ' (need ' + target + ')';
    status.className   = 'rec-bar-status s-ok';
  } else {
    fill.className     = 'rec-bar-fill bar-low';
    status.textContent = '✗ Not capable — ' + current + (id === 'barMounting' ? ' panels' : ' kW') + ' rated, need ' + target;
    status.className   = 'rec-bar-status s-low';
  }
}

// ─── STEPS ────────────────────────────────────────────────────────────────────

function solarGoToStep(n) {
  // Update tabs
  document.querySelectorAll('.step-tab').forEach(tab => {
    const tabStep = parseInt(tab.getAttribute('data-step'));
    tab.classList.remove('active', 'completed');
    if (tabStep === n) tab.classList.add('active');
    if (tabStep < n) tab.classList.add('completed');
  });

  // Show page
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page' + n).classList.add('active');

  if (n === 2) renderPeripherals();
  if (n === 3) renderSummary();

  // Populate bill info banner on components page
  if (n === 1 && typeof calcData !== 'undefined' && calcData.bill > 0) {
    const box = document.getElementById('billInfoBox');
    if (box) {
      document.getElementById('billInfoVal').textContent = '₱ ' + calcData.bill.toLocaleString('en-PH', {maximumFractionDigits:0});
      // Show total required kW/W instead of just panel count
      const totalW = calcData.requiredTotalWatts;
      document.getElementById('billInfoKw').textContent = totalW >= 1000
        ? (totalW / 1000).toFixed(1) + ' kW'
        : Math.round(totalW) + ' W';
      document.getElementById('billInfoRec').textContent = calcData.panels + ' × panels (based on ' + (totalW >= 1000 ? (totalW / 1000).toFixed(1) + ' kW' : Math.round(totalW) + ' W') + ' required)';
      document.getElementById('billInfoInverter').textContent = calcData.recInverterStr || '–';
      document.getElementById('billInfoBattery').textContent = calcData.recBatteryStr || '–';
      box.style.display = '';
      updateRecommendationBars();
    }
  }

  // Show/hide footer bar on calculator page
  const footer = document.querySelector('.footer-bar');
  if (footer) footer.style.display = (n === 0) ? 'none' : '';

  const solarSection = document.getElementById('solarBuilderSection');
  if (solarSection) {
    solarSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } else {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

// ─── TOAST ────────────────────────────────────────────────────────────────────

let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

// ─── MISMATCH WARNING POPUP ───────────────────────────────────────────────────

let lastShownMismatchKey = '';
function showMismatchPopup(errorWarnings) {
  // Create a unique key for current warnings to avoid showing same popup repeatedly
  const currentKey = errorWarnings.map(w => w.msg).sort().join('|');
  if (currentKey === lastShownMismatchKey) return;
  lastShownMismatchKey = currentKey;

  const list = document.getElementById('mismatchPopupList');
  const overlay = document.getElementById('mismatchPopupOverlay');
  if (!list || !overlay) return;

  list.innerHTML = errorWarnings.map(w => `<li>${w.msg}</li>`).join('');
  overlay.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeMismatchPopup(event) {
  if (event && event.target !== event.currentTarget) return;
  const overlay = document.getElementById('mismatchPopupOverlay');
  if (overlay) {
    overlay.classList.remove('show');
    document.body.style.overflow = '';
  }
}

// Reset mismatch key when selection changes significantly (e.g., clearing all)
function resetMismatchPopupState() {
  lastShownMismatchKey = '';
}

// ─── MOBILE DRAWER & INIT ─────────────────────────────────────────────────────

// Open selector panel (async - fetches products from API)
async function openSelector(cat) {
  activeCategory = cat;
  document.querySelectorAll('.comp-card').forEach(c => c.classList.remove('active-card'));
  const card = document.getElementById('card-' + cat);
  if (card) card.classList.add('active-card');

  // Fetch products from API if not loaded
  await fetchProducts(cat);
  const catData = PRODUCTS[cat];

  if (window.innerWidth <= 767) {
    openDrawerWithData(cat, catData);
  } else {
    document.getElementById('selTitle').innerHTML = catData.title + ' <span class="arr">›</span>';
    const brands = [...new Set(catData.items.map(i => i[catData.filterKeys[0]]))];
    const types  = [...new Set(catData.items.map(i => i[catData.filterKeys[1]]))];
    const brandSel = document.getElementById('filterBrand');
    const typeSel  = document.getElementById('filterType');
    brandSel.innerHTML = `<option value="">${catData.filterLabels[0]}: All</option>` + brands.map(b => `<option value="${b}">${b}</option>`).join('');
    typeSel.innerHTML  = `<option value="">${catData.filterLabels[1]}: All</option>` + types.map(t => `<option value="${t}">${t}</option>`).join('');
    document.getElementById('searchInput').value = '';
    renderProducts(catData.items);
  }
}

function isMobile() { return window.innerWidth <= 767; }

// Open drawer with pre-loaded data
function openDrawerWithData(cat, catData) {
  document.getElementById('drawerTitle').textContent = catData.title;
  const brands = [...new Set(catData.items.map(i => i[catData.filterKeys[0]]))];
  const types  = [...new Set(catData.items.map(i => i[catData.filterKeys[1]]))];
  document.getElementById('drawerFilterBrand').innerHTML = `<option value="">${catData.filterLabels[0]}: All</option>` + brands.map(b=>`<option value="${b}">${b}</option>`).join('');
  document.getElementById('drawerFilterType').innerHTML  = `<option value="">${catData.filterLabels[1]}: All</option>` + types.map(t=>`<option value="${t}">${t}</option>`).join('');
  document.getElementById('drawerSearchInput').value = '';
  renderDrawerProducts(catData.items);
  document.getElementById('drawerOverlay').classList.add('show');
  document.getElementById('drawer').classList.add('open');
  document.body.style.overflow = 'hidden';
}

// Legacy openDrawer (async wrapper)
async function openDrawer(cat) {
  await fetchProducts(cat);
  openDrawerWithData(cat, PRODUCTS[cat]);
}

function closeDrawer() {
  document.getElementById('drawerOverlay').classList.remove('show');
  document.getElementById('drawer').classList.remove('open');
  document.body.style.overflow = '';
}

function drawerFilter() {
  if (!activeCategory) return;
  const catData = PRODUCTS[activeCategory];
  const brand  = document.getElementById('drawerFilterBrand').value;
  const type   = document.getElementById('drawerFilterType').value;
  const search = document.getElementById('drawerSearchInput').value.toLowerCase();
  const filtered = catData.items.filter(item => {
    return (!brand  || item[catData.filterKeys[0]] === brand)
        && (!type   || item[catData.filterKeys[1]] === type)
        && (!search || item.name.toLowerCase().includes(search) || item.spec.toLowerCase().includes(search));
  });
  renderDrawerProducts(filtered);
}

function renderDrawerProducts(items) {
  const cat = activeCategory;
  const list = document.getElementById('drawerProdList');
  if (!items.length) { list.innerHTML = '<div style="text-align:center;color:var(--muted);font-size:0.78rem;padding:30px 0">No results.</div>'; return; }
  list.innerHTML = items.map(item => {
    const entry = selected[cat].find(e => e.item.id === item.id);
    const isActive = !!entry;
    const qty = entry ? entry.qty : 0;
    const saleTag = item.oldPrice ? `<span class="prod-sale">SALE</span> <span class="prod-old-price">₱${item.oldPrice.toLocaleString()}</span>` : '';
    const imgHtml = item.image
      ? `<img src="/SolarPower-Energy-Corporation/${item.image}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;" onerror="applyFallbackIcon(this.parentElement, '${cat}')">`
      : getIcon(cat);
    const qtyControls = isActive ? `
      <div class="qty-row">
        <div class="qty-stepper">
          <button class="qty-btn" onclick="event.stopPropagation();changeQtyDrawer('${cat}','${item.id}',-1)">−</button>
          <span class="qty-display">${qty}</span>
          <button class="qty-btn" onclick="event.stopPropagation();changeQtyDrawer('${cat}','${item.id}',1)">+</button>
        </div>
        <button class="remove-item-btn" onclick="event.stopPropagation();removeItemDrawer('${cat}','${item.id}')">Remove</button>
      </div>` : `
      <button class="select-btn" style="display:inline-block" onclick="event.stopPropagation();selectProductDrawer('${cat}','${item.id}')">SELECT</button>`;
    return `<div class="prod-card ${isActive?'active':''}" onclick="selectProductDrawer('${cat}','${item.id}')">
      <div class="prod-img">${imgHtml}</div>
      <div class="prod-inf">
        <div class="prod-name">${item.name}</div>
        <div class="prod-spec">${item.spec}</div>
        <div class="prod-price"><span>₱</span> ${item.price.toLocaleString()} ${saleTag}</div>
        ${qtyControls}
      </div>
    </div>`;
  }).join('');
}

function selectProductDrawer(cat, itemId) {
  const item = PRODUCTS[cat].items.find(i => i.id === itemId);
  if (!item) return;
  const existingIdx = selected[cat].findIndex(e => e.item.id === itemId);
  if (existingIdx >= 0) {
    selected[cat][existingIdx].qty++;
    showToast(`➕ ${item.name.split(' ').slice(0,4).join(' ')} qty: ${selected[cat][existingIdx].qty}`);
  } else {
    selected[cat].push({ item, qty: 1 });
    showToast(`✅ ${item.name.split(' ').slice(0,4).join(' ')} added!`);
    setTimeout(closeDrawer, 800);
  }
  updateCompCard(cat);
  updateStats();
  renderDrawerProducts(PRODUCTS[cat].items);
  if (['panels','inverter','battery','mounting','wiring'].includes(cat)) updateRecommendationBars();
}

function changeQtyDrawer(cat, itemId, delta) {
  const idx = selected[cat].findIndex(e => e.item.id === itemId);
  if (idx < 0) return;
  selected[cat][idx].qty = Math.max(1, selected[cat][idx].qty + delta);
  updateCompCard(cat);
  updateStats();
  renderDrawerProducts(PRODUCTS[cat].items);
  if (['panels','inverter','battery','mounting','wiring'].includes(cat)) updateRecommendationBars();
}

function removeItemDrawer(cat, itemId) {
  selected[cat] = selected[cat].filter(e => e.item.id !== itemId);
  resetMismatchPopupState();
  updateCompCard(cat);
  updateStats();
  renderDrawerProducts(PRODUCTS[cat].items);
  if (['panels','inverter','battery','mounting','wiring'].includes(cat)) updateRecommendationBars();
  showToast(`🗑️ Item removed.`);
}

updateStats();

// Hide footer initially (calculator page is first)
(function() {
  const footer = document.querySelector('.footer-bar');
  if (footer) footer.style.display = 'none';
})();

// ─── CALCULATOR ──────────────────────────────────────────────────────────────

// Stored calculator results (shared with builder)
let calcData = { bill: 0, kwp: 0, panels: 0, monthlySavings: 0, yearlySavings: 0 };

let calcPreviewChart = null;

function runCalculator() {
  const billInput = document.getElementById('calcBillInput');
  const bill = parseFloat(billInput.value);
  const errorEl = document.getElementById('calcError');
  const resultsEl = document.getElementById('calcResults');
  const proceedEl = document.getElementById('calcProceed');
  const recomEl = document.getElementById('calcRecommendation');
  const chartSection = document.getElementById('calcChartSection');

  if (!bill || bill <= 0) {
    errorEl.textContent = 'Please enter a valid electric bill amount.';
    resultsEl.classList.remove('show');
    proceedEl.classList.remove('show');
    recomEl.classList.remove('show');
    if (chartSection) chartSection.style.display = 'none';
    return;
  }
  errorEl.textContent = '';

  // Philippine solar constants
  const avgRate = 13.40;       // ₱/kWh average Meralco rate
  const baseSunHours = 4.5;    // Peak sun hours (PH average)
  const efficiency = 0.85;     // System efficiency factor
  const panelWattage = 705;    // Watts per panel (modern panels)

  const monthlyKwh = bill / avgRate;
  const dailyKwh = monthlyKwh / 30;
  const requiredKwp = dailyKwh / (baseSunHours * efficiency);
  const panels = Math.ceil((requiredKwp * 1000) / panelWattage);

  // --- Monthly production using seasonal multipliers ---
  const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const multipliers = currentSolarData && currentSolarData.monthly
    ? Object.values(currentSolarData.monthly).map(m => m.multiplier)
    : Object.values(DEFAULT_MONTHLY_MULTIPLIERS);
  const peakSunArr = currentSolarData && currentSolarData.monthly
    ? Object.values(currentSolarData.monthly).map(m => m.peak_sun_hours)
    : Object.values(DEFAULT_PEAK_SUN_HOURS_MONTHLY);
  const daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];

  let monthlyProductions = [];
  let monthlySavingsArr = [];
  for (let i = 0; i < 12; i++) {
    const prod = requiredKwp * peakSunArr[i] * efficiency * daysInMonth[i];
    const saving = Math.min(prod * avgRate, bill); // per-month production-based (for chart)
    monthlyProductions.push(prod);
    monthlySavingsArr.push(saving);
  }

  // Savings formula: 95% of monthly bill (matches savings-calculator.php)
  const savingsRate = 0.95;
  const avgMonthlySavings = Math.round(bill * savingsRate);
  const totalYearlySavings = avgMonthlySavings * 12;

  // Total required wattage
  const requiredTotalWatts = requiredKwp * 1000;

  // Store for builder use
  calcData = {
    bill, kwp: requiredKwp, panels,
    monthlySavings: avgMonthlySavings,
    yearlySavings: totalYearlySavings,
    requiredTotalWatts,
    monthlyProductions,
    monthlySavingsArr
  };

  // Determine recommended inverter size (round up to common sizes)
  const inverterKw = requiredKwp;
  let recInverterStr;
  let recInverterKwNum;
  if (inverterKw <= 3)       { recInverterStr = '3 kW';                      recInverterKwNum = 3; }
  else if (inverterKw <= 5)  { recInverterStr = '5 kW';                      recInverterKwNum = 5; }
  else if (inverterKw <= 8)  { recInverterStr = '8 kW';                      recInverterKwNum = 8; }
  else if (inverterKw <= 10) { recInverterStr = '10 kW';                     recInverterKwNum = 10; }
  else if (inverterKw <= 15) { recInverterStr = '15 kW';                     recInverterKwNum = 15; }
  else                       { recInverterKwNum = Math.ceil(inverterKw); recInverterStr = recInverterKwNum + ' kW'; }

  // Recommended battery (rule of thumb: 1 kWh per kWp for basic backup)
  const recBatteryKwh = Math.ceil(requiredKwp * 2.5);
  const recBatteryStr = recBatteryKwh + ' kWh';

  // Update UI
  document.getElementById('calcKwp').textContent = requiredKwp.toFixed(1);
  document.getElementById('calcPanels').textContent = panels;
  document.getElementById('calcMonthlySavings').textContent = '₱' + Math.round(avgMonthlySavings).toLocaleString('en-PH');
  document.getElementById('calcYearlySavings').textContent = '₱' + Math.round(totalYearlySavings).toLocaleString('en-PH');

  // Recommendation
  document.getElementById('recPanels').textContent = panels + ' × ' + panelWattage + 'W';
  document.getElementById('recInverter').textContent = recInverterStr;
  document.getElementById('recBattery').textContent = recBatteryStr;

  // Store recommendation strings & numeric values for Page 1 display
  calcData.recInverterStr  = recInverterStr;
  calcData.recBatteryStr   = recBatteryStr;
  calcData.recInverterKw   = recInverterKwNum;
  calcData.recBatteryKwh   = recBatteryKwh;

  resultsEl.classList.add('show');
  recomEl.classList.add('show');
  proceedEl.classList.add('show');

  // --- Render preview chart ---
  renderCalcPreviewChart(bill, monthlySavingsArr, monthNames);
}

function renderCalcPreviewChart(bill, savings, labels) {
  const chartSection = document.getElementById('calcChartSection');
  if (!chartSection) return;
  chartSection.style.display = 'block';

  const ctx = document.getElementById('calcPreviewChart');
  if (!ctx) return;

  if (calcPreviewChart) { calcPreviewChart.destroy(); calcPreviewChart = null; }

  const withoutSolar = labels.map(() => bill);

  calcPreviewChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Without Solar',
          data: withoutSolar,
          backgroundColor: '#C8C8C8',
          borderRadius: 3,
          barPercentage: 0.8,
          categoryPercentage: 0.7
        },
        {
          label: 'With Solar',
          data: savings.map((s, i) => Math.max(bill - s, 0)),
          backgroundColor: function(context) {
            const idx = context.dataIndex;
            const m = idx + 1;
            // 5-season color coding for full-year visibility
            if (m >= 3 && m <= 5) return '#2D5016';    // Peak Dry (Mar-May) — deep green
            if (m === 1 || m === 2) return '#3A5C1A';   // Cool Dry Amihan (Jan-Feb) — standard green
            if (m === 7 || m === 8) return '#D35400';   // Peak Wet + Typhoons (Jul-Aug) — dark orange
            if (m === 6 || m === 9) return '#E67E22';   // Wet shoulders (Jun, Sep) — orange
            if (m === 10) return '#D4A017';              // Transition (Oct) — amber
            return '#689F38';                            // Nov-Dec improving — olive green
          },
          borderRadius: 3,
          barPercentage: 0.8,
          categoryPercentage: 0.7
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1A2308',
          titleFont: { family: 'Montserrat', weight: '700', size: 11 },
          bodyFont: { family: 'Montserrat', size: 10 },
          callbacks: {
            label: function(ctx) {
              const val = ctx.raw;
              if (ctx.datasetIndex === 0) return 'Current bill: ₱' + val.toLocaleString('en-PH', {maximumFractionDigits:0});
              return 'With solar: ₱' + val.toLocaleString('en-PH', {maximumFractionDigits:0});
            },
            afterBody: function(items) {
              if (items.length >= 2) {
                const saved = items[0].raw - items[1].raw;
                return ['You save: ₱' + saved.toLocaleString('en-PH', {maximumFractionDigits:0})];
              }
              return [];
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { family: 'Montserrat', size: 9, weight: '700' }, color: '#6B7C52' }
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(107,124,82,0.12)' },
          ticks: {
            font: { family: 'Montserrat', size: 9 }, color: '#6B7C52',
            callback: v => '₱' + v.toLocaleString('en-PH', {maximumFractionDigits:0})
          }
        }
      }
    }
  });
}

function renderCalcSeasonalInfo(savings, monthNames) {
  const container = document.getElementById('calcSeasonalInfo');
  if (!container) return;

  const peakIdx = savings.indexOf(Math.max(...savings));
  const lowIdx = savings.indexOf(Math.min(...savings));

  // Seasonal averages
  const janFebAvg = (savings[0] + savings[1]) / 2;
  const wetAvg = (savings[6] + savings[7]) / 2; // Jul-Aug worst
  const berAvg = (savings[8] + savings[9] + savings[10] + savings[11]) / 4;
  const dryAvg = (savings[2] + savings[3] + savings[4]) / 3; // Mar-Apr-May peak dry

  container.innerHTML = `
    <div style="color:#1A2308;">
      <div style="font-weight:700; font-size:0.85rem; margin-bottom:2px;">Highest Bill: <span style="font-weight:700;">${monthNames[peakIdx]} (₱${Math.round(savings[peakIdx]).toLocaleString('en-PH')})</span></div>
      <div style="font-weight:700; font-size:0.85rem;">Peak Dry Average: <span style="font-weight:700;">₱${Math.round(dryAvg).toLocaleString('en-PH')}</span></div>
    </div>
    <div style="color:#D35400;">
      <div style="font-weight:700; font-size:0.85rem; margin-bottom:2px;">Wet Season Lows:</div>
      <div style="font-weight:700; font-size:0.85rem;">Jul-Aug ₱${Math.round(wetAvg).toLocaleString('en-PH')} <span style="font-weight:600;">(Lowest)</span></div>
    </div>
    <div style="color:#689F38;">
      <div style="font-weight:700; font-size:0.85rem; margin-bottom:2px;">Ber Months Average: <span style="font-weight:700;">₱${Math.round(berAvg).toLocaleString('en-PH')}</span></div>
      <div style="font-weight:600; font-size:0.8rem;">(Recovering, ~${Math.round((1 - berAvg / (dryAvg || 1)) * 100)}% less vs Peak Dry)</div>
    </div                 >
  `;

  // Update city label
  const cityLabel = document.getElementById('calcCityLabel');
  if (cityLabel && currentSolarData && currentSolarData.city) {
    cityLabel.textContent = currentSolarData.city.charAt(0).toUpperCase() + currentSolarData.city.slice(1);
  }
}

// Enter key support for calculator
document.getElementById('calcBillInput').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') runCalculator();
});

// ─── PRINT TO PDF ─────────────────────────────────────────────────────────────

function printBuildPDF() {
  const summaryEl = document.querySelector('.summary-wrap');
  if (!summaryEl) return;

  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <html>
    <head>
      <title>Solar Build Summary - SolarPower Energy Corporation</title>
      <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
      <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Montserrat', sans-serif; background: #fff; color: #1A2308; padding: 40px; }
        .summary-title { font-size: 1.3rem; font-weight: 900; color: #3A5C1A; margin-bottom: 6px; }
        .summary-sub { font-size: 0.78rem; color: #6B7C52; margin-bottom: 28px; }
        .summary-section { margin-bottom: 24px; }
        .summary-section-title {
          font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em;
          color: #6B7C52; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1.5px solid #DDE3D4;
        }
        .summary-item {
          display: flex; justify-content: space-between; align-items: center;
          padding: 11px 14px; background: #fff; border-radius: 8px; margin-bottom: 6px;
          border: 1.5px solid #DDE3D4;
        }
        .summary-item-left { display: flex; align-items: center; gap: 10px; }
        .summary-item-icon { font-size: 1.2rem; width: 44px; height: 44px; min-width: 44px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #F7F9F4; border: 1px solid #DDE3D4; }
        .summary-item-icon img { width: 100%; height: 100%; object-fit: cover; }
        .summary-item-name { font-size: 0.82rem; font-weight: 600; }
        .summary-item-spec { font-size: 0.67rem; color: #6B7C52; }
        .summary-item-price { font-size: 0.9rem; font-weight: 800; color: #3A5C1A; }
        .summary-item.empty { opacity: 0.4; border-style: dashed; }
        .summary-total-row {
          display: flex; justify-content: space-between; align-items: center;
          padding: 14px 18px; background: #3A5C1A; border-radius: 10px; margin-top: 14px;
        }
        .summary-total-lbl { color: rgba(255,255,255,0.75); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }
        .summary-total-val { color: #ffc107; font-size: 1.6rem; font-weight: 900; }
        .print-footer { margin-top: 30px; text-align: center; font-size: 0.7rem; color: #6B7C52; border-top: 1px solid #DDE3D4; padding-top: 15px; }
        @media print {
          body { padding: 20px; }
          .no-print { display: none; }
        }
      </style>
    </head>
    <body>
      ${summaryEl.innerHTML}
      <div class="print-footer">
        <p>Generated on ${new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' })} — SolarPower Energy Corporation</p>
      </div>
      <script>
        // Remove CTA buttons from print
        document.querySelectorAll('.summary-cta').forEach(el => el.remove());
        window.onload = function() { window.print(); };
      <\/script>
    </body>
    </html>
  `);
  printWindow.document.close();
}
</script>
</body>
</html>