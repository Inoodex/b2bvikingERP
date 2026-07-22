{{-- =====================================================
     ALPINE.JS — GLOBAL CART STORE & APP DATA + WISHLIST
     ===================================================== --}}
@php
    $frontendFlashToasts = [];
    $initialWishlistCount = 0;
    $initialCartCount = 0;

    if (session('success')) {
        $frontendFlashToasts[] = ['type' => 'success', 'message' => session('success')];
    }
    if (session('error')) {
        $frontendFlashToasts[] = ['type' => 'error', 'message' => session('error')];
    }
    if (session('warning')) {
        $frontendFlashToasts[] = ['type' => 'warning', 'message' => session('warning')];
    }
    if (session('message')) {
        $frontendFlashToasts[] = ['type' => 'success', 'message' => session('message')];
    }

    if ($errors->any()) {
        foreach ($errors->all() as $errorMessage) {
            $frontendFlashToasts[] = ['type' => 'error', 'message' => $errorMessage];
        }
    }

    if (auth()->check()) {
        $initialWishlistCount = (int) \App\Models\Wishlist::query()
            ->where('user_id', auth()->id())
            ->count();

        $initialCartCount = (int) \App\Models\Cart::query()
            ->where('user_id', auth()->id())
            ->sum('quantity');
    }
@endphp
<script>
    // User role (PHP-rendered, used for role-based pricing in cart)
    window.APP_USER_ROLE = '{{ auth()->user() ? (auth()->user()->hasRole('Outlet User') ? 'Outlet User' : (auth()->user()->hasRole('User') ? 'User' : 'Other')) : 'Guest' }}';
    window.APP_AUTHENTICATED = {{ auth()->check() ? 'true' : 'false' }};
    window.APP_AUTH_USER_ID = {{ auth()->check() ? auth()->id() : 'null' }};
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.APP_INITIAL_WISHLIST_COUNT = {{ $initialWishlistCount }};
    window.APP_INITIAL_CART_COUNT = {{ $initialCartCount }};
    window.FRONTEND_FLASH_TOASTS = @json($frontendFlashToasts);

    document.addEventListener('alpine:init', () => {
        const parseJsonArray = (value) => {
            try {
                const parsed = JSON.parse(value || '[]');
                return Array.isArray(parsed) ? parsed : [];
            } catch (_) {
                return [];
            }
        };
        const authCartCacheKey = `cart_items_auth_${window.APP_AUTH_USER_ID || 0}`;

        // ─── WISHLIST STORE ────────────────────────────────────
        Alpine.store('wishlist', {
            ids:   [],  // array of product_id's in wishlist
            count: parseInt(window.APP_INITIAL_WISHLIST_COUNT || 0, 10) || 0,
            hydrated: false,
            hydrating: false,

            isWishlisted(productId) {
                return this.ids.includes(parseInt(productId));
            },

            async ensureHydrated(force = false) {
                if (!window.APP_AUTHENTICATED) {
                    this.hydrated = true;
                    this.ids = [];
                    this.count = 0;
                    return;
                }

                if (!force && (this.hydrated || this.hydrating)) {
                    return;
                }

                this.hydrating = true;
                try {
                    const res  = await fetch('/wishlist/ids', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.ids   = data.ids || [];
                    this.count = data.count || 0;
                } catch (e) { /* silent */ }
                finally {
                    this.hydrated = true;
                    this.hydrating = false;
                }
            },

            async toggle(productId) {
                if (!window.APP_AUTHENTICATED) {
                    window.location.href = '{{ route("login") }}';
                    return null;
                }
                try {
                    const res  = await fetch('/wishlist/toggle', {
                        method:  'POST',
                        headers: {
                            'Content-Type':  'application/json',
                            'Accept':        'application/json',
                            'X-CSRF-TOKEN':  window.CSRF_TOKEN,
                        },
                        body: JSON.stringify({ product_id: productId }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (data.wishlisted) {
                            if (!this.ids.includes(parseInt(productId))) {
                                this.ids = [...this.ids, parseInt(productId)];
                            }
                        } else {
                            this.ids = this.ids.filter(id => id !== parseInt(productId));
                        }
                        this.count = data.count;
                    }
                    return data;
                } catch (e) { return null; }
            },

            async clearAll() {
                if (!window.APP_AUTHENTICATED) {
                    window.location.href = '{{ route("login") }}';
                    return null;
                }
                try {
                    const res  = await fetch('/wishlist/clear', {
                        method:  'POST',
                        headers: {
                            'Content-Type':  'application/json',
                            'Accept':        'application/json',
                            'X-CSRF-TOKEN':  window.CSRF_TOKEN,
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.ids = [];
                        this.count = 0;
                    }
                    return data;
                } catch (e) { return null; }
            },
        });

        // ─── CART STORE ───────────────────────────────────────
        Alpine.store('cart', {
            items: window.APP_AUTHENTICATED
                ? [...parseJsonArray(localStorage.getItem(authCartCacheKey))]
                : [...parseJsonArray(localStorage.getItem('cart_items'))],
            serverCount: parseInt(window.APP_INITIAL_CART_COUNT || 0, 10) || 0,
            hydrated: !window.APP_AUTHENTICATED,
            hydrating: false,
            serverSnapshot: {},
            qtySyncTimers: {},
            qtySyncVersions: {},
            pendingQtyValues: {},

            get count() {
                if (window.APP_AUTHENTICATED && !this.hydrated && this.items.length === 0) {
                    return this.serverCount;
                }

                return this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
            },

            get total() {
                return this.items.reduce((total, item) => total + (parseFloat(item.price) * (parseInt(item.quantity) || 0)), 0);
            },

            _rebuildServerSnapshot() {
                const snapshot = {};
                this.items.forEach((item) => {
                    const id = parseInt(item.id, 10);
                    if (!Number.isNaN(id)) {
                        snapshot[id] = Math.max(0, parseInt(item.quantity, 10) || 0);
                    }
                });
                this.serverSnapshot = snapshot;
            },

            _persistCache() {
                if (window.APP_AUTHENTICATED) {
                    localStorage.setItem(authCartCacheKey, JSON.stringify(this.items));
                } else {
                    this.save();
                }
            },

            _updateLineTotalsFromQuantity(item, quantity) {
                const safeQty = Math.max(1, parseInt(quantity, 10) || 1);
                const displayUnit = parseFloat(item.display_price ?? item.price) || 0;
                const originalUnit = parseFloat(item.original_price ?? item.price) || 0;
                item.quantity = safeQty;
                item.line_total_after_discount = Number((displayUnit * safeQty).toFixed(2));
                item.line_total = Number((originalUnit * safeQty).toFixed(2));
            },

            _applyLocalQuantity(cartId, quantity) {
                const id = parseInt(cartId, 10);
                const item = this.items.find((entry) => parseInt(entry.id, 10) === id);
                if (!item) {
                    return false;
                }

                this._updateLineTotalsFromQuantity(item, quantity);
                this.items = [...this.items];
                this.serverCount = this.items.reduce((total, entry) => total + (parseInt(entry.quantity, 10) || 0), 0);
                this._persistCache();
                return true;
            },

            _emitCartSyncError(message) {
                window.dispatchEvent(new CustomEvent('cart-sync-error', {
                    detail: {
                        message: message || 'Failed to sync cart quantity.',
                    },
                }));
            },

            async _syncQuantityToDB(cartId, version) {
                const id = parseInt(cartId, 10);
                const latestVersion = parseInt(this.qtySyncVersions[id] || 0, 10);
                if (latestVersion !== version) {
                    return;
                }

                const qty = Math.max(1, parseInt(this.pendingQtyValues[id], 10) || 1);
                try {
                    const res = await fetch('/frontend/cart/update-qty', {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': window.CSRF_TOKEN,
                        },
                        body: JSON.stringify({ cart_id: id, quantity: qty }),
                    });

                    let data = {};
                    try {
                        data = await res.json();
                    } catch (_) {}

                    if (!res.ok || data.success === false) {
                        throw new Error(data.message || 'Failed to update quantity.');
                    }

                    if (parseInt(this.qtySyncVersions[id] || 0, 10) !== version) {
                        return;
                    }

                    const appliedQty = Math.max(1, parseInt(data.applied_quantity, 10) || qty);
                    this.serverSnapshot[id] = appliedQty;
                    this._applyLocalQuantity(id, appliedQty);
                    delete this.pendingQtyValues[id];
                } catch (e) {
                    if (parseInt(this.qtySyncVersions[id] || 0, 10) !== version) {
                        return;
                    }

                    const fallbackQty = this.serverSnapshot[id];
                    if (fallbackQty && fallbackQty > 0) {
                        this._applyLocalQuantity(id, fallbackQty);
                    } else {
                        await this.loadFromDB();
                    }

                    this._emitCartSyncError(e?.message || 'Failed to update quantity.');
                }
            },

            async ensureHydrated(force = false) {
                if (!window.APP_AUTHENTICATED) {
                    this.hydrated = true;
                    return;
                }

                if (!force && (this.hydrated || this.hydrating)) {
                    return;
                }

                this.hydrating = true;
                try {
                    await this.loadFromDB();
                } finally {
                    this.hydrated = true;
                    this.hydrating = false;
                }
            },

            async loadFromDB() {
                try {
                    const res  = await fetch('/frontend/cart/items', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    // Force reactivity by reassigning the array
                    this.items = [...(data.items || [])];
                    this._persistCache();
                    this.serverCount = this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
                    this._rebuildServerSnapshot();
                } catch (e) {
                    console.error('Cart load error:', e);
                    // Fallback to cached cart if request fails
                    this.items = window.APP_AUTHENTICATED
                        ? [...parseJsonArray(localStorage.getItem(authCartCacheKey))]
                        : [...parseJsonArray(localStorage.getItem('cart_items'))];
                    this.serverCount = this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
                    this._rebuildServerSnapshot();
                }
            },

            async addItem(product, variant = null, quantity = 1) {
                const q    = parseInt(quantity) || 1;
                const role = window.APP_USER_ROLE || 'Guest';
                let price  = 0;

                if (role === 'Outlet User' || role === 'User') {
                    price = variant
                        ? (variant.outlet_price || variant.price)
                        : (product.outlet_price || product.price);
                } else {
                    price = variant ? variant.price : product.price;
                }

                if (window.APP_AUTHENTICATED) {
                    // Persist to DB
                    try {
                        const res = await fetch('/frontend/cart/add', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            },
                            body: JSON.stringify({
                                product_id: product.id,
                                variant_id: variant ? variant.id : null,
                                quantity: q
                            }),
                        });

                        let data = {};
                        try {
                            data = await res.json();
                        } catch (_) {}

                        if (!res.ok || data.success === false) {
                            throw new Error(data.message || 'Add to cart failed.');
                        }

                        await this.loadFromDB();
                        if (data.removed_from_wishlist) {
                            const wishlist = Alpine.store('wishlist');
                            wishlist.ids = wishlist.ids.filter(id => id !== parseInt(product.id));
                            wishlist.count = data.wishlist_count ?? wishlist.ids.length;
                        }
                    } catch (e) { 
                        console.error('Add item error:', e);
                        throw e;
                    }
                } else {
                    await this.ensureHydrated();
                    // Guest: use localStorage only
                    const existingItem = this.items.find(item =>
                        item.product_id === product.id &&
                        (!variant || item.variant_id === variant.id)
                    );

                    if (existingItem) {
                        existingItem.quantity += q;
                        existingItem.price = price;
                    } else {
                        this.items.push({
                            id:            Date.now() + Math.random(),
                            product_id:    product.id,
                            variant_id:    variant ? variant.id : null,
                            name:          product.name,
                            price:         price,
                            image:         product.thumb_image,
                            category:      product.category?.name || product.category || 'General',
                            variant_label: variant
                                ? (variant.name || `${variant.color || ''} ${variant.size || ''}`.trim())
                                : null,
                            quantity: q,
                        });
                    }
                    this.save();
                }
                this.items = [...this.items]; // force reactivity
            },

            async removeItem(cartId) {
                const id = parseInt(cartId, 10);
                if (!Number.isNaN(id) && this.qtySyncTimers[id]) {
                    clearTimeout(this.qtySyncTimers[id]);
                    delete this.qtySyncTimers[id];
                }
                delete this.pendingQtyValues[id];
                delete this.qtySyncVersions[id];
                delete this.serverSnapshot[id];

                if (window.APP_AUTHENTICATED) {
                    try {
                        const res = await fetch('/frontend/cart/remove', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            },
                            body: JSON.stringify({ cart_id: cartId }),
                        });
                        if (res.ok) {
                            await this.loadFromDB();
                        } else {
                            console.error('Remove failed:', res.status);
                        }
                    } catch (e) { 
                        console.error('Remove item error:', e);
                    }
                } else {
                    await this.ensureHydrated();
                    this.items = this.items.filter(item => item.id !== cartId);
                    this._persistCache();
                }
                this.items = [...this.items];
            },

            async updateQuantity(cartId, qty) {
                const q = Math.max(1, parseInt(qty) || 1);
                if (window.APP_AUTHENTICATED) {
                    const id = parseInt(cartId, 10);
                    if (Number.isNaN(id)) {
                        return;
                    }

                    const item = this.items.find((entry) => parseInt(entry.id, 10) === id);
                    if (!item) {
                        await this.loadFromDB();
                        return;
                    }

                    if (typeof this.serverSnapshot[id] !== 'number') {
                        this.serverSnapshot[id] = Math.max(1, parseInt(item.quantity, 10) || 1);
                    }

                    // Optimistic update: UI changes instantly, server sync runs in background.
                    this._applyLocalQuantity(id, q);

                    const nextVersion = (parseInt(this.qtySyncVersions[id] || 0, 10) + 1);
                    this.qtySyncVersions[id] = nextVersion;
                    this.pendingQtyValues[id] = q;

                    if (this.qtySyncTimers[id]) {
                        clearTimeout(this.qtySyncTimers[id]);
                    }

                    this.qtySyncTimers[id] = setTimeout(() => {
                        delete this.qtySyncTimers[id];
                        this._syncQuantityToDB(id, nextVersion);
                    }, 280);
                } else {
                    await this.ensureHydrated();
                    const item = this.items.find(i => i.id === cartId);
                    if (item) {
                        item.quantity = q;
                        this._persistCache();
                        this.items = [...this.items];
                    }
                }
            },

            // Clear cart (called on logout)
            async clearLocal() {
                this.items = [];
                this.serverCount = 0;
                this.hydrated = true;
                this.serverSnapshot = {};
                this.pendingQtyValues = {};
                this.qtySyncVersions = {};
                Object.values(this.qtySyncTimers).forEach((timerId) => clearTimeout(timerId));
                this.qtySyncTimers = {};
                localStorage.removeItem('cart_items');
                localStorage.removeItem(authCartCacheKey);
            },

            save() {
                localStorage.setItem('cart_items', JSON.stringify(this.items));
            },
        });

        // ─── GLOBAL APP DATA ──────────────────────────────────
        Alpine.data('globalApp', () => ({
            isCartOpen:     false,
            isWishlistOpen: false,
            notifications:  [],
            userRole:       window.APP_USER_ROLE,
            searchQuery:    '',

            init() {
                this.$watch('isCartOpen', (isOpen) => {
                    if (isOpen) {
                        Alpine.store('cart').ensureHydrated();
                    }
                });

                if (window.APP_AUTHENTICATED) {
                    // Warm cart data so first drawer open can render instantly.
                    Alpine.store('cart').ensureHydrated();
                }

                window.addEventListener('cart-sync-error', (event) => {
                    const message = event?.detail?.message || 'Failed to sync cart quantity.';
                    this.notify(message, 'error');
                });

                if (Array.isArray(window.FRONTEND_FLASH_TOASTS) && window.FRONTEND_FLASH_TOASTS.length > 0) {
                    window.FRONTEND_FLASH_TOASTS.forEach((toast, idx) => {
                        setTimeout(() => {
                            this.notify(toast.message, toast.type || 'success');
                        }, 120 * (idx + 1));
                    });
                    window.FRONTEND_FLASH_TOASTS = [];
                }
            },

            // Cart getters
            get cartCount() { return Alpine.store('cart').count; },
            get cartItems() { return Alpine.store('cart').items; },
            get cartTotal() { return Alpine.store('cart').total; },
            get cartHydrating() { return Alpine.store('cart').hydrating; },
            get cartDisplayTotal() {
                return this.cartItems.reduce((total, item) => {
                    const lineDiscounted = parseFloat(item.line_total_after_discount);
                    if (!Number.isNaN(lineDiscounted)) {
                        return total + lineDiscounted;
                    }

                    const unit = parseFloat(item.display_price ?? item.price) || 0;
                    const qty = parseInt(item.quantity) || 0;
                    return total + (unit * qty);
                }, 0);
            },
            get cartOriginalTotal() {
                return this.cartItems.reduce((total, item) => {
                    const lineOriginal = parseFloat(item.line_total);
                    if (!Number.isNaN(lineOriginal)) {
                        return total + lineOriginal;
                    }

                    const unit = parseFloat(item.original_price ?? item.price) || 0;
                    const qty = parseInt(item.quantity) || 0;
                    return total + (unit * qty);
                }, 0);
            },

            // Wishlist getters
            get wishlistCount() { return Alpine.store('wishlist').count; },
            isWishlisted(id)   { return Alpine.store('wishlist').isWishlisted(id); },

            // Notification helpers
            notify(message, type = 'success') {
                const id = Date.now();
                this.notifications.push({ id, message, type, show: true });
                setTimeout(() => this.hideNotification(id), 4000);
            },

            hideNotification(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].show = false;
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 300);
                }
            },

            // Cart actions
            async addToCart(product, variant = null, quantity = 1) {
                try {
                    await Alpine.store('cart').addItem(product, variant, quantity);
                    this.notify('Added to cart ✓');
                } catch (e) {
                    this.notify(e?.message || 'Add to cart failed.', 'error');
                }
            },

            async removeFromCart(cartId) {
                await Alpine.store('cart').removeItem(cartId);
                this.notify('Removed from cart', 'warning');
            },

            async updateCartQty(cartId, qty) {
                try {
                    let val = parseInt(qty);

                    if (Number.isNaN(val)) {
                        this.notify('Please enter a valid quantity.', 'error');
                        await Alpine.store('cart').loadFromDB();
                        return;
                    }

                    const item = this.cartItems.find(i => parseInt(i.id) === parseInt(cartId));
                    if (!item) {
                        await Alpine.store('cart').loadFromDB();
                        return;
                    }

                    const moq = Math.max(1, parseInt(item.minimum_order_qty) || 1);
                    const stock = item && item.available_stock !== undefined && item.available_stock !== null
                        ? Math.max(0, parseInt(item.available_stock) || 0)
                        : null;

                    if (val > 0) {
                        if (val < moq) {
                            val = 0;
                        } else if (val > moq) {
                            val = Math.ceil(val / moq) * moq;
                        }
                    }

                    if (stock !== null) {
                        const maxAddable = Math.floor(stock / moq) * moq;
                        if (maxAddable <= 0) {
                            await this.removeFromCart(cartId);
                            return;
                        }
                        if (val > maxAddable) {
                            this.notify(`Available stock: ${maxAddable}`, 'error');
                            val = maxAddable;
                        }
                    }

                    if (val < 1) {
                        await this.removeFromCart(cartId);
                    } else {
                        await Alpine.store('cart').updateQuantity(cartId, val);
                    }
                } catch (e) {
                    this.notify(e?.message || 'Failed to update quantity.', 'error');
                }
            },

            // Wishlist actions
            async toggleWishlist(productId) {
                const result = await Alpine.store('wishlist').toggle(productId);
                if (result) {
                    this.notify(result.message, result.wishlisted ? 'success' : 'warning');
                }
            },

            // Logout: clear local stores before submitting
            handleLogout(formEl) {
                Alpine.store('cart').clearLocal();
                Alpine.store('wishlist').ids   = [];
                Alpine.store('wishlist').count = 0;
                formEl.submit();
            },
        }));

    });
</script>
