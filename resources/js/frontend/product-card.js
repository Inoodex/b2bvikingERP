document.addEventListener('alpine:init', () => {
    const alpine = window.Alpine;
    if (!alpine) {
        return;
    }

    if (window.__productCardItemRegistered) {
        return;
    }
    window.__productCardItemRegistered = true;

    alpine.data('productCardItem', (product, variants) => ({
        qty: Math.max(1, parseInt(product.minimum_order_qty, 10) || 1),
        lastRawQty: null,
        moqToast: null,
        selectedVariantIndex: '',
        product,
        variants,

        init() {
            if (!this.hasVariants || !this.inventoryVisible) {
                return;
            }

            const firstAvailableIndex = this.variants.findIndex((variant) => (parseInt(variant.stock, 10) || 0) > 0);
            this.selectedVariantIndex = firstAvailableIndex >= 0 ? String(firstAvailableIndex) : '';
        },

        get minimumOrderQty() {
            return Math.max(1, parseInt(this.product.minimum_order_qty, 10) || 1);
        },

        get inventoryVisible() {
            return !!this.product.inventory_visible;
        },

        get hasVariants() {
            return this.variants.length > 0;
        },

        get cartItems() {
            return Alpine.store('cart')?.items || [];
        },

        get inCartQty() {
            const pid = parseInt(this.product?.id, 10);
            if (!Number.isFinite(pid)) {
                return 0;
            }

            const items = this.cartItems;
            if (!items.length) {
                return 0;
            }

            return items.reduce((total, item) => {
                const itemPid = parseInt(item?.product_id ?? item?.product?.id ?? item?.productId ?? item?.id, 10);
                if (!Number.isFinite(itemPid) || itemPid !== pid) {
                    return total;
                }

                const qty = Math.max(0, parseInt(item?.quantity, 10) || 0);
                return total + qty;
            }, 0);
        },

        get variantInCartQty() {
            if (!this.hasVariants || !this.selectedVariant) {
                return 0;
            }

            const pid = parseInt(this.product?.id, 10);
            const selectedVariantId = parseInt(this.selectedVariant?.id, 10);
            if (!Number.isFinite(pid) || !Number.isFinite(selectedVariantId)) {
                return 0;
            }

            const items = this.cartItems;
            if (!items.length) {
                return 0;
            }

            return items.reduce((total, item) => {
                const itemPid = parseInt(item?.product_id ?? item?.product?.id ?? item?.productId ?? item?.id, 10);
                if (!Number.isFinite(itemPid) || itemPid !== pid) {
                    return total;
                }

                const rawVariant = item?.variant_id;
                const itemVid = rawVariant === null || rawVariant === undefined || rawVariant === ''
                    ? null
                    : parseInt(rawVariant, 10);

                if (itemVid !== selectedVariantId) {
                    return total;
                }

                const qty = Math.max(0, parseInt(item?.quantity, 10) || 0);
                return total + qty;
            }, 0);
        },

        get isInCart() {
            return this.inCartQty > 0;
        },

        canSelectVariant(index) {
            const variant = this.variants[index];
            if (!variant) {
                return false;
            }

            if (!this.inventoryVisible) {
                return true;
            }

            return (parseInt(variant.stock, 10) || 0) > 0;
        },

        selectVariant(index) {
            if (!this.canSelectVariant(index)) {
                return;
            }

            this.selectedVariantIndex = String(index);
            this.lastRawQty = null;
            this.normalizeQty();
        },

        get selectedVariant() {
            if (this.selectedVariantIndex === '') {
                return null;
            }

            const index = parseInt(this.selectedVariantIndex, 10);
            if (!this.canSelectVariant(index)) {
                return null;
            }

            return this.variants[index] ?? null;
        },

        variantLabel(v) {
            const base = v.name || [v.color, v.size].filter(Boolean).join(' ') || 'Variant';
            if (!this.inventoryVisible) {
                return base;
            }

            const stock = parseInt(v.stock, 10) || 0;
            return stock <= 0 ? `${base} - Out` : `${base} - ${stock}`;
        },

        get productNormalizedDiscountType() {
            const type = String(this.product.discount_type || '').toLowerCase().trim();
            return ['flat', 'percent'].includes(type) ? type : '';
        },

        get productDiscountValue() {
            return Math.max(0, parseFloat(this.product.discount) || 0);
        },

        get globalNormalizedDiscountType() {
            const type = String(this.product.global_discount_type || '').toLowerCase().trim();
            return ['flat', 'percent'].includes(type) ? type : '';
        },

        get globalDiscountValue() {
            return Math.max(0, parseFloat(this.product.global_discount) || 0);
        },

        get normalizedDiscountType() {
            if (this.productNormalizedDiscountType !== '' && this.productDiscountValue > 0) {
                return this.productNormalizedDiscountType;
            }

            if (this.globalNormalizedDiscountType !== '' && this.globalDiscountValue > 0) {
                return this.globalNormalizedDiscountType;
            }

            return '';
        },

        get discountValue() {
            if (this.productNormalizedDiscountType !== '' && this.productDiscountValue > 0) {
                return this.productDiscountValue;
            }

            if (this.globalNormalizedDiscountType !== '' && this.globalDiscountValue > 0) {
                return this.globalDiscountValue;
            }

            return 0;
        },

        get hasDiscount() {
            return this.normalizedDiscountType !== '' && this.discountValue > 0;
        },

        get hasProductDiscount() {
            return this.productNormalizedDiscountType !== '' && this.productDiscountValue > 0;
        },

        get discountBadgeText() {
            if (!this.hasProductDiscount) {
                return '';
            }

            if (this.productNormalizedDiscountType === 'percent') {
                return `${this.productDiscountValue}% OFF`;
            }

            return `FLAT ${this.productDiscountValue.toFixed(2)} OFF`;
        },

        applyDiscount(price) {
            const numericPrice = Math.max(0, parseFloat(price) || 0);
            // Product card price should only reflect product-specific discounts.
            if (!this.hasProductDiscount) {
                return numericPrice;
            }

            if (this.productNormalizedDiscountType === 'percent') {
                const percent = Math.min(100, this.productDiscountValue);
                return Math.max(0, numericPrice - ((numericPrice * percent) / 100));
            }

            return Math.max(0, numericPrice - this.productDiscountValue);
        },

        get outletBasePrice() {
            return this.selectedVariant
                ? (this.selectedVariant.outlet_price || this.selectedVariant.price || this.product.outlet_price || this.product.price || 0)
                : (this.product.outlet_price || this.product.price || 0);
        },

        get retailBasePrice() {
            return this.selectedVariant
                ? (this.selectedVariant.price || this.product.price || 0)
                : (this.product.price || 0);
        },

        get outletDisplayPrice() {
            return Number(this.applyDiscount(this.outletBasePrice)).toFixed(2);
        },

        get retailDisplayPrice() {
            return Number(this.retailBasePrice || 0).toFixed(2);
        },

        get outletOriginalDisplayPrice() {
            return Number(this.outletBasePrice || 0).toFixed(2);
        },

        get retailOriginalDisplayPrice() {
            return Number(this.retailBasePrice || 0).toFixed(2);
        },

        get showOutletOriginalPrice() {
            return this.hasProductDiscount && (this.outletBasePrice > this.applyDiscount(this.outletBasePrice));
        },

        get showRetailOriginalPrice() {
            return false;
        },

        get currentStock() {
            if (this.hasVariants) {
                return this.selectedVariant ? Math.max(0, parseInt(this.selectedVariant.stock, 10) || 0) : 0;
            }

            return Math.max(0, parseInt(this.product.stock, 10) || 0);
        },

        get maxAddableQty() {
            if (!this.inventoryVisible) {
                return Number.MAX_SAFE_INTEGER;
            }

            const stock = this.currentStock;
            const moq = this.minimumOrderQty;
            if (stock < moq) {
                return 0;
            }

            return Math.floor(stock / moq) * moq;
        },

        get canAdd() {
            if (this.hasVariants && !this.selectedVariant) {
                return false;
            }

            if (!this.inventoryVisible) {
                return true;
            }

            const requestedQty = this.normalizedQty;
            return this.currentStock > 0 && this.maxAddableQty > 0 && requestedQty <= this.currentStock;
        },

        get cannotAddMessage() {
            if (this.hasVariants && !this.selectedVariant) {
                return 'Please select a variant';
            }

            if (!this.inventoryVisible) {
                return 'Cannot add this item right now';
            }

            if (this.currentStock <= 0) {
                return 'Out of stock';
            }

            if (this.maxAddableQty === 0) {
                return `Minimum order ${this.minimumOrderQty}, but stock is ${this.currentStock}`;
            }

            return `Available stock: ${this.currentStock}`;
        },

        get stockPillText() {
            if (!this.inventoryVisible) {
                return 'Available';
            }

            if (this.hasVariants && !this.selectedVariant) {
                return 'Select variant';
            }

            if (this.currentStock <= 0) {
                return 'Out of stock';
            }

            if (this.currentStock <= 5) {
                return `Low stock: ${this.currentStock}`;
            }

            return `Available: ${this.currentStock}`;
        },

        get stockPillClass() {
            if (!this.inventoryVisible) {
                return 'bg-slate-100 text-slate-500 border-slate-200';
            }

            if (this.hasVariants && !this.selectedVariant) {
                return 'bg-slate-100 text-slate-500 border-slate-200';
            }

            if (this.currentStock <= 0) {
                return 'bg-rose-50 text-rose-600 border-rose-200';
            }

            if (this.currentStock <= 5) {
                return 'bg-amber-50 text-amber-700 border-amber-200';
            }

            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        },

        get normalizedQty() {
            const inputQty = Math.max(1, parseInt(this.qty, 10) || 1);
            const moq = this.minimumOrderQty;

            if (inputQty < moq) {
                return moq;
            }

            if (inputQty > moq) {
                return Math.ceil(inputQty / moq) * moq;
            }

            return moq;
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

            let adjustedQty = moqAdjusted;

            if (this.inventoryVisible && this.maxAddableQty > 0 && adjustedQty > this.maxAddableQty) {
                adjustedQty = this.maxAddableQty;
            }

            this.qty = adjustedQty;
            if (moqAdjusted !== rawQty && adjustedQty === moqAdjusted) {
                this.moqToast = { moq, adjustedQty, rawQty };
            } else {
                this.moqToast = null;
                this.lastRawQty = null;
            }

            return this.qty;
        },

        async addToCart(prod, variant, qty) {
            try {
                const finalQty = this.normalizeQty();
                await Alpine.store('cart').addItem(prod, variant, finalQty);

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
            } catch (e) {
                console.error('Add to cart error:', e);
                this.notify(e?.message || 'Error adding to cart', 'error');
            }
        },

        async toggleWishlist(productId) {
            try {
                await Alpine.store('wishlist').toggle(productId);
                const message = this.isWishlisted(productId) ? 'Added to wishlist' : 'Removed from wishlist';
                this.notify(message, 'success');
            } catch (e) {
                console.error('Wishlist toggle error:', e);
                this.notify('Error updating wishlist', 'error');
            }
        },

        isWishlisted(productId) {
            return Alpine.store('wishlist').ids.includes(productId);
        },

        notify(message, type = 'error') {
            const bodyEl = document.querySelector('[x-data*="globalApp"]');
            if (bodyEl?._x_dataStack?.[0]) {
                bodyEl._x_dataStack[0].notify(message, type);
            }
        },
    }));
});
