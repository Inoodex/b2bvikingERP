@php
  $categoriesActive = request()->routeIs('admin.category.*', 'admin.sub-category.*', 'admin.child-category.*', 'admin.slider.*', 'admin.product-types.*');
  $productsActive   = request()->routeIs('admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*');
  $inventoryActive  = request()->routeIs('admin.inventory-reports.*', 'admin.issues.*', 'admin.issue-returns.*', 'admin.stock-ledger.*');
  $ordersActive     = request()->routeIs('admin.orders.*', 'admin.custom-product-requests.*', 'admin.product-requests.*');
  $purchaseActive   = request()->routeIs('admin.bookings.*', 'admin.purchases.*');
  $procurementActive = request()->routeIs('admin.rfqs.*', 'admin.purchase-orders.*', 'admin.letters-of-credit.*', 'admin.shipments.*', 'admin.goods-receipts.*', 'admin.vendor-returns.*', 'admin.vendor-bills.*');
  $reportsActive    = request()->routeIs('admin.reports.*', 'admin.purchase-reports.*');
  $accountsActive   = request()->routeIs('admin.accounts.*', 'admin.purchase-payments.*', 'admin.vendor-ledger.*');
  $brandsActive     = request()->routeIs('admin.brand.*');
  $vendorsActive    = request()->routeIs('admin.vendor.*');
  $masterActive     = request()->routeIs('admin.master.*');
  $systemActive     = request()->routeIs('admin.users.*', 'admin.role.*', 'admin.permission.*', 'admin.pricing-rules.*', 'admin.taxes.*', 'admin.discounts.*', 'admin.products.announcement.*', 'admin.settings.*');
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
  --sb-width: 268px;
  --sb-width-collapsed: 80px;
  --tb-height: 68px;
  --tb-height-mobile: 58px;

  /* ---- Premium brushed-gold on deep navy ---- */
  --nb-bg: #0a0e1a;
  --nb-bg-2: #0d1220;
  --nb-surface: #131a2b;
  --nb-surface-2: #161e33;
  --nb-border: rgba(201, 168, 106, 0.10);
  --nb-border-strong: rgba(201, 168, 106, 0.22);

  --nb-gold: #cda05a;
  --nb-gold-bright: #e3bd7c;
  --nb-gold-soft: rgba(205, 160, 90, 0.12);
  --nb-gold-glow: rgba(205, 160, 90, 0.45);

  --nb-text: rgba(248, 246, 240, 0.96);
  --nb-muted: rgba(226, 220, 205, 0.40);
  --nb-muted-2: rgba(226, 220, 205, 0.60);

  --nb-danger: #e2685f;
  --nb-danger-soft: rgba(226, 104, 95, 0.12);

  --nb-font-display: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  --nb-font-body: 'Inter', 'Segoe UI', sans-serif;

  --nb-shadow-lg: 0 24px 60px -12px rgba(0, 0, 0, 0.65), 0 4px 16px rgba(0, 0, 0, 0.35);
  --nb-shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.3);

  /* ---- Glass layer ---- */
  --glass-bg: rgba(19, 24, 41, 0.55);
  --glass-bg-strong: rgba(24, 30, 50, 0.72);
  --glass-border: rgba(255, 255, 255, 0.09);
  --glass-highlight: rgba(255, 255, 255, 0.14);
  --glass-blur: blur(22px) saturate(160%);
}

*, *::before, *::after { box-sizing: border-box; }

body {
  padding-left: var(--sb-width);
  padding-top: var(--tb-height);
  transition: padding-left 0.15s ease;
  background: #eef0f4;
  overflow-x: hidden;
  font-family: var(--nb-font-body);
  position: relative;
}

/* Ambient aurora glow — sits behind the glass sidebar/topbar so the blur has depth to pick up */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background:
    radial-gradient(560px 420px at -6% -8%, rgba(205, 160, 90, 0.35), transparent 60%),
    radial-gradient(480px 480px at 18% 55%, rgba(88, 101, 242, 0.16), transparent 65%),
    radial-gradient(520px 420px at 4% 96%, rgba(205, 160, 90, 0.20), transparent 60%),
    #0a0e1a;
  opacity: 0.9;
}

.main-wrapper {
  max-width: 100% !important;
  width: 100% !important;
  padding: 0 !important;
  margin: 0 !important;
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - var(--tb-height));
}
.main-content {
  background: #fff;
  flex: 1;
  padding: 0 26px 26px !important;
  border-radius: 0;
  margin: 0;
  box-shadow: none;
}

.main-footer {
  padding-left: calc(var(--sb-width) + 28px);
  padding-right: 28px;
  background: transparent;
  transition: padding-left 0.3s cubic-bezier(.4,0,.2,1);
}
.main-footer .footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px 20px;
  padding: 18px 28px;
  background: linear-gradient(145deg, rgba(13, 18, 32, 0.85), rgba(10, 14, 26, 0.94));
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 8px 32px -12px rgba(0,0,0,0.3);
  position: relative;
  width: 100%;
}
.main-footer .footer-inner::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1.5px;
  background: linear-gradient(90deg, transparent 5%, rgba(205,160,90,0.3) 30%, rgba(227,189,124,0.5) 50%, rgba(205,160,90,0.3) 70%, transparent 95%);
}
.main-footer a { color: #e3bd7c; text-decoration: none; font-weight: 600; font-size: 13px; transition: color 0.2s; }
.main-footer a:hover { color: #f5d6a0; }
.main-footer .footer-left { color: rgba(248,246,240,0.5); font-size: 13px; display: flex; align-items: center; gap: 6px; line-height: 1.4; }
.main-footer .footer-left a { font-size: 13px; }
.main-footer .footer-center { display: flex; align-items: center; gap: 6px; }
.main-footer .footer-copyright { color: rgba(248,246,240,0.3); font-size: 12px; font-weight: 500; letter-spacing: 0.3px; line-height: 1.4; }
.main-footer .footer-right { color: rgba(248,246,240,0.5); font-size: 13px; display: flex; align-items: center; gap: 6px; line-height: 1.4; }
.main-footer .footer-right a { font-size: 13px; }

body.sidebar-collapsed { padding-left: 0; }
body.sidebar-collapsed .main-footer { padding-left: 28px; }

@media (max-width: 991.98px) {
  body { padding-left: 0 !important; padding-top: calc(var(--tb-height-mobile) + env(safe-area-inset-top)); }
  .main-wrapper { min-height: calc(100vh - var(--tb-height-mobile) - env(safe-area-inset-top)); }
  .main-footer { padding-left: 16px !important; padding-right: 16px; }
  .main-footer .footer-inner { padding: 14px 18px; }
}

@media (max-width: 767.98px) {
  .main-footer .footer-inner {
    flex-direction: column;
    text-align: center;
    padding: 14px 18px;
    gap: 6px;
  }
  .main-footer .footer-center { order: -1; }
  .main-footer .footer-left,
  .main-footer .footer-right { justify-content: center; }
}

@media (max-width: 374.98px) {
  .main-footer { padding-left: 12px !important; padding-right: 12px; }
  .main-footer .footer-inner { padding: 12px 14px; border-radius: 12px; }
  .main-footer a { font-size: 11.5px; }
  .main-footer .footer-left,
  .main-footer .footer-right,
  .main-footer .footer-copyright { font-size: 11px; }
}

/* ========================================
   TOPBAR
   ======================================== */

.topbar {
  position: fixed;
  top: 0;
  left: var(--sb-width);
  right: 0;
  height: var(--tb-height);
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border-bottom: 1px solid var(--glass-border);
  box-shadow: var(--nb-shadow-sm), inset 0 1px 0 var(--glass-highlight);
  z-index: 1030;
  display: flex;
  align-items: center;
  padding: 0 22px;
  transition: left 0.15s ease;
}

.topbar::after {
  content: '';
  position: absolute;
  left: 0; right: 0; bottom: -1px;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--nb-gold-glow) 18%, transparent 40%);
  opacity: 0.6;
}

body.sidebar-collapsed .topbar { left: 0; }

.hamburger-toggle {
  position: relative;
  color: var(--nb-text) !important;
  padding: 9px 11px;
  border-radius: 10px;
  transition: all 0.22s cubic-bezier(.2,.8,.2,1);
  font-size: 17px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  box-shadow: inset 0 1px 0 var(--glass-highlight), 0 2px 8px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.hamburger-toggle:hover {
  background: var(--nb-gold-soft);
  border-color: var(--nb-border-strong);
  color: var(--nb-gold-bright) !important;
  transform: translateY(-1px);
  box-shadow: inset 0 1px 0 var(--glass-highlight), 0 8px 20px rgba(205, 160, 90, 0.22);
}
.hamburger-toggle:active { transform: translateY(0) scale(0.94); }

.topbar-title {
  font-family: var(--nb-font-display);
  color: var(--nb-muted-2);
  font-size: 12.5px;
  font-weight: 600;
  margin-left: 18px;
  letter-spacing: 0.4px;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
  height: 100%;
  padding-right: 4px;
}

.navbar-right > li { display: flex; align-items: center; height: 100%; }

.divider-vertical {
  width: 1px;
  height: 26px;
  background: linear-gradient(180deg, transparent, var(--nb-border-strong), transparent);
  margin: 0 6px;
}

/* Notification dropdown */
.notif-dropdown {
  width: 372px !important;
  padding: 0 !important;
  border-radius: 18px !important;
  overflow: hidden;
  position: relative;
}

.notif-dropdown::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--glass-highlight) 30%, var(--glass-highlight) 70%, transparent);
  z-index: 2;
}

.notif-dropdown .notif-header {
  position: relative;
  padding: 18px 20px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--glass-border);
  background: linear-gradient(180deg, rgba(205,160,90,0.08), transparent);
}

.notif-dropdown .notif-header .notif-title {
  font-family: var(--nb-font-display);
  font-size: 14.5px;
  font-weight: 700;
  color: var(--nb-text);
  letter-spacing: 0.2px;
}

.notif-dropdown .notif-header .notif-mark-read {
  font-size: 11px;
  color: var(--nb-gold-bright);
  text-decoration: none;
  font-weight: 600;
  transition: opacity 0.2s;
  display: flex;
  align-items: center;
  gap: 5px;
}

.notif-dropdown .notif-header .notif-mark-read:hover { opacity: 0.75; }

.notif-dropdown .notif-body { max-height: 350px; overflow-y: auto; padding: 8px; }

.notif-dropdown .notif-body::-webkit-scrollbar { width: 4px; }
.notif-dropdown .notif-body::-webkit-scrollbar-thumb { background: var(--nb-gold-glow); border-radius: 10px; }

.notif-dropdown .notif-body .notif-empty {
  text-align: center;
  padding: 40px 16px;
  color: var(--nb-muted);
  font-size: 12px;
}

.notif-dropdown .notif-body .notif-empty i {
  font-size: 30px;
  display: block;
  margin-bottom: 10px;
  opacity: 0.25;
  color: var(--nb-gold);
}

.notif-dropdown .notif-footer { padding: 13px 20px; text-align: center; border-top: 1px solid var(--nb-border); }

.notif-dropdown .notif-footer a {
  color: var(--nb-gold-bright);
  text-decoration: none;
  font-size: 12.5px;
  font-weight: 600;
  transition: opacity 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.notif-dropdown .notif-footer a:hover { opacity: 0.75; }

.notif-dropdown .notif-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 13px 14px;
  border-radius: 10px;
  transition: background 0.15s;
  text-decoration: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.notif-dropdown .notif-item:last-child { border-bottom: none; }
.notif-dropdown .notif-item:hover { background: rgba(255, 255, 255, 0.035); }

.notif-dropdown .notif-item .notif-icon {
  width: 38px; height: 38px; min-width: 38px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 11px;
  font-size: 14px;
  color: #1a1408;
  background: linear-gradient(135deg, var(--nb-gold-bright), var(--nb-gold));
}

.notif-dropdown .notif-item .notif-content { flex: 1; min-width: 0; }

.notif-dropdown .notif-item .notif-content .notif-title {
  font-size: 13px;
  font-weight: 600;
  color: rgba(248, 246, 240, 0.88);
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notif-dropdown .notif-item .notif-content .notif-desc {
  font-size: 11.5px;
  color: var(--nb-muted);
  margin-top: 2px;
}

.notif-dropdown .notif-item .notif-content .notif-time {
  font-size: 10px;
  font-weight: 700;
  color: var(--nb-gold-bright);
  margin-top: 0px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.notif-dropdown .notif-item.unread {
  background: var(--nb-gold-soft);
  border-left: 3px solid var(--nb-gold);
}

.notif-dropdown .notif-item.out-of-stock.unread {
  background: var(--nb-danger-soft);
  border-left: 3px solid var(--nb-danger);
}
.notif-dropdown .notif-item.out-of-stock .notif-icon {
  background: linear-gradient(135deg, #ef8983, var(--nb-danger));
  color: #fff;
}

@keyframes nbBellRing {
  0%, 100% { transform: rotate(0); }
  10% { transform: rotate(14deg); }
  20% { transform: rotate(-10deg); }
  30% { transform: rotate(8deg); }
  40% { transform: rotate(-6deg); }
  50% { transform: rotate(4deg); }
  60% { transform: rotate(-2deg); }
  70%, 90% { transform: rotate(0); }
}

@keyframes nbBadgePulse {
  0% { box-shadow: 0 0 0 0 rgba(226, 104, 95, 0.55); }
  70% { box-shadow: 0 0 0 8px rgba(226, 104, 95, 0); }
  100% { box-shadow: 0 0 0 0 rgba(226, 104, 95, 0); }
}

.navbar-right .notification-toggle {
  position: relative;
  padding: 0;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 13px;
  transition: all 0.25s cubic-bezier(.2,.8,.2,1);
  color: var(--nb-text) !important;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  box-shadow: inset 0 1px 0 var(--glass-highlight), 0 2px 8px rgba(0,0,0,0.2);
  overflow: hidden;
  line-height: 1;
}

.navbar-right .notification-toggle i { line-height: 1; font-size: 16px; position: relative; z-index: 1; }

.navbar-right .notification-toggle:hover {
  background: var(--nb-gold-soft);
  border-color: var(--nb-border-strong);
  color: var(--nb-gold-bright) !important;
  transform: translateY(-2px);
  box-shadow: inset 0 1px 0 var(--glass-highlight), 0 10px 22px rgba(205, 160, 90, 0.25);
}
.navbar-right .notification-toggle:active { transform: translateY(0) scale(0.95); }

.navbar-right .notification-toggle.has-count {
  background: var(--nb-danger-soft);
  border-color: rgba(226, 104, 95, 0.22);
}

.navbar-right .notification-toggle.has-count i {
  animation: nbBellRing 1.2s ease-in-out;
  transform-origin: top center;
  color: var(--nb-danger);
}

.navbar-right .notification-toggle .badge {
  position: absolute;
  top: -3px;
  right: -3px;
  background: linear-gradient(135deg, #ef8983, var(--nb-danger));
  color: #fff;
  font-size: 8px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--nb-bg);
  box-shadow: 0 2px 8px rgba(226, 104, 95, 0.4);
  animation: nbBadgePulse 2s infinite;
}

.navbar-right .nav-link-user {
  display: flex;
  align-items: center;
  padding: 3px;
  border-radius: 100px;
  border: 1px solid var(--glass-border);
  background: rgba(255,255,255,0.04);
  box-shadow: inset 0 1px 0 var(--glass-highlight);
  transition: border-color 0.22s ease, box-shadow 0.22s ease, transform 0.22s cubic-bezier(.2,.8,.2,1);
}
.navbar-right .nav-link-user:hover {
  border-color: var(--nb-border-strong);
  box-shadow: inset 0 1px 0 var(--glass-highlight), 0 0 0 5px rgba(205, 160, 90, 0.12);
  transform: translateY(-2px);
}

.navbar-right .nav-link-user img {
  border: 2px solid var(--nb-gold);
  border-radius: 50%;
  transition: all 0.2s ease;
}

.navbar-right .nav-link-user:hover img { border-color: var(--nb-gold-bright); }

.dropdown-menu {
  position: relative;
  background: var(--glass-bg-strong);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border: 1px solid var(--glass-border);
  border-radius: 15px;
  padding: 6px;
  margin-top: 6px !important;
  min-width: 210px;
  box-shadow: var(--nb-shadow-lg), inset 0 1px 0 var(--glass-highlight);
  overflow: hidden;
  animation: nbDropdownIn 0.18s cubic-bezier(.2,.8,.2,1);
}

@keyframes nbDropdownIn {
  from { opacity: 0; transform: translateY(-6px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.dropdown-menu::before {
  content: '';
  position: absolute;
  top: -40%; right: -30%;
  width: 60%; height: 90%;
  background: radial-gradient(circle, rgba(205, 160, 90, 0.10), transparent 70%);
  pointer-events: none;
}

.dropdown-menu .dropdown-item {
  padding: 9px 13px;
  color: var(--nb-muted-2) !important;
  font-size: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 8px;
  transition: all 0.15s ease;
}

.dropdown-menu .dropdown-item i { font-size: 13px; width: 16px; color: var(--nb-muted); }
.dropdown-menu .dropdown-item:hover { background: var(--nb-gold-soft) !important; color: var(--nb-text) !important; transform: translateX(2px); }
.dropdown-menu .dropdown-item:hover i { color: var(--nb-gold-bright); }
.dropdown-menu .dropdown-item.text-danger:hover { background: var(--nb-danger-soft) !important; color: #fff !important; }
.dropdown-menu .dropdown-item.text-danger:hover i { color: var(--nb-danger); }
.dropdown-menu .dropdown-divider { margin: 4px 8px; border-color: var(--nb-border); }

/* ========================================
   LEFT SIDEBAR
   ======================================== */

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(6, 9, 16, 0.65);
  backdrop-filter: blur(2px);
  z-index: 1049;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.25s ease, visibility 0.25s ease;
}

.sidebar-overlay.open { opacity: 1; visibility: visible; pointer-events: auto; }
.sidebar-overlay:not(.open) { pointer-events: none; }

.app-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: var(--sb-width);
  background: var(--glass-bg) !important;
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border-right: 1px solid var(--glass-border);
  box-shadow: inset -1px 0 0 var(--glass-highlight), var(--nb-shadow-lg);
  z-index: 1050;
  display: flex;
  flex-direction: column;
  transition: transform 0.15s ease;
  overflow: visible;
}

.app-sidebar::after {
  content: '';
  position: absolute;
  top: 0; right: -1px; bottom: 0;
  width: 1px;
  background: linear-gradient(180deg, transparent, var(--nb-gold-glow) 8%, transparent 60%);
  opacity: 0.5;
}

body.sidebar-collapsed .app-sidebar { transform: translateX(-100%); }

.sidebar-header {
  position: relative;
  height: var(--tb-height);
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 20px;
  border-bottom: 1px solid var(--nb-border);
  flex-shrink: 0;
  overflow: hidden;
  white-space: nowrap;
  background: radial-gradient(160px 60px at 10% 0%, rgba(205,160,90,0.10), transparent 70%);
}

.sidebar-header i {
  position: relative;
  width: 36px;
  height: 36px;
  min-width: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
  background: linear-gradient(135deg, var(--nb-gold-bright), var(--nb-gold));
  color: #1a1408;
  font-size: 15px;
  box-shadow: 0 4px 14px rgba(205, 160, 90, 0.35), inset 0 1px 0 rgba(255,255,255,0.45);
  overflow: hidden;
}

.sidebar-header i::after {
  content: '';
  position: absolute;
  top: -60%; left: -20%;
  width: 60%; height: 220%;
  background: linear-gradient(115deg, transparent, rgba(255,255,255,0.55), transparent);
  transform: rotate(20deg);
  animation: nbShine 4.5s ease-in-out infinite;
}

@keyframes nbShine {
  0%, 15% { transform: translateX(-140%) rotate(20deg); }
  55%, 100% { transform: translateX(140%) rotate(20deg); }
}

.sidebar-header span {
  font-family: var(--nb-font-display);
  color: var(--nb-text);
  font-weight: 800;
  font-size: 15px;
  letter-spacing: 0.6px;
}

.sidebar-body { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 14px 12px 40px; scrollbar-width: thin; scrollbar-color: var(--nb-gold) transparent; }

.sb-footer { margin-top: 10px; border-top: 1px solid var(--nb-border); padding-top: 10px; }

.sidebar-body::-webkit-scrollbar { width: 4px; }
.sidebar-body::-webkit-scrollbar-track { background: transparent; }
.sidebar-body::-webkit-scrollbar-thumb { background: var(--nb-gold); border-radius: 10px; }
.sidebar-body::-webkit-scrollbar-thumb:hover { background: var(--nb-gold-bright); }

.sb-nav { list-style: none; margin: 0; padding: 0; }

.sb-item { margin-bottom: 3px; position: relative; }

.sb-link {
  position: relative;
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 11px 13px;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.88) !important;
  font-family: var(--nb-font-display);
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
  white-space: nowrap;
  letter-spacing: 0.1px;
  overflow: hidden;
}

.sb-link::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(100deg, transparent 30%, rgba(255,255,255,0.06) 48%, transparent 66%);
  transform: translateX(-120%);
  transition: transform 0.55s ease;
  pointer-events: none;
}

.sb-link:hover::before { transform: translateX(120%); }

.sb-link > i:first-child {
  width: 18px;
  min-width: 18px;
  text-align: center;
  font-size: 14.5px;
  color: var(--nb-muted);
  transition: color 0.18s ease, transform 0.18s ease;
  position: relative;
  z-index: 1;
}

.sb-link > span { position: relative; z-index: 1; }

.sb-link:hover { background: rgba(255, 255, 255, 0.055); color: var(--nb-text) !important; transform: translateX(2px); }
.sb-link:hover > i:first-child { color: var(--nb-gold-bright); transform: scale(1.08); }

.sb-item.active > .sb-link {
  background: linear-gradient(90deg, var(--nb-gold-soft), rgba(205,160,90,0.04) 80%);
  backdrop-filter: blur(6px);
  color: var(--nb-text) !important;
  box-shadow: inset 0 0 0 1px var(--nb-border-strong), inset 0 1px 0 rgba(255,255,255,0.08), 0 4px 14px -6px rgba(205,160,90,0.25);
}
.sb-item.active > .sb-link > i:first-child { color: var(--nb-gold-bright); }

.sb-item.active > .sb-link::before {
  content: '';
  position: absolute;
  left: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 22px;
  border-radius: 3px;
  background: linear-gradient(180deg, var(--nb-gold-bright), var(--nb-gold));
  box-shadow: 0 0 12px var(--nb-gold-glow);
}

.sb-link span.sb-label { flex: 1; overflow: hidden; text-overflow: ellipsis; }

.sb-arrow {
  font-size: 9px !important;
  width: auto !important;
  min-width: auto !important;
  color: var(--nb-muted) !important;
  transition: transform 0.25s ease, color 0.2s ease;
}

.sb-item.open > .sb-link .sb-arrow { transform: rotate(180deg); color: var(--nb-gold-bright) !important; }

.sb-submenu {
  list-style: none;
  margin: 3px 0 8px 0;
  padding: 0;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  position: relative;
}

.sb-item.open > .sb-submenu { max-height: 3000px; }

.sb-submenu::before {
  content: '';
  position: absolute;
  left: 24px;
  top: 2px;
  bottom: 10px;
  width: 1px;
  background: linear-gradient(180deg, var(--nb-border-strong), var(--nb-border) 70%, transparent);
}

.sb-submenu li a {
  position: relative;
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 9px 12px 9px 42px;
  color: rgba(255, 255, 255, 0.68);
  font-size: 12px;
  font-weight: 500;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.18s ease;
  white-space: nowrap;
}

.sb-submenu li a i { font-size: 11.5px; width: 14px; color: rgba(226, 220, 205, 0.28); transition: color 0.15s ease; }
.sb-submenu li a:hover { background: rgba(255, 255, 255, 0.045); color: var(--nb-text); transform: translateX(2px); }
.sb-submenu li a:hover i { color: var(--nb-gold-bright); }

.sb-submenu .sb-submenu-header {
  padding: 10px 12px 3px 42px;
  color: var(--nb-gold);
  font-family: var(--nb-font-display);
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-weight: 700;
  opacity: 0.75;
}

.sb-submenu-divider { margin: 5px 14px 5px 42px; border-top: 1px solid var(--nb-border); }

.sb-footer { margin-top: 10px; border-top: 1px solid var(--nb-border); padding-top: 10px; }

.sb-flyout-title { display: none; }

/* ========================================
   MOBILE / RESPONSIVE — tablets down to small phones
   ======================================== */

@media (max-width: 991.98px) {
  body { padding-left: 0 !important; }
  .main-wrapper.container, .main-wrapper { max-width: 100% !important; width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; }

  .topbar {
    left: 0 !important;
    height: calc(var(--tb-height-mobile) + env(safe-area-inset-top));
    padding: 0 12px;
    padding-top: env(safe-area-inset-top);
    padding-left: max(12px, env(safe-area-inset-left));
    padding-right: max(12px, env(safe-area-inset-right));
  }

  .main-content {
    margin: 0 !important;
    padding: 18px !important;
    border-radius: 0 !important;
    min-height: calc(100vh - var(--tb-height-mobile) - env(safe-area-inset-top) - 0px);
  }

  .app-sidebar {
    width: min(300px, 86vw) !important;
    max-width: 86vw;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    box-shadow: 16px 0 50px rgba(0, 0, 0, 0.55);
    padding-left: env(safe-area-inset-left);
  }

  .app-sidebar.mobile-open { transform: translateX(0); }

  .app-sidebar .sb-link { justify-content: flex-start !important; padding: 11px 13px !important; }
  .app-sidebar .sidebar-header span { display: inline !important; }
  .app-sidebar .sb-item .sb-submenu {
    position: static !important;
    width: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    box-shadow: none !important;
    border: none !important;
    background: transparent !important;
    padding: 0 !important;
    max-height: 0 !important;
  }
  .app-sidebar .sb-item.open .sb-submenu { max-height: 3000px !important; }
  .app-sidebar .sb-item .sb-flyout-title { display: none !important; }

  /* ---- Right-side icon cluster: tightened + precisely centered ---- */
  .navbar-right {
    gap: 6px;
    padding-right: 0;
  }

  .divider-vertical { display: none !important; }

  .navbar-right > li {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .navbar-right .notification-toggle {
    width: 38px;
    height: 38px;
    border-radius: 11px;
  }
  .navbar-right .notification-toggle i {
    font-size: 15px;
    line-height: 0;
  }
  .navbar-right .notification-toggle .badge {
    top: -3px;
    right: -3px;
  }

  .navbar-right .nav-link-user { padding: 2px; }
  .navbar-right .nav-link-user img { width: 30px !important; height: 30px !important; }

  .dropdown-menu.dropdown-list {
    position: fixed !important;
    top: calc(var(--tb-height-mobile) + env(safe-area-inset-top) + 6px) !important;
    left: 10px !important;
    right: 10px !important;
    width: auto !important;
    max-width: none;
    background: rgba(10, 14, 26, 0.85) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
  }
}

/* ---- Standard phones (e.g. iPhone 12/13/14, Pixel) ---- */
@media (max-width: 480px) {
  :root { --tb-height-mobile: 56px; }

  .topbar { padding-left: max(10px, env(safe-area-inset-left)); padding-right: max(10px, env(safe-area-inset-right)); }

  .hamburger-toggle { padding: 8px 10px; font-size: 16px; }

  .navbar-right { gap: 4px; }

  .navbar-right .notification-toggle { width: 36px; height: 36px; border-radius: 10px; }
  .navbar-right .notification-toggle i { font-size: 14px; margin-top: 0px; }
  .navbar-right .notification-toggle .badge {
    min-width: 16px;
    height: 16px;
    font-size: 7px;
    top: -5px;
    right: -5px;
    border-width: 1.5px;
  }

  .navbar-right .nav-link-user img { width: 28px !important; height: 28px !important; }

  .notif-dropdown .notif-header { padding: 15px 16px 13px; }
  .notif-dropdown .notif-item { padding: 11px 12px; }

  /* Bigger text on mobile */
  .sb-link { font-size: 14.5px !important; padding: 13px 15px !important; }
  .sb-submenu li a { font-size: 14px !important; padding: 11px 13px 11px 44px !important; }
  .sidebar-header span { font-size: 17px; }
  .topbar-title { font-size: 14px; }
  .notif-dropdown .notif-item .notif-content .notif-title { font-size: 14px; }
  .notif-dropdown .notif-item .notif-content .notif-desc { font-size: 12.5px; }
  .notif-dropdown .notif-item .notif-content .notif-time { font-size: 11px; }
}

/* ---- Small / compact phones (e.g. iPhone SE, older Android) ---- */
@media (max-width: 374.98px) {
  :root { --tb-height-mobile: 52px; }

  .topbar { padding-left: max(8px, env(safe-area-inset-left)); padding-right: max(8px, env(safe-area-inset-right)); }

  .hamburger-toggle { padding: 7px 9px; font-size: 15px; border-radius: 9px; }

  .sidebar-header { padding: 0 14px; gap: 10px; }
  .sidebar-header i { width: 32px; height: 32px; min-width: 32px; font-size: 13px; }
  .sidebar-header span { font-size: 13px; }

  .navbar-right { gap: 3px; }

  .navbar-right .notification-toggle { width: 34px; height: 34px; border-radius: 9px; }
  .navbar-right .notification-toggle i { font-size: 13px; margin-top: 0px; }

  .navbar-right .nav-link-user img { width: 26px !important; height: 26px !important; }

  .app-sidebar { width: min(272px, 88vw) !important; max-width: 88vw; }

  .sb-link { padding: 12px 14px !important; font-size: 13.5px; }
  .sb-submenu li a { padding: 10px 12px 10px 40px; font-size: 13px; }

  .notif-dropdown { border-radius: 14px !important; }
  .notif-dropdown .notif-title { font-size: 13.5px; }
}

/* ---- Landscape phones: shorter viewport height ---- */
@media (max-width: 991.98px) and (max-height: 420px) and (orientation: landscape) {
  :root { --tb-height-mobile: 50px; }
  .app-sidebar { padding-top: env(safe-area-inset-top); }
  .sidebar-header { height: 50px; }
  .sidebar-body { padding: 8px 10px; }
  .sb-link { padding: 8px 12px; }
}

@media (max-width: 575.98px) {
  .dropdown-menu.dropdown-menu-right:not(.dropdown-list) {
    position: fixed !important;
    top: calc(var(--tb-height-mobile) + 8px) !important;
    left: 8px !important;
    right: 8px !important;
    width: auto !important;
    max-width: none;
    transform: none !important;
  }
}
</style>

{{-- ============================================
     TOPBAR
     ============================================ --}}
<nav class="topbar">
  <button class="hamburger-toggle" id="sidebarToggle" aria-label="Toggle menu">
    <i class="fas fa-bars"></i>
  </button>

  <ul class="navbar-right" style="list-style:none;">
    <li class="divider-vertical d-none d-md-block"></li>

    <li class="dropdown dropdown-list-toggle">
      <a id="low-stock-count-toggle" href="#" data-toggle="dropdown" class="notification-toggle" aria-label="Notifications">
        <i class="fas fa-bell"></i>
        <span id="low-stock-count-badge" class="badge" style="display: none;">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-right dropdown-list notif-dropdown">
        <div class="notif-header">
          <span class="notif-title">Notifications</span>
          <a href="#" onclick="markAllAsRead(); return false;" class="notif-mark-read">
            <i class="fas fa-check-double"></i> Mark All Read
          </a>
        </div>
        <div id="low-stock-list" class="notif-body">
          <div class="notif-empty">
            <i class="fas fa-inbox"></i>
            No new notifications
          </div>
        </div>
        <div class="notif-footer">
          <a href="{{ route('admin.notifications.all') }}">
            View All <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </li>

    <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link-user">
        <img alt="image" height="32px" width="32px" src="https://ui-avatars.com/api/?background=cda05a&color=1a1408&bold=true&name={{ urlencode(Auth::user()->name) }}" class="rounded-circle">
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <a href="{{ route('admin.profile') }}" class="dropdown-item has-icon">
          <i class="far fa-user"></i> Profile
        </a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item has-icon text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </form>
      </div>
    </li>
  </ul>
</nav>

{{-- ============================================
     LEFT SIDEBAR
     ============================================ --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="app-sidebar" id="appSidebar">
  <div class="sidebar-header">
    <i class="fas fa-store"></i>
    <span>{{ strtoupper(collect(explode(' ', Auth::user()->name))->map(fn($w) => $w[0])->take(2)->implode('')) }}</span>
  </div>

  <div class="sidebar-body">
    <ul class="sb-nav">

      <li class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a href="{{ route('admin.dashboard') }}" class="sb-link" title="Dashboard">
          <i class="fas fa-th-large"></i>
          <span class="sb-label">Dashboard</span>
        </a>
      </li>

      <li class="sb-item has-children {{ $categoriesActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Categories">
          <i class="fas fa-layer-group"></i>
          <span class="sb-label">Categories</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Categories</li>
          <li><a href="{{ route('admin.category.index') }}"><i class="fas fa-tags"></i> Category</a></li>
          <li><a href="{{ route('admin.sub-category.index') }}"><i class="fas fa-tag"></i> Sub Category</a></li>
          <li><a href="{{ route('admin.child-category.index') }}"><i class="fas fa-tag"></i> Child Category</a></li>
          <li class="sb-submenu-divider"></li>
          <li><a href="{{ route('admin.slider.index') }}"><i class="fas fa-images"></i> Slider</a></li>
          <li><a href="{{ route('admin.product-types.index') }}"><i class="fas fa-calendar-alt"></i> Occasion Type</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $productsActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Products">
          <i class="fas fa-box-open"></i>
          <span class="sb-label">Products</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Products</li>
          <li><a href="{{ route('admin.products.index') }}"><i class="fas fa-cubes"></i> All Products</a></li>
          <li class="sb-submenu-header">Attributes</li>
          <li><a href="{{ route('admin.units.index') }}"><i class="fas fa-weight-hanging"></i> Units</a></li>
          <li><a href="{{ route('admin.colors.index') }}"><i class="fas fa-palette"></i> Colors</a></li>
          <li><a href="{{ route('admin.sizes.index') }}"><i class="fas fa-ruler"></i> Sizes</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $inventoryActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Inventory">
          <i class="fas fa-warehouse"></i>
          <span class="sb-label">Inventory</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Inventory</li>
          <li><a href="{{ route('admin.inventory-reports.index') }}"><i class="fas fa-clipboard-check"></i> Current Stock</a></li>
          <li><a href="{{ route('admin.issues.index') }}"><i class="fas fa-arrow-right"></i> Stock Issues</a></li>
          <li><a href="{{ route('admin.issue-returns.index') }}"><i class="fas fa-undo-alt"></i> Stock Returns</a></li>
          <li class="sb-submenu-divider"></li>
          <li><a href="{{ route('admin.stock-ledger.index') }}"><i class="fas fa-book"></i> Stock Ledger</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $ordersActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Orders">
          <i class="fas fa-shopping-bag"></i>
          <span class="sb-label">Orders</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Orders</li>
          <li><a href="{{ route('admin.orders.index') }}"><i class="fas fa-store-alt"></i> Outlet/Shop Orders</a></li>
          <li><a href="{{ route('admin.custom-product-requests.index') }}"><i class="fas fa-sync-alt"></i> Custom Requests</a></li>
          <li class="sb-submenu-divider"></li>
          <li><a href="{{ route('admin.product-requests.index') }}"><i class="fas fa-history"></i> Old Requests</a></li>
          <li><a href="{{ route('admin.product-requests.create') }}"><i class="fas fa-plus-circle"></i> Create Request</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $purchaseActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Purchase">
          <i class="fas fa-cart-plus"></i>
          <span class="sb-label">Purchase</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Purchase</li>
          <li><a href="{{ route('admin.bookings.index') }}"><i class="fas fa-shopping-cart"></i> All Order Place</a></li>
          <li><a href="{{ route('admin.purchases.index') }}"><i class="fas fa-check-circle"></i> All Order Receive</a></li>
          <li class="sb-submenu-header">Quick Actions</li>
          <li><a href="{{ route('admin.purchases.create') }}"><i class="fas fa-plus-circle"></i> Create New</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $procurementActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Procurement">
          <i class="fas fa-file-contract"></i>
          <span class="sb-label">Procurement</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Procurement</li>
          <li><a href="{{ route('admin.rfqs.index') }}"><i class="fas fa-list"></i> All RFQs</a></li>
          <li><a href="{{ route('admin.rfqs.create') }}"><i class="fas fa-plus-circle"></i> Create RFQ</a></li>
          <li><a href="{{ route('admin.purchase-orders.index') }}"><i class="fas fa-file-invoice"></i> Purchase Orders (PO)</a></li>
          <li><a href="{{ route('admin.vendor-bills.index') }}"><i class="fas fa-file-invoice-dollar"></i> Vendor Bills (Invoices)</a></li>
          <li><a href="{{ route('admin.letters-of-credit.index') }}"><i class="fas fa-university"></i> LC Register</a></li>
          <li class="sb-submenu-header">Logistics & Receiving</li>
          <li><a href="{{ route('admin.shipments.index') }}"><i class="fas fa-ship"></i> Shipments & SIT</a></li>
          <li><a href="{{ route('admin.goods-receipts.index') }}"><i class="fas fa-dolly"></i> Goods Receipts (GRN)</a></li>
          <li><a href="{{ route('admin.vendor-returns.index') }}"><i class="fas fa-undo"></i> Vendor Returns</a></li>
          <li class="sb-submenu-header">Procurement Reports</li>
          <li><a href="{{ route('admin.purchase-reports.supplier-wise') }}"><i class="fas fa-user-tag"></i> Supplier-wise Purchase</a></li>
          <li><a href="{{ route('admin.purchase-reports.item-wise') }}"><i class="fas fa-cubes"></i> Item-wise Purchase</a></li>
          <li><a href="{{ route('admin.purchase-reports.total-value') }}"><i class="fas fa-calculator"></i> Total Purchase Value</a></li>
          <li><a href="{{ route('admin.purchase-reports.vs-last-year') }}"><i class="fas fa-balance-scale"></i> Purchase vs Last Year</a></li>
          <li><a href="{{ route('admin.purchase-reports.pr-status') }}"><i class="fas fa-clipboard-list"></i> PR Status & Pending</a></li>
          <li><a href="{{ route('admin.purchase-reports.po-status') }}"><i class="fas fa-file-signature"></i> PO Issued & Items</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $reportsActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Reports">
          <i class="fas fa-chart-bar"></i>
          <span class="sb-label">Reports</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Reports</li>
          <li class="sb-submenu-header">Analytics</li>
          <li><a href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-pie"></i> All Reports</a></li>
          <li><a href="{{ route('admin.reports.orders') }}"><i class="fas fa-file-invoice"></i> Order & Issue Report</a></li>
          <li class="sb-submenu-header">Stock Reports</li>
          <li><a href="{{ route('admin.reports.stock') }}"><i class="fas fa-boxes"></i> Stock Reports</a></li>
          <li><a href="{{ route('admin.reports.low-stock') }}"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</a></li>
          
          <li class="sb-submenu-header">Current Stock Report</li>
          <li><a href="{{ route('admin.reports.current-stock') }}"><i class="fas fa-cubes"></i> Current Stock Report</a></li>

          <li class="sb-submenu-header">Financial</li>
          <li><a href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-coins"></i> Profit & Loss</a></li>
          <li><a href="{{ route('admin.reports.purchase') }}"><i class="fas fa-shopping-bag"></i> Purchase History</a></li>
          <li><a href="{{ route('admin.reports.product-purchase-history') }}"><i class="fas fa-search"></i> Product Tracking</a></li>
          <li class="sb-submenu-header">Procurement Reports</li>
          <li><a href="{{ route('admin.purchase-reports.supplier-wise') }}"><i class="fas fa-user-tag"></i> Supplier-wise Purchase</a></li>
          <li><a href="{{ route('admin.purchase-reports.item-wise') }}"><i class="fas fa-cubes"></i> Item-wise Purchase</a></li>
          <li><a href="{{ route('admin.purchase-reports.total-value') }}"><i class="fas fa-calculator"></i> Total Purchase Value</a></li>
          <li><a href="{{ route('admin.purchase-reports.vs-last-year') }}"><i class="fas fa-balance-scale"></i> Purchase vs Last Year</a></li>
          <li><a href="{{ route('admin.purchase-reports.pr-status') }}"><i class="fas fa-clipboard-list"></i> PR Status & Pending</a></li>
          <li><a href="{{ route('admin.purchase-reports.po-status') }}"><i class="fas fa-file-signature"></i> PO Issued & Items</a></li>
          <li class="sb-submenu-divider"></li>
          <li><a href="{{ route('admin.reports.audit') }}"><i class="fas fa-clipboard-check"></i> Audit Report</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $accountsActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Accounts">
          <i class="fas fa-file-invoice-dollar"></i>
          <span class="sb-label">Accounts</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Accounts</li>
          <li class="sb-submenu-header">Customer Accounts</li>
          <li><a href="{{ route('admin.accounts.index') }}"><i class="fas fa-users"></i> Customer Transactions</a></li>
          <li><a href="{{ route('admin.accounts.record-payment') }}"><i class="fas fa-hand-holding-usd"></i> Receive Payment</a></li>
          <li><a href="{{ route('admin.accounts.due-orders') }}"><i class="fas fa-clock"></i> Customer Due Orders</a></li>
          <li class="sb-submenu-header">Vendor Accounts & AP</li>
          <li><a href="{{ route('admin.vendor-bills.index') }}"><i class="fas fa-file-invoice-dollar"></i> Vendor Bills</a></li>
          <li><a href="{{ route('admin.purchase-payments.index') }}"><i class="fas fa-receipt"></i> Payment Vouchers</a></li>
          <li><a href="{{ route('admin.vendor-ledger.index') }}"><i class="fas fa-book"></i> Supplier Ledger & Statement</a></li>
          <li><a href="{{ route('admin.vendor-ledger.aging') }}"><i class="fas fa-clock"></i> AP Aging Analysis</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $brandsActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Brands">
          <i class="fas fa-tag"></i>
          <span class="sb-label">Brands</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Brands</li>
          <li><a href="{{ route('admin.brand.index') }}"><i class="fas fa-list"></i> All Brands</a></li>
          <li><a href="{{ route('admin.brand.create') }}"><i class="fas fa-plus-circle"></i> Add Brand</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $vendorsActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Vendors">
          <i class="fas fa-truck"></i>
          <span class="sb-label">Vendors</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Vendors</li>
          <li><a href="{{ route('admin.vendor.index') }}"><i class="fas fa-list"></i> All Vendors</a></li>
          <li><a href="{{ route('admin.vendor.create') }}"><i class="fas fa-plus-circle"></i> Add Vendor</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $masterActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="Enterprise Setup">
          <i class="fas fa-building"></i>
          <span class="sb-label">Enterprise Setup</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">Enterprise Setup</li>
          <li><a href="{{ route('admin.master.companies.index') }}"><i class="fas fa-city"></i> Companies</a></li>
          <li><a href="{{ route('admin.master.outlets.index') }}"><i class="fas fa-warehouse"></i> Outlets & Hubs</a></li>
          <li><a href="{{ route('admin.master.departments.index') }}"><i class="fas fa-sitemap"></i> Departments</a></li>
          <li><a href="{{ route('admin.master.currencies.index') }}"><i class="fas fa-coins"></i> Currencies</a></li>
          <li><a href="{{ route('admin.master.approval-workflows.index') }}"><i class="fas fa-check-double"></i> Approval Workflows</a></li>
        </ul>
      </li>

      <li class="sb-item has-children {{ $systemActive ? 'active open' : '' }}">
        <a href="#" class="sb-link sb-toggle" title="System">
          <i class="fas fa-cog"></i>
          <span class="sb-label">System</span>
          <i class="fas fa-chevron-down sb-arrow"></i>
        </a>
        <ul class="sb-submenu">
          <li class="sb-flyout-title">System</li>
          <li class="sb-submenu-header">User Management</li>
          <li><a href="{{ route('admin.users.index') }}"><i class="fas fa-users-cog"></i> Users</a></li>
          <li><a href="{{ route('admin.role.index') }}"><i class="fas fa-user-tag"></i> Roles</a></li>
          <li><a href="{{ route('admin.permission.index') }}"><i class="fas fa-lock"></i> Permissions</a></li>
          <li class="sb-submenu-header">Business Rules</li>
          <li><a href="{{ route('admin.pricing-rules.index') }}"><i class="fas fa-dollar-sign"></i> Pricing Rules</a></li>
          <li><a href="{{ route('admin.taxes.index') }}"><i class="fas fa-percent"></i> Tax / VAT</a></li>
          <li><a href="{{ route('admin.discounts.index') }}"><i class="fas fa-tags"></i> Discount</a></li>
          <li class="sb-submenu-header">System Settings</li>
          <li><a href="{{ route('admin.products.announcement.index') }}"><i class="fas fa-bullhorn"></i> Product Announcement</a></li>
          <li><a href="{{ route('admin.settings.index') }}"><i class="fas fa-sliders-h"></i> Settings</a></li>
        </ul>
      </li>

    </ul>
  </div>
</aside>

<script>
(function () {
  var sidebar = document.getElementById('appSidebar');
  var overlay = document.getElementById('sidebarOverlay');
  var toggleBtn = document.getElementById('sidebarToggle');

  function isDesktop() { return window.innerWidth >= 992; }

  function openMobile() {
    var scrollW = window.innerWidth - document.documentElement.clientWidth;
    sidebar.classList.add('mobile-open');
    document.body.style.overflow = 'hidden';
    document.body.style.paddingRight = scrollW + 'px';
    setTimeout(function () { overlay.classList.add('open'); }, 100);
  }

  function closeMobile() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }

  function toggleSidebar() {
    if (isDesktop()) {
      document.body.classList.toggle('sidebar-collapsed');
      try { localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0'); } catch (e) {}
    } else {
      if (sidebar.classList.contains('mobile-open')) { closeMobile(); }
      else { openMobile(); }
    }
  }

  // Fresh clone — no inherited listeners
  var fresh = document.createElement('button');
  fresh.className = toggleBtn.className;
  fresh.id = toggleBtn.id;
  fresh.setAttribute('aria-label', toggleBtn.getAttribute('aria-label'));
  fresh.innerHTML = toggleBtn.innerHTML;
  toggleBtn.parentNode.replaceChild(fresh, toggleBtn);

  var locked = false;
  function handleToggle(e) {
    if (locked) return;
    locked = true;
    fresh.style.pointerEvents = 'none';
    toggleSidebar();
    setTimeout(function () { locked = false; fresh.style.pointerEvents = ''; }, 500);
  }
  fresh.addEventListener('click', handleToggle);
  fresh.addEventListener('touchstart', function (e) {
    if (locked) return;
    e.preventDefault();
    handleToggle(e);
  }, { passive: false });

  overlay.addEventListener('click', closeMobile);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMobile();
  });

  try {
    if (isDesktop() && localStorage.getItem('sidebar-collapsed') === '1') {
      // document.body.classList.add('sidebar-collapsed');
    }
  } catch (e) {}

  // Accordion submenu toggle
  document.querySelectorAll('.sb-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      if (document.body.classList.contains('sidebar-collapsed') && isDesktop()) return;
      e.preventDefault();
      var item = this.closest('.sb-item');
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.sb-item.open').forEach(function (openItem) {
        if (openItem !== item) openItem.classList.remove('open');
      });
      item.classList.toggle('open', !isOpen);
    });
  });

  // Close mobile sidebar on resize to desktop
  window.addEventListener('resize', function () {
    if (isDesktop()) closeMobile();
  });
})();
</script>