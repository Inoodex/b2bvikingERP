<script>
    document.addEventListener('alpine:init', () => {
        if (window.__wishlistPageComponentsRegistered) {
            return;
        }
        window.__wishlistPageComponentsRegistered = true;

        Alpine.data('wishlistItem', (item) => ({
            qty: Math.max(1, parseInt(item.minimum_order_qty) || 1),
            removing: false,
            lastRawQty: null,
            moqToast: null,
            product: item,
            variants: item.variants || [],
            selectedVariantIndex: '',

            get minimumOrderQty() {
                return Math.max(1, parseInt(this.product.minimum_order_qty) || 1);
            },

            get hasVariants() {
                return this.variants.length > 0;
            },

            get uniqueVariantOptions() {
                const seen = new Set();
                const unique = [];
                this.variants.forEach((v) => {
                    const key = `${v.id || 'default'}-${v.color || 'default'}-${v.size || 'default'}`;
                    if (!seen.has(key)) {
                        seen.add(key);
                        unique.push(v);
                    }
                });
                return unique;
            },

            get selectedVariant() {
                if (!this.hasVariants || this.selectedVariantIndex === '') {
                    return null;
                }
                return this.uniqueVariantOptions[parseInt(this.selectedVariantIndex, 10)] || null;
            },

            getSelectedPrice(priceType) {
                if (this.selectedVariant) {
                    if (priceType === 'outlet_price') {
                        return (this.selectedVariant.outlet_price || this.selectedVariant.price || this.product.outlet_price || this.product.price).toFixed(2);
                    }

                    if (priceType === 'price') {
                        return (this.selectedVariant.price || this.product.price).toFixed(2);
                    }
                } else {
                    if (priceType === 'outlet_price') {
                        return this.product.outlet_price.toFixed(2);
                    }

                    if (priceType === 'price') {
                        return this.product.price.toFixed(2);
                    }
                }

                return (0).toFixed(2);
            },

            normalizeQty() {
                const rawSource = this.lastRawQty !== null ? this.lastRawQty : this.qty;
                const rawQty = Math.max(1, parseInt(rawSource, 10) || 1);
                const moq = this.minimumOrderQty;
                let moqAdjusted = rawQty;

                if (rawQty < moq) {
                    moqAdjusted = moq;
                } else if (rawQty > moq) {
                    moqAdjusted = Math.ceil(rawQty / moq) * moq;
                }

                this.qty = moqAdjusted;
                if (moqAdjusted !== rawQty) {
                    this.moqToast = { moq, adjustedQty: moqAdjusted, rawQty };
                } else {
                    this.moqToast = null;
                    this.lastRawQty = null;
                }

                return this.qty;
            },

            async addToCart() {
                try {
                    const variant = this.selectedVariant;

                    if (this.hasVariants && !variant) {
                        this.notify('Please select a variant', 'error');
                        return;
                    }

                    const finalQty = this.normalizeQty();
                    await Alpine.store('cart').addItem(this.product, variant, finalQty);
                    this.removing = true;

                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        const notifier = bodyEl._x_dataStack[0];
                        const toast = this.moqToast;
                        if (toast) {
                            notifier.notify('Added to cart ✓', 'success');
                            setTimeout(() => {
                                notifier.notify(`Minimum order quantity is ${toast.moq}. Your cart has been updated to ${toast.adjustedQty} items.`, 'warning');
                            }, 250);
                        } else {
                            notifier.notify('Added to cart ✓', 'success');
                        }
                    }
                    this.moqToast = null;
                    this.lastRawQty = null;
                    this.moqToast = null;
                } catch (e) {
                    console.error('Add to cart error:', e);
                    this.notify(e?.message || 'Error adding to cart', 'error');
                }
            },

            async toggleWishlist(productId) {
                try {
                    await Alpine.store('wishlist').toggle(productId);
                } catch (e) {
                    console.error('Wishlist toggle error:', e);
                }
            },

            notify(message, type = 'error') {
                const bodyEl = document.querySelector('[x-data*="globalApp"]');
                if (bodyEl?._x_dataStack?.[0]) {
                    bodyEl._x_dataStack[0].notify(message, type);
                }
            },
        }));

        Alpine.data('wishlistPage', (initialCount = 0) => ({
            showClearConfirm: false,
            initialCount: parseInt(initialCount, 10) || 0,

            init() {
                const store = Alpine.store('wishlist');
                if (store.count === 0 && this.initialCount > 0) {
                    store.count = this.initialCount;
                }
            },

            get wishlistCount() {
                return parseInt(Alpine.store('wishlist').count, 10) || 0;
            },

            async clearAllWishlist() {
                const result = await Alpine.store('wishlist').clearAll();
                if (result && result.success) {
                    this.showClearConfirm = false;
                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        bodyEl._x_dataStack[0].notify('Wishlist cleared successfully ✓', 'success');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        bodyEl._x_dataStack[0].notify('Error clearing wishlist', 'error');
                    }
                }
            },
        }));
    });
</script>
