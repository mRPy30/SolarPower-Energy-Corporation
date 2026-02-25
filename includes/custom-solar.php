<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solar System Builder – SolarPower Energy Corporation</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --yellow:   #ffc107;
      --yellow-d: #D99200;
      --yellow-l: #FFF8E1;
      --green:    #3A5C1A;
      --green-d:  #2C4713;
      --green-l:  #EEF3E8;
      --white:    #FFFFFF;
      --bg:       #F7F9F4;
      --border:   #DDE3D4;
      --text:     #1A2308;
      --muted:    #6B7C52;
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
      background: var(--white); border-bottom: 2px solid var(--border);
      display: flex; padding: 0 40px;
    }
    .step-tab {
      padding: 13px 26px; font-size: 0.77rem; font-weight: 700;
      color: var(--muted); cursor: pointer; border-bottom: 3px solid transparent;
      margin-bottom: -2px; transition: all .2s; letter-spacing: 0.06em; text-transform: uppercase;
      display: flex; align-items: center; gap: 8px;
    }
    .step-num {
      width: 22px; height: 22px; border-radius: 50%;
      background: var(--border); color: var(--muted);
      font-size: 0.67rem; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
    }
    .step-tab.active { color: var(--green); border-bottom-color: var(--yellow); }
    .step-tab.active .step-num { background: var(--yellow); color: var(--green-d); }
    .step-tab.completed .step-num { background: var(--green); color: #fff; }

    /* ── Pages ── */
    .page { display: none; }
    .page.active { display: block; }

    /* ── Layout ── */
    .builder {
      display: grid; grid-template-columns: 280px 1fr 330px;
      min-height: calc(100vh - 36px - 68px - 50px - 60px);
    }

    /* ── Radar panel ── */
    .radar-panel {
      background: var(--white); border-right: 1.5px solid var(--border);
      padding: 26px 20px; display: flex; flex-direction: column; align-items: center;
    }
    .panel-lbl {
      font-size: 0.67rem; font-weight: 800; text-transform: uppercase;
      letter-spacing: 0.13em; color: var(--muted); margin-bottom: 18px; align-self: flex-start;
    }
    .radar-wrap { width: 195px; height: 195px; margin-bottom: 20px; }
    .radar-wrap svg { width: 100%; height: 100%; }

    .radar-polygon { transition: all 0.6s cubic-bezier(.4,0,.2,1); }

    .build-box {
      width: 100%; background: var(--green); border-radius: 10px;
      padding: 15px 17px; margin-bottom: 14px; color: #fff;
    }
    .build-lbl { font-size: 0.64rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.65; margin-bottom: 3px; }
    .build-val { font-weight: 900; font-size: 1.35rem; color: var(--yellow); transition: all 0.4s; }
    .tier-bar { width: 100%; height: 6px; background: rgba(255,255,255,0.18); border-radius: 3px; margin-top: 9px; overflow: hidden; }
    .tier-fill { height: 100%; background: linear-gradient(90deg, var(--yellow), #82C820); border-radius: 3px; transition: width 0.6s cubic-bezier(.4,0,.2,1); }
    .tier-lbls { display: flex; justify-content: space-between; margin-top: 5px; font-size: 0.6rem; opacity: 0.55; }

    .stats { width: 100%; }
    .stat-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 8px 0; border-top: 1px solid var(--border); font-size: 0.77rem;
    }
    .stat-n { color: var(--muted); font-weight: 500; }
    .stat-v { color: var(--green); font-weight: 700; transition: all 0.4s; }

    /* ── Components ── */
    .comp-panel { background: var(--bg); display: flex; flex-direction: column; }
    .comp-head {
      background: var(--white); padding: 18px 28px;
      border-bottom: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .comp-head h2 { font-size: 1.1rem; font-weight: 800; color: var(--green); }
    .clear-btn {
      background: transparent; border: 1.5px solid #F0C8C8; color: #C0392B;
      font-family: 'Montserrat', sans-serif; font-size: 0.72rem; font-weight: 700;
      padding: 7px 13px; border-radius: 6px; cursor: pointer; transition: all .2s;
    }
    .clear-btn:hover { background: #FFF0EE; }

    .comp-list { padding: 16px 22px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; }

    .comp-card {
      background: var(--white); border: 1.5px solid var(--border); border-radius: 10px;
      padding: 13px 15px; display: flex; align-items: center; gap: 13px;
      cursor: pointer; transition: all .22s; box-shadow: var(--shadow);
    }
    .comp-card:hover { border-color: var(--yellow); transform: translateY(-1px); box-shadow: 0 5px 18px rgba(58,92,26,0.13); }
    .comp-card.active-card { border-color: var(--yellow); border-left: 4px solid var(--yellow); background: var(--yellow-l); }
    .comp-card.done { border-left: 4px solid var(--green); border-color: var(--green); }
    .comp-card.warn { border-left: 4px solid var(--yellow); background: #FFFBF0; border-color: #E8A020; }

    .comp-icon {
      width: 50px; height: 50px; border-radius: 8px;
      background: var(--green-l); border: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.45rem; flex-shrink: 0; transition: background 0.3s;
    }
    .comp-card.done .comp-icon { background: var(--green); }
    .comp-info { flex: 1; }
    .comp-type { font-size: 0.63rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em; color: var(--yellow-d); margin-bottom: 3px; }
    .comp-name { font-size: 0.83rem; font-weight: 600; color: var(--text); line-height: 1.35; }
    .comp-tag {
      font-size: 0.67rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;
      letter-spacing: 0.04em; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;
    }
    .tag-g { background: var(--green-l); color: var(--green); border: 1px solid #C5DCA0; }
    .tag-w { background: #FFF5E0; color: #9A6A00; border: 1px solid #EDD080; }
    .tag-a { background: #F2F4EE; color: var(--muted); border: 1px solid var(--border); }

    /* ── Selector ── */
    .sel-panel { background: var(--white); border-left: 1.5px solid var(--border); display: flex; flex-direction: column; }
    .sel-head { padding: 17px 20px 13px; border-bottom: 1.5px solid var(--border); }
    .sel-head h3 { font-size: 0.9rem; font-weight: 800; color: var(--green); display: flex; align-items: center; gap: 7px; }
    .sel-head h3 .arr { color: var(--yellow); }
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

    .prod-list { flex: 1; overflow-y: auto; padding: 4px 20px 14px; display: flex; flex-direction: column; gap: 8px; }
    .prod-card {
      border: 1.5px solid var(--border); border-radius: 8px; padding: 11px;
      display: flex; gap: 10px; cursor: pointer; transition: all .2s;
    }
    .prod-card:hover { border-color: var(--yellow); background: var(--yellow-l); }
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
    .summary-item-icon { font-size: 1.2rem; }
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
      background: var(--green); padding: 13px 40px;
      display: flex; align-items: center; justify-content: space-between; gap: 20px;
      position: sticky; bottom: 0; z-index: 100;
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

    /* Tablet: 768px–1024px — collapse radar into top bar, 2-col layout */
    @media (max-width: 1024px) {
      .top-bar-right { display: none; }
      .top-bar { padding: 8px 20px; }
      .site-header { padding: 0 20px; }
      .hbtn-ghost:not(:last-child) { display: none; }
      .step-nav { padding: 0 20px; }
      .step-tab { padding: 12px 16px; font-size: 0.7rem; }

      .builder { grid-template-columns: 1fr 300px; grid-template-rows: auto 1fr; }
      .radar-panel {
        grid-column: 1 / -1; grid-row: 1;
        flex-direction: row; align-items: flex-start; gap: 20px;
        padding: 16px 20px; border-right: none; border-bottom: 1.5px solid var(--border);
        flex-wrap: wrap;
      }
      .panel-lbl { display: none; }
      .radar-wrap { width: 130px; height: 130px; margin-bottom: 0; flex-shrink: 0; }
      .build-box { flex: 1; min-width: 160px; margin-bottom: 0; }
      .stats { flex: 2; min-width: 200px; display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px; }
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

      /* Radar collapses to a compact horizontal strip */
      .radar-panel {
        grid-column: 1; grid-row: 1;
        flex-direction: column; align-items: center;
        padding: 16px 14px; border-right: none; border-bottom: 1.5px solid var(--border);
      }
      .panel-lbl { display: none; }
      .radar-wrap { width: 140px; height: 140px; }
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
  </style>
</head>
<body>





<!-- Step Nav -->
<nav class="step-nav" id="stepNav">
  <div class="step-tab active" data-step="1" onclick="goToStep(1)">
    <div class="step-num">01</div> Components
  </div>
  <div class="step-tab" data-step="2" onclick="goToStep(2)">
    <div class="step-num">02</div> Peripherals
  </div>
  <div class="step-tab" data-step="3" onclick="goToStep(3)">
    <div class="step-num">03</div> Summary
  </div>
</nav>

<!-- ══════════════════ PAGE 1: COMPONENTS ══════════════════ -->
<div class="page active" id="page1">
<div class="builder">

  <!-- LEFT: Radar + Stats -->
  <div class="radar-panel">
    <div class="panel-lbl">System Performance Map</div>
    <div class="radar-wrap">
      <svg id="radarSvg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <!-- Grid -->
        <polygon points="100,18 182,68 182,142 100,182 18,142 18,68" fill="none" stroke="#DDE3D4" stroke-width="1.5"/>
        <polygon points="100,38 162,76 162,132 100,162 38,132 38,76" fill="none" stroke="#DDE3D4" stroke-width="1.5"/>
        <polygon points="100,58 142,84 142,122 100,142 58,122 58,84" fill="none" stroke="#DDE3D4" stroke-width="1.5"/>
        <polygon points="100,78 122,92 122,112 100,122 78,112 78,92" fill="none" stroke="#DDE3D4" stroke-width="1.5"/>
        <!-- Axes -->
        <line x1="100" y1="18" x2="100" y2="182" stroke="#DDE3D4" stroke-width="1"/>
        <line x1="18" y1="68" x2="182" y2="142" stroke="#DDE3D4" stroke-width="1"/>
        <line x1="18" y1="142" x2="182" y2="68" stroke="#DDE3D4" stroke-width="1"/>
        <!-- Dynamic polygon -->
        <polygon id="radarPolygon" class="radar-polygon"
          points="100,100 100,100 100,100 100,100 100,100 100,100"
          fill="rgba(245,168,0,0.15)" stroke="#F5A800" stroke-width="2.5" stroke-linejoin="round"/>
        <circle id="radarCenter" cx="100" cy="100" r="5" fill="#F5A800"/>
        <!-- Labels -->
        <text x="100" y="11" text-anchor="middle" fill="#6B7C52" font-size="8" font-family="Montserrat,sans-serif" font-weight="700">POWER OUTPUT</text>
        <text x="188" y="66" text-anchor="start" fill="#6B7C52" font-size="8" font-family="Montserrat,sans-serif" font-weight="700">PANELS</text>
        <text x="188" y="148" text-anchor="start" fill="#6B7C52" font-size="8" font-family="Montserrat,sans-serif" font-weight="700">BATTERY</text>
        <text x="100" y="196" text-anchor="middle" fill="#6B7C52" font-size="8" font-family="Montserrat,sans-serif" font-weight="700">INVERTER</text>
        <text x="12" y="148" text-anchor="end" fill="#6B7C52" font-size="8" font-family="Montserrat,sans-serif" font-weight="700">WIRING</text>
        <text x="12" y="66" text-anchor="end" fill="#6B7C52" font-size="8" font-family="Montserrat,sans-serif" font-weight="700">MOUNTING</text>
      </svg>
    </div>

    <div class="build-box">
      <div class="build-lbl">Build Category</div>
      <div class="build-val" id="buildCategoryVal">–</div>
      <div class="tier-bar"><div class="tier-fill" id="tierFill" style="width:0%"></div></div>
      <div class="tier-lbls"><span>Entry Level</span><span>Mid-Range</span><span>High End</span></div>
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
        <div class="comp-icon" id="icon-panels">🌞</div>
        <div class="comp-info">
          <div class="comp-type">Solar Panels</div>
          <div class="comp-name" id="name-panels">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-panels">+ Select</span>
      </div>

      <div class="comp-card" id="card-inverter" onclick="openSelector('inverter')">
        <div class="comp-icon" id="icon-inverter">⚡</div>
        <div class="comp-info">
          <div class="comp-type">Inverter</div>
          <div class="comp-name" id="name-inverter">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-inverter">+ Select</span>
      </div>

      <div class="comp-card" id="card-battery" onclick="openSelector('battery')">
        <div class="comp-icon" id="icon-battery">🔋</div>
        <div class="comp-info">
          <div class="comp-type">Battery Storage</div>
          <div class="comp-name" id="name-battery">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-battery">+ Select</span>
      </div>

      <div class="comp-card" id="card-mounting" onclick="openSelector('mounting')">
        <div class="comp-icon" id="icon-mounting">🔩</div>
        <div class="comp-info">
          <div class="comp-type">Mounting System</div>
          <div class="comp-name" id="name-mounting">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-mounting">+ Select</span>
      </div>

      <div class="comp-card" id="card-wiring" onclick="openSelector('wiring')">
        <div class="comp-icon" id="icon-wiring">🔌</div>
        <div class="comp-info">
          <div class="comp-type">Wiring & Protection</div>
          <div class="comp-name" id="name-wiring">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-wiring">+ Select</span>
      </div>

      <div class="comp-card" id="card-monitoring" onclick="openSelector('monitoring')">
        <div class="comp-icon" id="icon-monitoring">📡</div>
        <div class="comp-info">
          <div class="comp-type">Monitoring System</div>
          <div class="comp-name" id="name-monitoring">Select item</div>
        </div>
        <span class="comp-tag tag-a" id="tag-monitoring">+ Select</span>
      </div>

    </div>
  </div>

  <!-- RIGHT: Product Selector Panel -->
  <div class="sel-panel" id="selPanel">
    <div class="sel-head">
      <h3 id="selTitle">Select a component <span class="arr">›</span></h3>
      <div class="filter-row">
        <select class="f-sel" id="filterBrand" onchange="filterProducts()">
          <option value="">Brand: All</option>
        </select>
        <select class="f-sel" id="filterType" onchange="filterProducts()">
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
    <div class="summary-title">🌞 Build Summary</div>
    <div class="summary-sub">Review your solar system configuration before adding to cart.</div>

    <div class="summary-section">
      <div class="summary-section-title">Core Components</div>
      <div id="summaryComponents"></div>
    </div>

    <div class="summary-section" id="summaryPeriphSection">
      <div class="summary-section-title">Add-On Services</div>
      <div id="summaryPeripherals"></div>
    </div>

    <div class="summary-total-row">
      <span class="summary-total-lbl">Total Build Cost</span>
      <span class="summary-total-val" id="summaryTotal">₱ 0.00</span>
    </div>

    <div class="summary-cta">
      <button class="cta-secondary" onclick="goToStep(1)">← Edit Build</button>
      <button class="cta-primary" onclick="showToast('🛒 Build added to cart! Our team will contact you within 24 hours.')">🛒 Add to Cart & Request Quote</button>
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
  <button class="cart-btn" onclick="goToStep(3)">🛒 Review Build</button>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
// ─── DATA ─────────────────────────────────────────────────────────────────────

const PRODUCTS = {
  panels: {
    title: '🌞 Solar Panels',
    filterLabels: ['Brand', 'Wattage'],
    filterKeys: ['brand', 'wattage'],
    items: [
      { id:'p1', name:'Jinko Solar Tiger Neo 440W Monocrystalline', spec:'440W · Mono PERC · 21.3% eff · 20 panels', price:95000, oldPrice:105000, brand:'Jinko Solar', wattage:'440W', radar:{panels:0.9, output:0.85}, stats:{kw:8.8, coverage:'3–4 BR', savings:480, roi:'4–5 Yrs', co2:6.5} },
      { id:'p2', name:'Canadian Solar HiKu7 450W Bifacial', spec:'450W · Bifacial · 22.1% eff · 20 panels', price:112000, brand:'Canadian Solar', wattage:'450W', radar:{panels:0.95, output:0.9}, stats:{kw:9.0, coverage:'4–5 BR', savings:520, roi:'4–5 Yrs', co2:7.0} },
      { id:'p3', name:'Longi Solar Hi-MO6 405W Monocrystalline', spec:'405W · Mono · 20.9% eff · 20 panels', price:78500, brand:'Longi Solar', wattage:'405W', radar:{panels:0.75, output:0.7}, stats:{kw:8.1, coverage:'2–3 BR', savings:420, roi:'3–4 Yrs', co2:5.8} },
      { id:'p4', name:'Risen Solar Titan 550W Bifacial HJT', spec:'550W · HJT Bifacial · 23.5% eff · 14 panels', price:135000, brand:'Risen Solar', wattage:'550W', radar:{panels:1.0, output:1.0}, stats:{kw:7.7, coverage:'4–5 BR', savings:560, roi:'5–6 Yrs', co2:7.8} },
      { id:'p5', name:'Trina Solar Vertex S 390W Mono', spec:'390W · Mono · 20.4% eff · 22 panels', price:72000, brand:'Trina Solar', wattage:'390W', radar:{panels:0.65, output:0.6}, stats:{kw:8.6, coverage:'2–3 BR', savings:390, roi:'3–4 Yrs', co2:5.4} },
    ]
  },
  inverter: {
    title: '⚡ Inverter',
    filterLabels: ['Brand', 'Type'],
    filterKeys: ['brand', 'type'],
    items: [
      { id:'i1', name:'Growatt SPH 8000TL3 BH-UP 8kW Hybrid', spec:'8kW · Hybrid · 3-Phase · WiFi built-in', price:58000, brand:'Growatt', type:'Hybrid', radar:{output:0.85}, stats:{} },
      { id:'i2', name:'SolarEdge SE10K 10kW Grid-Tie Inverter', spec:'10kW · Grid-Tie · HD-Wave · Module-level MPPT', price:78000, oldPrice:85000, brand:'SolarEdge', type:'Grid-Tie', radar:{output:0.95}, stats:{} },
      { id:'i3', name:'Fronius Symo 8.0-3-M 8kW 3-Phase', spec:'8kW · 3-Phase · SuperFlex Design', price:65000, brand:'Fronius', type:'Grid-Tie', radar:{output:0.8}, stats:{} },
      { id:'i4', name:'Huawei SUN2000-10KTL-M1 10kW Smart', spec:'10kW · Hybrid · AI optimization · 2 MPPT', price:62000, brand:'Huawei', type:'Hybrid', radar:{output:0.9}, stats:{} },
      { id:'i5', name:'Deye SUN-6K-SG03LP1 6kW Hybrid Off-Grid', spec:'6kW · Off-Grid/Hybrid · LV Battery support', price:44000, brand:'Deye', type:'Off-Grid', radar:{output:0.65}, stats:{} },
    ]
  },
  battery: {
    title: '🔋 Battery Storage',
    filterLabels: ['Brand', 'Chemistry'],
    filterKeys: ['brand', 'chemistry'],
    items: [
      { id:'b1', name:'Pylontech US5000 48V 74Ah LiFePO4 (2 units)', spec:'14.8kWh · LiFePO4 · 6000 cycles · CAN/RS485', price:88000, brand:'Pylontech', chemistry:'LiFePO4', radar:{battery:0.85}, stats:{} },
      { id:'b2', name:'BYD Battery-Box Premium HVS 10.2kWh', spec:'10.2kWh · LFP · High Voltage · Modular', price:105000, oldPrice:115000, brand:'BYD', chemistry:'LFP', radar:{battery:0.9}, stats:{} },
      { id:'b3', name:'Dyness Tower B4850 48V 100Ah 4.8kWh', spec:'4.8kWh · LFP · IP20 · Tower design', price:42000, brand:'Dyness', chemistry:'LFP', radar:{battery:0.55}, stats:{} },
      { id:'b4', name:'CATL LUNA 2000-5-E1 5kWh LFP', spec:'5kWh · LFP · -20°C–55°C · IP55', price:65000, brand:'CATL', chemistry:'LFP', radar:{battery:0.7}, stats:{} },
      { id:'b5', name:'Redway 48V 200Ah 9.6kWh LiFePO4 Rack', spec:'9.6kWh · LFP · Rack mount · RS485 comms', price:72000, brand:'Redway', chemistry:'LiFePO4', radar:{battery:0.8}, stats:{} },
    ]
  },
  mounting: {
    title: '🔩 Mounting System',
    filterLabels: ['Brand', 'Type'],
    filterKeys: ['brand', 'type'],
    items: [
      { id:'m1', name:'IronRidge Flush Mount Aluminum Rail System', spec:'Aluminum · Roof · Up to 30 panels', price:18500, brand:'IronRidge', type:'Roof', radar:{mounting:0.8}, stats:{} },
      { id:'m2', name:'Unirac SolarMount Ground Array', spec:'Steel · Ground · Up to 40 panels', price:24000, brand:'Unirac', type:'Ground', radar:{mounting:0.9}, stats:{} },
      { id:'m3', name:'K2 Systems RoofKit Compact', spec:'Aluminum · Roof · Up to 20 panels', price:14200, brand:'K2 Systems', type:'Roof', radar:{mounting:0.65}, stats:{} },
      { id:'m4', name:'Schletter Eco15 Carport Mount', spec:'Galvanized · Carport · Up to 50 panels', price:38800, brand:'Schletter', type:'Carport', radar:{mounting:1.0}, stats:{} },
      { id:'m5', name:'Renusol VS+ Flat Roof Ballast', spec:'Plastic · Flat Roof · No drilling', price:11900, brand:'Renusol', type:'Roof', radar:{mounting:0.55}, stats:{} },
    ]
  },
  wiring: {
    title: '🔌 Wiring & Protection',
    filterLabels: ['Brand', 'Includes'],
    filterKeys: ['brand', 'includes'],
    items: [
      { id:'w1', name:'Complete DC/AC Cable & Protection Kit – Standard', spec:'DC cables · AC breakers · SPD · Disconnect', price:12500, brand:'Generic', includes:'Full Kit', radar:{wiring:0.75}, stats:{} },
      { id:'w2', name:'Schneider Electric Solar Protection Bundle', spec:'RCCB · MCB · SPD · Din Rail enclosure', price:22000, brand:'Schneider', includes:'Full Kit', radar:{wiring:0.9}, stats:{} },
      { id:'w3', name:'ABB Fuse & DC Combiner Box Set', spec:'Fuse holders · Combiner · 1000V rated', price:17500, brand:'ABB', includes:'Combiner', radar:{wiring:0.85}, stats:{} },
      { id:'w4', name:'Basic Cable Tray & MC4 Connector Set', spec:'MC4 connectors · PV cable 4mm² · 30m roll', price:6800, brand:'Generic', includes:'Cables Only', radar:{wiring:0.5}, stats:{} },
    ]
  },
  monitoring: {
    title: '📡 Monitoring System',
    filterLabels: ['Brand', 'Connectivity'],
    filterKeys: ['brand', 'connectivity'],
    items: [
      { id:'mo1', name:'Growatt ShineWifi-X Wireless Monitor', spec:'WiFi · Cloud · Growatt ShinePhone app', price:3200, brand:'Growatt', connectivity:'WiFi', radar:{}, stats:{} },
      { id:'mo2', name:'SolarEdge Monitoring Portal + Meter', spec:'Cell/WiFi · Module-level monitoring', price:8500, brand:'SolarEdge', connectivity:'WiFi+Cell', radar:{}, stats:{} },
      { id:'mo3', name:'Victron GX Touch 50 Display + VRM', spec:'5" touch · VRM cloud · Color display', price:14500, brand:'Victron', connectivity:'LAN/WiFi', radar:{}, stats:{} },
      { id:'mo4', name:'Fronius Solar.web Smart Meter Kit', spec:'Smart meter · Web portal · API access', price:9800, brand:'Fronius', connectivity:'WiFi+LAN', radar:{}, stats:{} },
    ]
  }
};

const PERIPHERALS = [
  { id:'pe1', name:'Site Assessment & Shade Analysis', type:'Service', price:3500, icon:'🏠', desc:'Professional roof inspection + shading report' },
  { id:'pe2', name:'MERALCO Net Metering Application', type:'Service', price:8000, icon:'📋', desc:'Full application processing & requirements' },
  { id:'pe3', name:'Extended 10-Year Labor Warranty', type:'Warranty', price:15000, icon:'🛡️', desc:'Extended workmanship guarantee' },
  { id:'pe4', name:'Smart Home Energy Controller', type:'Accessory', price:22000, icon:'🏡', desc:'Automate load shifting & EV charging' },
  { id:'pe5', name:'Annual Preventive Maintenance Plan', type:'Service', price:12000, icon:'🔧', desc:'Yearly cleaning + electrical check + report' },
  { id:'pe6', name:'Panel Cleaning Kit + Anti-Dust Coating', type:'Accessory', price:4500, icon:'🧹', desc:'Professional-grade cleaning supplies' },
  { id:'pe7', name:'Lightning Arrester & Earthing System', type:'Safety', price:9500, icon:'⚡', desc:'Surge & lightning protection for system' },
  { id:'pe8', name:'CCTV Camera for Inverter Room', type:'Security', price:7800, icon:'📷', desc:'Remote monitoring of equipment room' },
];

// ─── STATE ────────────────────────────────────────────────────────────────────

const selected = { panels: null, inverter: null, battery: null, mounting: null, wiring: null, monitoring: null };
const selectedPeriphs = new Set();
let activeCategory = null;

// ─── RADAR MATH ───────────────────────────────────────────────────────────────

// Hexagon vertices (6 axes): top, top-right, bottom-right, bottom, bottom-left, top-left
// Axes: POWER OUTPUT(top), PANELS(top-right), BATTERY(bot-right), INVERTER(bot), WIRING(bot-left), MOUNTING(top-left)
const RADAR_CENTER = { x: 100, y: 100 };
const RADAR_MAX = 82; // max radius

const AXIS_ANGLES = [
  -90,   // top: POWER OUTPUT
  -30,   // top-right: PANELS
  30,    // bottom-right: BATTERY
  90,    // bottom: INVERTER
  150,   // bottom-left: WIRING
  210,   // top-left: MOUNTING
];

function getRadarPoint(axisIdx, value) {
  const angle = AXIS_ANGLES[axisIdx] * Math.PI / 180;
  const r = RADAR_MAX * value;
  return {
    x: RADAR_CENTER.x + r * Math.cos(angle),
    y: RADAR_CENTER.y + r * Math.sin(angle)
  };
}

function computeRadarValues() {
  // [power_output, panels, battery, inverter, wiring, mounting]
  const vals = [0, 0, 0, 0, 0, 0];

  // Power output: average of panels + inverter output
  const panelsItem = selected.panels;
  const inverterItem = selected.inverter;
  if (panelsItem) vals[1] = panelsItem.radar.panels || 0;
  if (inverterItem) vals[3] = inverterItem.radar.output || 0;
  vals[0] = (vals[1] + vals[3]) / 2;

  // Battery
  const battItem = selected.battery;
  if (battItem) vals[2] = battItem.radar.battery || 0;

  // Wiring
  const wiringItem = selected.wiring;
  if (wiringItem) vals[4] = wiringItem.radar.wiring || 0;

  // Mounting
  const mountItem = selected.mounting;
  if (mountItem) vals[5] = mountItem.radar.mounting || 0;

  return vals;
}

function updateRadar() {
  const vals = computeRadarValues();
  const points = vals.map((v, i) => {
    const pt = getRadarPoint(i, Math.max(0.05, v));
    return `${pt.x.toFixed(1)},${pt.y.toFixed(1)}`;
  });
  document.getElementById('radarPolygon').setAttribute('points', points.join(' '));
}

// ─── STATS ────────────────────────────────────────────────────────────────────

function updateStats() {
  const panelItem = selected.panels;
  if (panelItem && panelItem.stats && panelItem.stats.kw) {
    const s = panelItem.stats;
    document.getElementById('statOutput').textContent = s.kw + ' kWp';
    document.getElementById('statCoverage').textContent = s.coverage;
    document.getElementById('statSavings').textContent = '~₱' + s.savings.toLocaleString() + ' / day';
    document.getElementById('statROI').textContent = s.roi;
    document.getElementById('statCO2').textContent = s.co2 + ' t / year';
  } else {
    ['statOutput','statCoverage','statSavings','statROI','statCO2'].forEach(id => {
      document.getElementById(id).textContent = '–';
    });
  }
  updateBuildCategory();
  updateTotal();
  updateRadar();
}

function updateBuildCategory() {
  const count = Object.values(selected).filter(Boolean).length;
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
  Object.values(selected).forEach(v => { if (v) t += v.price; });
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

function updateCompCard(cat, item) {
  const card = document.getElementById('card-' + cat);
  const nameEl = document.getElementById('name-' + cat);
  const tagEl = document.getElementById('tag-' + cat);
  const iconEl = document.getElementById('icon-' + cat);

  card.classList.remove('done', 'warn', 'active-card');
  if (item) {
    nameEl.textContent = item.name;
    tagEl.textContent = '✓ Added';
    tagEl.className = 'comp-tag tag-g';
    card.classList.add('done');
    iconEl.style.background = 'var(--green)';
  } else {
    nameEl.textContent = 'Select item';
    tagEl.textContent = '+ Select';
    tagEl.className = 'comp-tag tag-a';
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
    const isActive = selected[cat] && selected[cat].id === item.id;
    const saleTag = item.oldPrice ? `<span class="prod-sale">SALE</span> <span class="prod-old-price">₱${item.oldPrice.toLocaleString()}</span>` : '';
    return `
      <div class="prod-card ${isActive ? 'active' : ''}" id="prod-${item.id}" onclick="selectProduct('${cat}', '${item.id}')">
        <div class="prod-img">${getIcon(cat)}</div>
        <div class="prod-inf">
          <div class="prod-name">${item.name}</div>
          <div class="prod-spec">${item.spec}</div>
          <div class="prod-price"><span>₱</span> ${item.price.toLocaleString()} ${saleTag}</div>
          <button class="select-btn" onclick="event.stopPropagation(); selectProduct('${cat}','${item.id}')">${isActive ? '✓ Selected' : 'SELECT'}</button>
        </div>
      </div>`;
  }).join('');
}

function getIcon(cat) {
  const icons = { panels:'🌞', inverter:'⚡', battery:'🔋', mounting:'🔩', wiring:'🔌', monitoring:'📡' };
  return icons[cat] || '⚙️';
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

  const wasSelected = selected[cat] && selected[cat].id === itemId;
  selected[cat] = wasSelected ? null : item;

  updateCompCard(cat, selected[cat]);
  updateStats();
  renderProducts(PRODUCTS[cat].items); // re-render to update active state

  if (!wasSelected) {
    showToast(`✅ ${item.name.split(' ').slice(0,4).join(' ')} added!`);
  }
}

// ─── CLEAR ALL ────────────────────────────────────────────────────────────────

function clearAll() {
  Object.keys(selected).forEach(cat => {
    selected[cat] = null;
    updateCompCard(cat, null);
  });
  selectedPeriphs.clear();
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
    { key: 'panels', label: 'Solar Panels', icon: '🌞' },
    { key: 'inverter', label: 'Inverter', icon: '⚡' },
    { key: 'battery', label: 'Battery Storage', icon: '🔋' },
    { key: 'mounting', label: 'Mounting System', icon: '🔩' },
    { key: 'wiring', label: 'Wiring & Protection', icon: '🔌' },
    { key: 'monitoring', label: 'Monitoring System', icon: '📡' },
  ];

  const compHtml = compCategories.map(({ key, label, icon }) => {
    const item = selected[key];
    if (item) {
      return `<div class="summary-item">
        <div class="summary-item-left">
          <div class="summary-item-icon">${icon}</div>
          <div>
            <div class="summary-item-name">${item.name}</div>
            <div class="summary-item-spec">${item.spec}</div>
          </div>
        </div>
        <div class="summary-item-price">₱ ${item.price.toLocaleString()}</div>
      </div>`;
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
}

// ─── STEPS ────────────────────────────────────────────────────────────────────

function goToStep(n) {
  // Update tabs
  document.querySelectorAll('.step-tab').forEach((tab, idx) => {
    tab.classList.remove('active', 'completed');
    if (idx + 1 === n) tab.classList.add('active');
    if (idx + 1 < n) tab.classList.add('completed');
  });

  // Show page
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page' + n).classList.add('active');

  if (n === 2) renderPeripherals();
  if (n === 3) renderSummary();

  window.scrollTo({ top: 0, behavior: 'smooth' });
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

// ─── MOBILE DRAWER & INIT ─────────────────────────────────────────────────────

// Mobile: update openSelector to use drawer
const _origOpenSelector = openSelector;
function openSelector(cat) {
  activeCategory = cat;
  document.querySelectorAll('.comp-card').forEach(c => c.classList.remove('active-card'));
  const card = document.getElementById('card-' + cat);
  if (card) card.classList.add('active-card');

  if (window.innerWidth <= 767) {
    openDrawer(cat);
  } else {
    const catData = PRODUCTS[cat];
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

function openDrawer(cat) {
  const catData = PRODUCTS[cat];
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
    const isActive = selected[cat] && selected[cat].id === item.id;
    const saleTag = item.oldPrice ? `<span class="prod-sale">SALE</span> <span class="prod-old-price">₱${item.oldPrice.toLocaleString()}</span>` : '';
    return `<div class="prod-card ${isActive?'active':''}" onclick="selectProductDrawer('${cat}','${item.id}')">
      <div class="prod-img">${getIcon(cat)}</div>
      <div class="prod-inf">
        <div class="prod-name">${item.name}</div>
        <div class="prod-spec">${item.spec}</div>
        <div class="prod-price"><span>₱</span> ${item.price.toLocaleString()} ${saleTag}</div>
        <button class="select-btn" style="display:inline-block" onclick="event.stopPropagation();selectProductDrawer('${cat}','${item.id}')">${isActive?'✓ Selected':'SELECT'}</button>
      </div>
    </div>`;
  }).join('');
}

function selectProductDrawer(cat, itemId) {
  const item = PRODUCTS[cat].items.find(i => i.id === itemId);
  if (!item) return;
  const wasSelected = selected[cat] && selected[cat].id === itemId;
  selected[cat] = wasSelected ? null : item;
  updateCompCard(cat, selected[cat]);
  updateStats();
  renderDrawerProducts(PRODUCTS[cat].items);
  if (!wasSelected) {
    showToast(`✅ ${item.name.split(' ').slice(0,4).join(' ')} added!`);
    setTimeout(closeDrawer, 800);
  }
}

updateStats();
</script>
</body>
</html>