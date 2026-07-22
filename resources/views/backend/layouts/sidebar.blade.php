<!-- <div class="main-sidebar sidebar-style-2">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="{{ route('admin.dashboard') }}">{{ Auth::user()->name }}</a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
      <a href="{{ route('admin.dashboard') }}">{{ substr(Auth::user()->name, 0, 2) }}</a>
    </div>
    <ul class="sidebar-menu">
      <li class="menu-header">Dashboard</li>
      <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
      </li>

      @canany(['Manage Categories', 'Manage Products', 'Manage Order Place', 'Manage Order Receive'])
      <li class="menu-header">E-Commerce</li>
      @endcanany

      @can('Manage Categories')
      <li class="dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-list"></i>
          <span>Manage Categories</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link"
              href="{{ route('admin.category.index') }}"><i class="fas fa-folder"></i> Category </a></li>
          <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link"
              href="{{ route('admin.sub-category.index') }}"><i class="fas fa-folder-open"></i> Sub Category </a></li>
          <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link"
              href="{{ route('admin.child-category.index') }}"><i class="fas fa-level-down-alt"></i> Child Category </a>
          </li>
          <li class="{{ setActive(['admin.product-types.*']) }}"><a class="nav-link"
              href="{{ route('admin.product-types.index') }}"><i class="fas fa-layer-group"></i> Occasion Type </a></li>
        </ul>
      </li>
      @endcan

      @canany(['Manage Products', 'View Product Stock'])
      <li class="menu-header">Products</li>
      <li class="dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box"></i>
          <span>Manage Products</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link"
              href="{{ route('admin.products.index') }}"><i class="fas fa-boxes"></i> Products</a></li>
          @can('Manage Products')
          <li class="{{ setActive(['admin.units.*']) }}"><a class="nav-link" href="{{ route('admin.units.index') }}"><i
                class="fas fa-balance-scale"></i> Units</a></li>
          <li class="{{ setActive(['admin.colors.*']) }}"><a class="nav-link"
              href="{{ route('admin.colors.index') }}"><i class="fas fa-palette"></i> Colors</a></li>
          <li class="{{ setActive(['admin.sizes.*']) }}"><a class="nav-link" href="{{ route('admin.sizes.index') }}"><i
                class="fas fa-ruler"></i> Sizes</a></li>
          @endcan
        </ul>
      </li>
      @endcanany

      @canany(['Manage Order Place', 'Manage Order Receive'])
      @can('Manage Order Place')
      <li class="menu-header">Order Place</li>
      <li class="dropdown {{ setActive(['admin.bookings.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-book"></i>
          <span>Manage Order Place</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.bookings.*']) }}"><a class="nav-link"
              href="{{ route('admin.bookings.index') }}"><i class="fas fa-calendar-check"></i> Order Place</a></li>
        </ul>
      </li>
      @endcan

      @can('Manage Order Receive')
      <li class="menu-header">Order Receive</li>
      <li class="dropdown {{ setActive(['admin.purchases.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-shopping-cart"></i>
          <span>Manage Order Receive</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.purchases.index']) }}"><a class="nav-link"
              href="{{ route('admin.purchases.index') }}"><i class="fas fa-receipt"></i> Order Receive</a></li>
          <li class="{{ setActive(['admin.purchases.create']) }}"><a class="nav-link"
              href="{{ route('admin.purchases.create') }}"><i class="fas fa-plus"></i> Create New</a></li>
        </ul>
      </li>
      @endcan
      @endcanany

      @canany(['Manage Product Requests', 'Create Product Requests', 'View Product Requests'])
      <li class="menu-header">{{ Auth::user()->can('Manage Product Requests') ? 'Outlet Request' : 'Product Request' }}
      </li>
      <li class="dropdown {{ setActive(['admin.product-requests.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box-open"></i>
          <span>{{ Auth::user()->can('Manage Product Requests') ? 'Outlet Request' : 'Product Request' }}</span></a>
        <ul class="dropdown-menu">
          @canany(['Manage Product Requests', 'View Product Requests'])
          <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link"
              href="{{ route('admin.product-requests.index') }}"><i class="fas fa-clipboard-list"></i> Requests</a></li>
          @endcanany
          @can('Create Product Requests')
          <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link"
              href="{{ route('admin.product-requests.create') }}"><i class="fas fa-plus"></i> Create New</a></li>
          @endcan
        </ul>
      </li>
      @endcanany

      @canany(['Manage Custom Product Requests', 'Create Custom Product Requests', 'View Custom Product Requests'])
      <li class="menu-header">Custom Request</li>
      <li class="dropdown no-hover {{ setActive(['admin.custom-product-requests.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-list-alt"></i>
          <span>Custom Request</span></a>
        <ul class="dropdown-menu">
          @canany(['Manage Custom Product Requests', 'View Custom Product Requests'])
          <li class="{{ setActive(['admin.custom-product-requests.index']) }}"><a class="nav-link"
              href="{{ route('admin.custom-product-requests.index') }}"><i class="fas fa-list-alt"></i> All Requests</a>
          </li>
          @endcanany
          @can('Create Custom Product Requests')
          @if(!Auth::user()->hasRole('Admin'))
          <li class="{{ setActive(['admin.custom-product-requests.create']) }}"><a class="nav-link"
              href="{{ route('admin.custom-product-requests.create') }}"><i class="fas fa-plus-circle"></i> New
              Product</a></li>
          @endif
          @endcan
        </ul>
      </li>
      @endcanany



      @can('Manage Reports')
      <li class="menu-header">Reports</li>
      <li class="dropdown {{ setActive(['admin.reports.index', 'admin.reports.stock', 'admin.reports.purchase', 'admin.reports.product-purchase-history', 'admin.reports.low-stock', 'admin.reports.profit-loss']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-chart-line"></i>
          <span>Reports</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.reports.index']) }}"><a class="nav-link"
              href="{{ route('admin.reports.index') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li class="{{ setActive(['admin.reports.stock']) }}"><a class="nav-link"
              href="{{ route('admin.reports.stock') }}"><i class="fas fa-boxes"></i> Stock Valuation</a></li>
          <li class="{{ setActive(['admin.reports.purchase']) }}"><a class="nav-link"
              href="{{ route('admin.reports.purchase') }}"><i class="fas fa-history"></i> Purchase History</a></li>
          <li class="{{ setActive(['admin.reports.product-purchase-history']) }}"><a class="nav-link"
              href="{{ route('admin.reports.product-purchase-history') }}"><i class="fas fa-map-marker-alt"></i> Product
              Tracking</a></li>
          <li class="{{ setActive(['admin.reports.low-stock']) }}"><a class="nav-link"
              href="{{ route('admin.reports.low-stock') }}"><i class="fas fa-exclamation-triangle"></i> Low Stock
              Alert</a></li>
          <li class="{{ setActive(['admin.reports.profit-loss']) }}"><a class="nav-link"
              href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-chart-bar"></i> Profit & Loss</a></li>
        </ul>
      </li>

      <li class="{{ setActive(['admin.reports.current-stock']) }}">
        <a href="{{ route('admin.reports.current-stock') }}" class="nav-link"><i class="fas fa-cubes"></i>
          <span>Current Stock Report</span></a>
      </li>
      @endcan

      @canany(['Manage Brands', 'Manage Vendors'])
      <li class="menu-header">Brands & Vendors</li>
      @can('Manage Brands')
      <li class="dropdown {{ setActive(['admin.brand.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-tag"></i>
          <span>Brands</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.brand.index']) }}"><a class="nav-link"
              href="{{ route('admin.brand.index') }}">All Brands</a></li>
          <li class="{{ setActive(['admin.brand.create']) }}"><a class="nav-link"
              href="{{ route('admin.brand.create') }}">Add Brand</a></li>
        </ul>
      </li>
      @endcan

      @can('Manage Vendors')
      <li class="dropdown {{ setActive(['admin.vendor.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-truck"></i>
          <span>Vendors</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.vendor.index']) }}"><a class="nav-link"
              href="{{ route('admin.vendor.index') }}">All Vendors</a></li>
          <li class="{{ setActive(['admin.vendor.create']) }}"><a class="nav-link"
              href="{{ route('admin.vendor.create') }}">Add Vendor</a></li>
        </ul>
      </li>
      @endcan
      @endcanany


      @can('Manage Inventory')
      <li class="menu-header">Inventory System</li>
      <li
        class="dropdown {{ setActive(['admin.issues.*', 'admin.reports.stock', 'admin.stock-ledger.index', 'admin.inventory-reports.index']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-warehouse"></i>
          <span>Inventory Plane</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.inventory-reports.index']) }}"><a class="nav-link"
              href="{{ route('admin.inventory-reports.index') }}"><i class="fas fa-boxes"></i> Current Stock</a></li>
          <li class="{{ setActive(['admin.issues.index']) }}"><a class="nav-link"
              href="{{ route('admin.issues.index') }}"><i class="fas fa-dolly"></i> Stock Issues</a></li>
          <li class="{{ setActive(['admin.stock-ledger.index']) }}"><a class="nav-link"
              href="{{ route('admin.stock-ledger.index') }}"><i class="fas fa-history"></i> Stock Ledger</a></li>
        </ul>
      </li>
      @endcan

      @can('Administration')
      <li class="menu-header">System</li>
      <li
        class="dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*', 'admin.pricing-rules.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-cogs"></i>
          <span>Administration</span></a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link"
              href="{{ route('admin.users.index') }}">Users</a></li>
          <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link"
              href="{{ route('admin.permission.index') }}">Permissions</a></li>
          <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link"
              href="{{ route('admin.role.index') }}">Roles</a></li>
          <li class="{{ setActive(['admin.pricing-rules.*']) }}"><a class="nav-link"
              href="{{ route('admin.pricing-rules.index') }}">Pricing Rules</a></li>
          <li class="{{ setActive(['admin.settings.*']) }}"><a class="nav-link"
              href="{{ route('admin.settings.index') }}">Settings</a></li>
        </ul>
      </li>
      @endcan


    </ul>
  </aside>
</div>

<style>
:root {
  --sb-bg: #070b14;
  --sb-surface: #0d1525;
  --sb-border: rgba(255, 255, 255, 0.05);
  --sb-primary: #6366f1;
  --sb-primary-soft: rgba(99, 102, 241, 0.1);
  --sb-text: rgba(255, 255, 255, 0.9);
  --sb-muted: rgba(255, 255, 255, 0.4);
  --sb-width: 268px;
}

.main-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: var(--sb-width);
  background: linear-gradient(180deg, #070b14 0%, #0d1525 50%, #070b14 100%);
  border-right: 1px solid var(--sb-border);
  box-shadow: none;
  z-index: 1040;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transform: translateX(-100%);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

#sidebar-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.sidebar-brand {
  padding: 18px 20px;
  border-bottom: 1px solid var(--sb-border);
  min-height: 70px;
  display: flex;
  align-items: center;
}

.sidebar-brand a {
  color: #fff !important;
  font-weight: 700;
  font-size: 16px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-brand-sm {
  display: none;
}

.sidebar-menu {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 12px 12px 20px;
  list-style: none;
  margin: 0;
}

.sidebar-menu::-webkit-scrollbar {
  width: 3px;
}

.sidebar-menu::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-menu::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.2);
  border-radius: 10px;
}

.sidebar-menu .menu-header {
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: rgba(255, 255, 255, 0.2);
  padding: 20px 14px 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.sidebar-menu .menu-header::after {
  content: '';
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.04), transparent);
}

.sidebar-menu li {
  list-style: none;
  margin-bottom: 2px;
}

.sidebar-menu li a {
  display: flex;
  align-items: center;
  padding: 10px 14px;
  color: var(--sb-muted) !important;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.25s ease;
  gap: 12px;
  cursor: pointer;
  border: none;
  width: 100%;
  text-align: left;
}

.sidebar-menu li a i {
  font-size: 16px;
  width: 20px;
  text-align: center;
  color: var(--sb-muted);
  flex-shrink: 0;
}

.sidebar-menu li a:hover {
  background: var(--sb-primary-soft);
  color: var(--sb-primary) !important;
  transform: translateX(4px);
}

.sidebar-menu li a:hover i {
  color: var(--sb-primary);
}

.sidebar-menu li.active>a {
  background: var(--sb-primary-soft);
  color: #8b96d2!important;
  box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.2);
}

.sidebar-menu li.active>a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 20px;
  background: linear-gradient(180deg, #6366f1, #8b5cf6);
  border-radius: 0 4px 4px 0;
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

/* .sidebar-menu li.active>a i {
  color: var(--sb-primary);
} */

.sidebar-menu li .has-dropdown {
  position: relative;
}

.sidebar-menu li .has-dropdown::after {
  content: '\f054';
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  font-size: 9px;
  color: var(--sb-muted);
  position: absolute;
  right: 14px;
  transition: all 0.25s ease;
  opacity: 0.5;
}

.sidebar-menu li .has-dropdown[aria-expanded="true"]::after {
  transform: rotate(90deg);
  color: var(--sb-primary);
  opacity: 1;
}

.sidebar-menu .dropdown-menu {
  background: rgba(255, 255, 255, 0.02);
  border: none;
  border-radius: 8px;
  padding: 2px 0 2px 6px;
  margin-top: 2px;
  display: none;
}

.sidebar-menu .dropdown-menu.show {
  display: block;
  animation: sbSlide 0.2s ease;
}

@keyframes sbSlide {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.sidebar-menu .dropdown-menu li a {
  padding: 7px 14px 7px 30px;
  font-size: 12px;
  font-weight: 400;
}

.sidebar-menu .dropdown-menu li a i {
  font-size: 12px;
  width: 16px;
}

.sidebar-menu .dropdown-menu li a:hover {
  transform: translateX(4px);
}

.sidebar-menu .dropdown-menu li.active>a {
  background: rgba(99, 102, 241, 0.06);
  color: #fff !important;
}

@media (max-width: 1024px) {
  body.sidebar-show .main-sidebar {
    transform: translateX(0) !important;
    box-shadow: 4px 0 60px rgba(0, 0, 0, 0.6);
  }
}
</style> -->