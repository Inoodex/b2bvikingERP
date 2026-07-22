<nav class="navbar navbar-secondary navbar-expand-lg">
    <div class="container">
        <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            <!-- Categories -->
            @can('Manage Categories')
            <li class="nav-item dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-list"></i><span>Categories</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link" href="{{ route('admin.category.index') }}">Category</a></li>
                    <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link" href="{{ route('admin.sub-category.index') }}">Sub Category</a></li>
                    <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link" href="{{ route('admin.child-category.index') }}">Child Category</a></li>
                </ul>
            </li>
            @endcan

            <!-- Products -->
            @canany(['Manage Products', 'View Product Stock'])
            <li class="nav-item dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-box"></i><span>Products</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link" href="{{ route('admin.products.index') }}">All Products</a></li>
                    @can('Manage Products')
                    <li class="{{ setActive(['admin.units.*']) }}"><a class="nav-link" href="{{ route('admin.units.index') }}">Units</a></li>
                    <li class="{{ setActive(['admin.colors.*']) }}"><a class="nav-link" href="{{ route('admin.colors.index') }}">Colors</a></li>
                    <li class="{{ setActive(['admin.sizes.*']) }}"><a class="nav-link" href="{{ route('admin.sizes.index') }}">Sizes</a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            <!-- Bookings & Purchases -->
            @canany(['Manage Order Place', 'Manage Order Receive'])
            @can('Manage Order Place')
            <li class="nav-item dropdown {{ setActive(['admin.bookings.*', 'admin.orders.*', 'admin.custom-product-requests.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-book"></i><span>Order Place</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.bookings.*']) }}"><a class="nav-link" href="{{ route('admin.bookings.index') }}">All Order Place</a></li>
                    <li class="{{ setActive(['admin.orders.*']) }}"><a class="nav-link" href="{{ route('admin.orders.index') }}">Frontend Orders</a></li>
                    @canany(['Manage Custom Product Requests', 'View Custom Product Requests'])
                    <li class="{{ setActive(['admin.custom-product-requests.*']) }}"><a class="nav-link" href="{{ route('admin.custom-product-requests.index') }}">Custom Requests</a></li>
                    @endcanany
                </ul>
            </li>
            @endcan

            @can('Manage Order Receive')
            <!-- Purchases -->
            <li class="nav-item dropdown {{ setActive(['admin.purchases.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-shopping-cart"></i><span>Order Receive</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.purchases.index']) }}"><a class="nav-link" href="{{ route('admin.purchases.index') }}">All Order Receive</a></li>
                    <li class="{{ setActive(['admin.purchases.create']) }}"><a class="nav-link" href="{{ route('admin.purchases.create') }}">Create New</a></li>
                </ul>
            </li>
            @endcan
            @endcanany

            <!-- Sales -->

             <!-- Product Requests -->
             {{-- @canany(['Manage Product Requests', 'Create Product Requests', 'View Product Requests'])
             <li class="nav-item dropdown {{ setActive(['admin.product-requests.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-box-open"></i><span>Outlet Request</span></a>
                <ul class="dropdown-menu">
                    @canany(['Manage Product Requests', 'View Product Requests'])
                    <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.product-requests.index') }}">All Outlet Request</a></li>
                    @endcanany
                    
                    @can('Create Product Requests')
                    <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.product-requests.create') }}">Create New</a></li>
                    @endcan
                </ul>
            </li>
            @endcanany --}}

            <!-- Reports -->
            @can('Manage Reports')
            <li class="nav-item dropdown {{ setActive(['admin.reports.*', 'admin.reports.orders']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-chart-line"></i><span>Reports</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.reports.index']) }}"><a class="nav-link" href="{{ route('admin.reports.index') }}">All Reports</a></li>
                    <li class="{{ setActive(['admin.reports.orders']) }}"><a class="nav-link" href="{{ route('admin.reports.orders') }}">Order & Issue Report</a></li>
                    <li class="{{ setActive(['admin.reports.stock']) }}"><a class="nav-link" href="{{ route('admin.reports.stock') }}">Stock Reports</a></li>
                    <li class="{{ setActive(['admin.reports.purchase']) }}"><a class="nav-link" href="{{ route('admin.reports.purchase') }}">Purchase History</a></li>
                    <li class="{{ setActive(['admin.reports.product-purchase-history']) }}"><a class="nav-link" href="{{ route('admin.reports.product-purchase-history') }}">Product Tracking</a></li>
                    <li class="{{ setActive(['admin.reports.low-stock']) }}"><a class="nav-link" href="{{ route('admin.reports.low-stock') }}">Low Stock Alert</a></li>
                    <li class="{{ setActive(['admin.reports.profit-loss']) }}"><a class="nav-link" href="{{ route('admin.reports.profit-loss') }}">Profit & Loss</a></li>
                    <li class="{{ setActive(['admin.reports.audit']) }}"><a class="nav-link" href="{{ route('admin.reports.audit') }}">Audit Report</a></li>
                </ul>
            </li>
            @endcan

            <!-- More (Brands, Vendors, Settings) -->
             <li class="nav-item dropdown {{ setActive(['admin.brand.*', 'admin.vendor.*', 'admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*', 'admin.taxes.*', 'admin.discounts.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-ellipsis-h"></i><span>More</span></a>
                <ul class="dropdown-menu">
                    @canany(['Manage Brands', 'Manage Vendors'])
                        <li class="dropdown-title">Brands & Vendors</li>
                        @can('Manage Brands')
                        <li class="{{ setActive(['admin.brand.*']) }}"><a class="nav-link" href="{{ route('admin.brand.index') }}">Brands</a></li>
                        @endcan
                        
                        @can('Manage Vendors')
                        <li class="{{ setActive(['admin.vendor.*']) }}"><a class="nav-link" href="{{ route('admin.vendor.index') }}">Vendors</a></li>
                        @endcan
                        
                        <li class="dropdown-divider"></li>
                    @endcanany

                    @can('Administration')
                        <li class="dropdown-title">Administration</li>
                        <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link" href="{{ route('admin.permission.index') }}">Permissions</a></li>
                        <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link" href="{{ route('admin.role.index') }}">Roles</a></li>
                        <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="{{ setActive(['admin.taxes.*']) }}"><a class="nav-link" href="{{ route('admin.taxes.index') }}">Tax / VAT</a></li>
                        <li class="{{ setActive(['admin.discounts.*']) }}"><a class="nav-link" href="{{ route('admin.discounts.index') }}">Discount</a></li>
                        <li class="{{ setActive(['admin.settings.*']) }}"><a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a></li>
                    @endcan
                </ul>
            </li>
        </ul>
    </div>
</nav>
