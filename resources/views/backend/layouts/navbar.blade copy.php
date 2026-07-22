@php
  $categoriesActive = request()->routeIs('admin.category.*', 'admin.sub-category.*', 'admin.child-category.*', 'admin.slider.*', 'admin.product-types.*');
  $productsActive   = request()->routeIs('admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*');
  $inventoryActive  = request()->routeIs('admin.inventory-reports.*', 'admin.issues.*', 'admin.issue-returns.*', 'admin.stock-ledger.*');
  $ordersActive     = request()->routeIs('admin.orders.*', 'admin.custom-product-requests.*', 'admin.product-requests.*');
  $purchaseActive   = request()->routeIs('admin.bookings.*', 'admin.purchases.*');
  $reportsActive    = request()->routeIs('admin.reports.*');
  $accountsActive   = request()->routeIs('admin.accounts.*');
  $brandsActive     = request()->routeIs('admin.brand.*');
  $vendorsActive    = request()->routeIs('admin.vendor.*');
  $systemActive     = request()->routeIs('admin.users.*', 'admin.role.*', 'admin.permission.*', 'admin.pricing-rules.*', 'admin.taxes.*', 'admin.discounts.*', 'admin.products.announcement.*', 'admin.settings.*');
@endphp

<style>
:root {
  --sb-width: 260px;
  --sb-width-collapsed: 78px;
  --tb-height: 64px;
  --tb-height-mobile: 56px;
  --nb-bg: #0f172a;
  --nb-surface: #1e293b;
  --nb-border: rgba(255, 255, 255, 0.06);
  --nb-primary: #60a5fa;
  --nb-primary-soft: rgba(96, 165, 250, 0.1);
  --nb-text: rgba(255, 255, 255, 0.92);
  --nb-muted: rgba(255, 255, 255, 0.38);
  --nb-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
}

*, *::before, *::after { box-sizing: border-box; }

/* Push page content out of the sidebar/topbar's way.
   If your layout already has its own wrapper/padding, adjust or remove this block. */
body {
  padding-left: var(--sb-width);
  padding-top: var(--tb-height);
  transition: padding-left 0.25s ease;
  background: #f4f6f9;
  overflow-x: hidden;
}

body.layout-3 .main-content {
  background: #fff;
  min-height: calc(100vh - var(--tb-height) - 32px);
  padding: 24px !important;
  border-radius: 8px;
  margin: 16px;
}

.main-footer {
  padding-left: calc(var(--sb-width) + 24px);
}

body.sidebar-collapsed {
  padding-left: var(--sb-width-collapsed);
}

@media (max-width: 991.98px) {
  body {
    padding-left: 0 !important;
    padding-top: var(--tb-height-mobile);
  }
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
  background: #0f172a;
  border-bottom: 1px solid var(--nb-border);
  box-shadow: var(--nb-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.03);
  z-index: 1030;
  display: flex;
  align-items: center;
  padding: 0 20px;
  transition: left 0.25s ease;
}

body.sidebar-collapsed .topbar {
  left: var(--sb-width-collapsed);
}

.hamburger-toggle {
  color: #fff !important;
  padding: 8px 10px;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-size: 18px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--nb-border);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.hamburger-toggle:hover {
  background: rgba(255, 255, 255, 0.08);
}

.topbar-title {
  color: rgba(255, 255, 255, 0.7);
  font-size: 13px;
  font-weight: 600;
  margin-left: 16px;
  letter-spacing: 0.3px;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
  height: 100%;
  padding-right: 16px;
}

.navbar-right > li {
  display: flex;
  align-items: center;
  height: 100%;
}

.divider-vertical {
  width: 1px;
  height: 28px;
  background: var(--nb-border);
  margin: 0 8px;
}

/* Notification dropdown */
.notif-dropdown {
  width: 360px !important;
  padding: 0 !important;
  border-radius: 12px !important;
  overflow: hidden;
}

.notif-dropdown .notif-header {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--nb-border);
}

.notif-dropdown .notif-header .notif-title {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.3px;
}

.notif-dropdown .notif-header .notif-mark-read {
  font-size: 11px;
  color: var(--nb-primary);
  text-decoration: none;
  font-weight: 600;
  transition: opacity 0.2s;
}

.notif-dropdown .notif-header .notif-mark-read:hover {
  opacity: 0.8;
}

.notif-dropdown .notif-body {
  max-height: 340px;
  overflow-y: auto;
  padding: 6px;
}

.notif-dropdown .notif-body::-webkit-scrollbar { width: 4px; }
.notif-dropdown .notif-body::-webkit-scrollbar-thumb { background: rgba(96, 165, 250, 0.3); border-radius: 10px; }

.notif-dropdown .notif-body .notif-empty {
  text-align: center;
  padding: 32px 16px;
  color: rgba(255, 255, 255, 0.35);
  font-size: 12px;
}

.notif-dropdown .notif-body .notif-empty i {
  font-size: 28px;
  display: block;
  margin-bottom: 8px;
  opacity: 0.25;
}

.notif-dropdown .notif-footer {
  padding: 12px 20px;
  text-align: center;
  border-top: 1px solid var(--nb-border);
}

.notif-dropdown .notif-footer a {
  color: var(--nb-primary);
  text-decoration: none;
  font-size: 12.5px;
  font-weight: 600;
  transition: opacity 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.notif-dropdown .notif-footer a:hover { opacity: 0.8; }

.notif-dropdown .notif-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 8px;
  transition: background 0.15s;
  text-decoration: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.notif-dropdown .notif-item:last-child { border-bottom: none; }
.notif-dropdown .notif-item:hover { background: rgba(255, 255, 255, 0.04); }

.notif-dropdown .notif-item .notif-icon {
  width: 36px;
  height: 36px;
  min-width: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 14px;
  color: #fff;
}

.notif-dropdown .notif-item .notif-content { flex: 1; min-width: 0; }

.notif-dropdown .notif-item .notif-content .notif-title {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.85);
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notif-dropdown .notif-item .notif-content .notif-desc {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.45);
  margin-top: 2px;
}

.notif-dropdown .notif-item .notif-content .notif-time {
  font-size: 10px;
  font-weight: 600;
  color: var(--nb-primary);
  margin-top: 4px;
}

.notif-dropdown .notif-item.unread {
  background: rgba(96, 165, 250, 0.06);
  border-left: 3px solid var(--nb-primary);
}

.notif-dropdown .notif-item.out-of-stock.unread {
  background: rgba(239, 68, 68, 0.06);
  border-left: 3px solid #ef4444;
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
  0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
  70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
  100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.navbar-right .notification-toggle {
  position: relative;
  padding: 0;
  width: 44px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  transition: all 0.3s ease;
  color: #fff !important;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--nb-border);
  overflow: visible;
  line-height: 1;
}

.navbar-right .notification-toggle i {
  line-height: 1;
  font-size: 18px;
}

.navbar-right .notification-toggle:hover {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
}

.navbar-right .notification-toggle i { font-size: 16px; }

.navbar-right .notification-toggle.has-count {
  background: rgba(239, 68, 68, 0.06);
  border-color: rgba(239, 68, 68, 0.15);
}

.navbar-right .notification-toggle.has-count i {
  animation: nbBellRing 1.2s ease-in-out;
  transform-origin: top center;
}

.navbar-right .notification-toggle .badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  font-size: 8px;
  font-weight: 700;
  min-width: 17px;
  height: 20px;
  padding: 0 4px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--nb-bg);
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  animation: nbBadgePulse 2s infinite;
}

.navbar-right .nav-link-user {
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 100px;
}

.navbar-right .nav-link-user img {
  border: 2px solid rgba(96, 165, 250, 0.3);
  transition: all 0.2s ease;
}

.navbar-right .nav-link-user:hover img {
  border-color: var(--nb-primary);
}

.dropdown-menu {
  background: rgba(30, 41, 59, 0.98);
  backdrop-filter: blur(20px);
  border: 1px solid var(--nb-border);
  border-radius: 10px;
  padding: 5px;
  margin-top: 4px !important;
  min-width: 200px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

.dropdown-menu .dropdown-item {
  padding: 7px 12px;
  color: rgba(255, 255, 255, 0.7) !important;
  font-size: 11.5px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 9px;
  border-radius: 6px;
  transition: all 0.15s ease;
}

.dropdown-menu .dropdown-item i { font-size: 13px; width: 16px; color: var(--nb-muted); }
.dropdown-menu .dropdown-item:hover { background: var(--nb-primary-soft) !important; color: #fff !important; }
.dropdown-menu .dropdown-item:hover i { color: var(--nb-primary); }
.dropdown-menu .dropdown-divider { margin: 3px 8px; border-color: var(--nb-border); }

/* ========================================
   LEFT SIDEBAR (desktop persistent, mobile off-canvas)
   ======================================== */

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  z-index: 1049;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.25s ease, visibility 0.25s ease;
}

.sidebar-overlay.open { opacity: 1; visibility: visible; }

.app-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: var(--sb-width);
  background: #0f172a !important;
  border-right: 1px solid var(--nb-border);
  z-index: 1050;
  display: flex;
  flex-direction: column;
  transition: width 0.25s ease;
  overflow: visible;
}

body.sidebar-collapsed .app-sidebar { width: var(--sb-width-collapsed); }

.sidebar-header {
  height: var(--tb-height);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 18px;
  border-bottom: 1px solid var(--nb-border);
  flex-shrink: 0;
  overflow: hidden;
  white-space: nowrap;
}

.sidebar-header i {
  width: 32px;
  height: 32px;
  min-width: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  background: var(--nb-primary-soft);
  color: var(--nb-primary);
  font-size: 14px;
}

.sidebar-header span {
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  letter-spacing: 0.3px;
}

body.sidebar-collapsed .sidebar-header span { display: none; }

.sidebar-body {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 10px;
}

body.sidebar-collapsed .sidebar-body { overflow: visible; }

.sidebar-body::-webkit-scrollbar { width: 4px; }
.sidebar-body::-webkit-scrollbar-thumb { background: rgba(96, 165, 250, 0.3); border-radius: 10px; }

.sb-nav { list-style: none; margin: 0; padding: 0; }

.sb-item { margin-bottom: 2px; position: relative; }

.sb-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 12px;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.82) !important;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.sb-link > i:first-child {
  width: 18px;
  min-width: 18px;
  text-align: center;
  font-size: 15px;
  color: rgba(255, 255, 255, 0.5);
  transition: color 0.15s ease;
}

.sb-link:hover,
.sb-link:hover > i:first-child { background: rgba(255, 255, 255, 0.05); color: #fff !important; }

.sb-item.active > .sb-link { background: var(--nb-primary-soft); color: #fff !important; }
.sb-item.active > .sb-link > i:first-child { color: var(--nb-primary); }

.sb-link span.sb-label { flex: 1; overflow: hidden; text-overflow: ellipsis; }

.sb-arrow {
  font-size: 10px !important;
  width: auto !important;
  min-width: auto !important;
  color: rgba(255, 255, 255, 0.3) !important;
  transition: transform 0.2s ease;
}

.sb-item.open > .sb-link .sb-arrow { transform: rotate(180deg); color: var(--nb-primary) !important; }

.sb-submenu {
  list-style: none;
  margin: 2px 0 6px 0;
  padding: 0;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.25s ease;
}

.sb-item.open > .sb-submenu { max-height: 700px; }

.sb-submenu li a {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 9px 12px 9px 40px;
  color: rgba(255, 255, 255, 0.55);
  font-size: 12.5px;
  font-weight: 500;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.sb-submenu li a i { font-size: 12px; width: 14px; color: rgba(255, 255, 255, 0.3); }
.sb-submenu li a:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
.sb-submenu li a:hover i { color: var(--nb-primary); }

.sb-submenu .sb-submenu-header {
  padding: 8px 12px 2px 40px;
  color: var(--nb-muted);
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  font-weight: 700;
}

.sb-submenu-divider { margin: 4px 12px 4px 40px; border-top: 1px solid var(--nb-border); }

.sb-footer {
  margin-top: 8px;
  border-top: 1px solid var(--nb-border);
  padding-top: 8px;
}

/* --- Collapsed desktop rail: hide labels, flyout submenus on hover --- */
body.sidebar-collapsed .sb-label,
body.sidebar-collapsed .sb-arrow,
body.sidebar-collapsed .sb-submenu-header,
body.sidebar-collapsed .sb-submenu-divider { display: none; }

body.sidebar-collapsed .sb-link { justify-content: center; padding: 12px; }

body.sidebar-collapsed .sb-item .sb-submenu {
  position: absolute;
  left: calc(100% + 8px);
  top: 0;
  width: 220px;
  max-height: none;
  background: var(--nb-surface);
  border: 1px solid var(--nb-border);
  border-radius: 10px;
  padding: 6px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
  opacity: 0;
  visibility: hidden;
  transform: translateX(-6px);
  transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease;
  margin: 0;
}

body.sidebar-collapsed .sb-item:hover .sb-submenu {
  opacity: 1;
  visibility: visible;
  transform: translateX(0);
}

body.sidebar-collapsed .sb-item .sb-flyout-title {
  display: block;
  padding: 8px 10px;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  border-bottom: 1px solid var(--nb-border);
  margin-bottom: 4px;
}

.sb-flyout-title { display: none; }

/* ========================================
   MOBILE: sidebar becomes off-canvas overlay
   ======================================== */

@media (max-width: 991.98px) {
  .topbar { left: 0 !important; height: var(--tb-height-mobile); padding: 0 12px; }

  .app-sidebar {
    width: 280px !important;
    max-width: 86vw;
    transform: translateX(-100%);
    transition: transform 0.28s ease;
    box-shadow: 12px 0 40px rgba(0, 0, 0, 0.5);
  }

  .app-sidebar.mobile-open { transform: translateX(0); }

  body.sidebar-collapsed .sb-label,
  body.sidebar-collapsed .sb-arrow,
  body.sidebar-collapsed .sb-submenu-header,
  body.sidebar-collapsed .sb-submenu-divider { display: revert; }

  .app-sidebar .sb-link { justify-content: flex-start !important; padding: 11px 12px !important; }
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
  .app-sidebar .sb-item.open .sb-submenu { max-height: 700px !important; }
  .app-sidebar .sb-item .sb-flyout-title { display: none !important; }

  .navbar-right .notification-toggle { width: 36px; height: 36px; }
  .navbar-right .notification-toggle i { font-size: 15px; }

  .dropdown-menu.dropdown-list {
    width: calc(100vw - 32px) !important;
    max-width: 400px;
    right: -8px !important;
    left: auto !important;
  }
}

@media (max-width: 575.98px) {
  .dropdown-menu.dropdown-menu-right:not(.dropdown-list) {
    position: fixed !important;
    top: 60px !important;
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
        <img alt="image" height="30px" width="30px" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" class="rounded-circle">
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
     LEFT SIDEBAR (desktop: persistent + collapsible, mobile: off-canvas)
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
          <li class="sb-submenu-header">Financial</li>
          <li><a href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-coins"></i> Profit & Loss</a></li>
          <li><a href="{{ route('admin.reports.purchase') }}"><i class="fas fa-shopping-bag"></i> Purchase History</a></li>
          <li><a href="{{ route('admin.reports.product-purchase-history') }}"><i class="fas fa-search"></i> Product Tracking</a></li>
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
          <li class="sb-submenu-header">Vendor Accounts</li>
          <li><a href="{{ route('admin.accounts.vendor-payments.index') }}"><i class="fas fa-truck"></i> Vendor Payments</a></li>
          <li><a href="{{ route('admin.accounts.vendor-payments.record-payment') }}"><i class="fas fa-money-check-alt"></i> Pay Vendor Invoice</a></li>
          <li><a href="{{ route('admin.accounts.vendor-payments.due-purchases') }}"><i class="fas fa-hourglass-half"></i> Vendor Due Purchases</a></li>
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
    sidebar.classList.add('mobile-open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeMobile() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  function toggleSidebar() {
    if (isDesktop()) {
      var collapsed = document.body.classList.toggle('sidebar-collapsed');
      try { localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0'); } catch (e) {}
    } else {
      if (sidebar.classList.contains('mobile-open')) {
        closeMobile();
      } else {
        openMobile();
      }
    }
  }

  toggleBtn.addEventListener('click', function (e) {
    e.preventDefault();
    toggleSidebar();
  });

  overlay.addEventListener('click', closeMobile);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMobile();
  });

  // restore collapsed state on desktop
  try {
    if (isDesktop() && localStorage.getItem('sidebar-collapsed') === '1') {
      // document.body.classList.add('sidebar-collapsed'); // disabled to show full sidebar by default
    }
  } catch (e) {}

  // accordion toggle (expanded desktop + mobile)
  document.querySelectorAll('.sb-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      if (document.body.classList.contains('sidebar-collapsed') && isDesktop()) {
        // collapsed rail: submenu shows on hover, ignore click-toggle
        return;
      }
      e.preventDefault();
      var item = this.closest('.sb-item');
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.sb-item.open').forEach(function (openItem) {
        if (openItem !== item) openItem.classList.remove('open');
      });
      item.classList.toggle('open', !isOpen);
    });
  });

  // reset mobile state when resizing up to desktop
  window.addEventListener('resize', function () {
    if (isDesktop()) closeMobile();
  });
})();
</script>