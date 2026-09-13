{{-- ============================================
     OFFCANVAS CART DRAWER (Procurement & Sales Quotation)
     Modularized Component
     ============================================ --}}

<style>
/* ---- Cart Drawer CSS ---- */
.swal2-container {
  z-index: 10000000 !important;
}

.modal-backdrop {
  z-index: 10500 !important;
}
.modal {
  z-index: 10550 !important;
}
.select2-container--open {
  z-index: 10600 !important;
}

.cart-drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(10, 14, 26, 0.6);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 99998;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}
.cart-drawer-backdrop.active {
  opacity: 1;
  visibility: visible;
}

.cart-offcanvas-drawer {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: min(440px, 94vw);
  background: #ffffff;
  box-shadow: -10px 0 40px rgba(0, 0, 0, 0.25);
  z-index: 99999;
  display: flex;
  flex-direction: column;
  transform: translateX(100%);
  transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
}
.cart-offcanvas-drawer.open {
  transform: translateX(0);
}

.cart-drawer-header {
  padding: 18px 20px;
  background: #0f172a;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.cart-drawer-icon-box {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}
.cart-drawer-icon-box.booking {
  background: rgba(234, 179, 8, 0.2);
  color: #facc15;
  border: 1px solid rgba(234, 179, 8, 0.3);
}
.cart-drawer-icon-box.request {
  background: rgba(37, 99, 235, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(37, 99, 235, 0.3);
}
.cart-drawer-title {
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -0.2px;
  color: #ffffff;
}
.cart-drawer-close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #fff;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.cart-drawer-close-btn:hover {
  background: rgba(255, 255, 255, 0.22);
  transform: scale(1.05);
}

.cart-drawer-stats {
  padding: 10px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.cart-drawer-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
}

.cart-item-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  margin-bottom: 10px;
  transition: all 0.2s ease;
}
.cart-item-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
.cart-item-img {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: contain;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  flex-shrink: 0;
  padding: 3px;
}
.cart-item-info {
  flex: 1;
  min-width: 0;
}
.cart-item-name {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cart-item-meta {
  font-size: 11px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 8px;
}
.cart-item-remove-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: #fee2e2;
  color: #ef4444;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}
.cart-item-remove-btn:hover {
  background: #ef4444;
  color: #ffffff;
}

.cart-item-qty-wrapper {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
}
.cart-item-qty-control {
  display: inline-flex;
  align-items: center;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  overflow: hidden;
  background: #f8fafc;
}
.cart-qty-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: bold;
  color: #475569;
  cursor: pointer;
  transition: all 0.15s ease;
  padding: 0;
}
.cart-qty-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}
.cart-qty-input {
  width: 38px;
  height: 24px;
  border: none;
  border-left: 1px solid #cbd5e1;
  border-right: 1px solid #cbd5e1;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  color: #0f172a;
  background: #ffffff;
  padding: 0;
}
.cart-qty-input::-webkit-outer-spin-button,
.cart-qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.cart-drawer-empty {
  text-align: center;
  padding: 50px 20px;
}
.cart-drawer-empty .empty-icon-circle {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #94a3b8;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.cart-drawer-footer {
  padding: 16px 20px;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.04);
}
</style>

{{-- HTML Backdrop & Drawer --}}
<div class="cart-drawer-backdrop" id="cartDrawerBackdrop" onclick="closeCartDrawer()"></div>

<div class="cart-offcanvas-drawer" id="cartOffcanvasDrawer">
  {{-- Header --}}
  <div class="cart-drawer-header">
    <div class="d-flex align-items-center" style="gap: 10px;">
      <div id="cart-drawer-icon-box" class="cart-drawer-icon-box booking">
        <i id="cart-drawer-header-icon" class="fas fa-shopping-basket"></i>
      </div>
      <div>
        <h5 class="cart-drawer-title mb-0" id="cart-drawer-title">Procurement Cart</h5>
        <small class="text-white-50" id="cart-drawer-subtitle" style="font-size: 11px;">Review items before creating RFQ</small>
      </div>
    </div>
    <button type="button" class="cart-drawer-close-btn" onclick="closeCartDrawer()" aria-label="Close">
      <i class="fas fa-times"></i>
    </button>
  </div>

  {{-- Quick stats bar --}}
  <div class="cart-drawer-stats d-flex justify-content-between align-items-center">
    <span class="badge badge-pill badge-primary font-weight-bold" id="cart-drawer-item-count-badge" style="font-size: 11px;">0 items</span>
    <button type="button" class="btn btn-link text-danger p-0" id="cart-drawer-clear-btn" onclick="clearActiveDrawerCart()" style="font-size: 12px; font-weight: 600; text-decoration: none;">
      <i class="fas fa-trash-alt mr-1"></i> Clear Cart
    </button>
  </div>

  {{-- Body (Items List) --}}
  <div class="cart-drawer-body" id="cart-drawer-items-list">
    <div class="cart-drawer-empty">
      <div class="empty-icon-circle">
        <i class="fas fa-shopping-basket fa-2x"></i>
      </div>
      <h6 class="font-weight-bold text-dark">Your Cart is Empty</h6>
      <p class="text-muted small">Select products from the catalog to add them here.</p>
    </div>
  </div>

  {{-- Footer --}}
  <div class="cart-drawer-footer">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="text-muted font-weight-bold" style="font-size: 13px;">Selected Products</span>
      <span class="font-weight-bold text-dark" id="cart-drawer-footer-count" style="font-size: 14px;">0 items</span>
    </div>
    <a href="{{ route('admin.rfqs.create') }}?source=basket" id="cart-drawer-proceed-btn" class="btn btn-warning btn-block font-weight-bold py-2 shadow-sm d-flex align-items-center justify-content-center" style="font-size: 13.5px; border-radius: 8px; gap: 8px; color: #1a1408;">
      <span id="cart-drawer-btn-text">Proceed to Create RFQ</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>
</div>

{{-- JavaScript Controller --}}
<script>
window.cartStore = {
  booking: { ids: [], count: 0, items: [] },
  request: { ids: [], count: 0, items: [] }
};
let activeDrawerType = 'booking';
let qtyUpdateTimers = {};

window.updateGlobalCartBadges = function(bookingCount, requestCount) {
  if (typeof bookingCount !== 'undefined') {
    if (window.cartStore && window.cartStore.booking) window.cartStore.booking.count = bookingCount;
    var $bBadge = document.getElementById('topbar-booking-cart-badge');
    if ($bBadge) {
      $bBadge.textContent = bookingCount;
      $bBadge.style.display = (bookingCount > 0) ? 'inline-flex' : 'none';
    }
  }
  if (typeof requestCount !== 'undefined') {
    if (window.cartStore && window.cartStore.request) window.cartStore.request.count = requestCount;
    var $rBadge = document.getElementById('topbar-request-cart-badge');
    if ($rBadge) {
      $rBadge.textContent = requestCount;
      $rBadge.style.display = (requestCount > 0) ? 'inline-flex' : 'none';
    }
  }
};

window.openCartDrawer = function(type) {
  activeDrawerType = type;
  const isBooking = (type === 'booking');

  // Update Drawer UI Header & Buttons based on Type
  const $title = $('#cart-drawer-title');
  const $subtitle = $('#cart-drawer-subtitle');
  const $iconBox = $('#cart-drawer-icon-box');
  const $headerIcon = $('#cart-drawer-header-icon');
  const $proceedBtn = $('#cart-drawer-proceed-btn');
  const $btnText = $('#cart-drawer-btn-text');

  if (isBooking) {
    $title.text('Procurement Cart');
    $subtitle.text('Review items before generating RFQ / Purchase');
    $iconBox.removeClass('request').addClass('booking');
    $headerIcon.attr('class', 'fas fa-shopping-basket');
    $proceedBtn.removeClass('btn-danger btn-primary').addClass('btn-warning').css('color', '#1a1408');
    $proceedBtn.attr('href', "{{ route('admin.rfqs.create') }}?source=basket");
    $btnText.text('Proceed to Create RFQ');
  } else {
    $title.text('Sales Quotation Cart');
    $subtitle.text('Review items before generating Customer Quotation (SQ)');
    $iconBox.removeClass('booking').addClass('request');
    $headerIcon.attr('class', 'fas fa-file-invoice-dollar');
    $proceedBtn.removeClass('btn-warning btn-danger').addClass('btn-primary').css('color', '#ffffff');
    $proceedBtn.attr('href', "{{ route('admin.sales-quotations.create') }}?source=cart");
    $btnText.text('Proceed to Create Sales Quotation');
  }

  // Render items INSTANTLY from in-memory cartStore (0ms delay)
  renderDrawerItems();

  // Open Drawer & Backdrop
  $('#cartDrawerBackdrop').addClass('active');
  $('#cartOffcanvasDrawer').addClass('open');
  document.body.style.overflow = 'hidden';
};

window.closeCartDrawer = function() {
  $('#cartDrawerBackdrop').removeClass('active');
  $('#cartOffcanvasDrawer').removeClass('open');
  document.body.style.overflow = '';
};

function renderDrawerItems() {
  const store = window.cartStore[activeDrawerType] || { count: 0, items: [] };
  const items = store.items || [];
  const count = items.length;
  
  $('#cart-drawer-item-count-badge').text(count + ' item' + (count === 1 ? '' : 's'));
  $('#cart-drawer-footer-count').text(count + ' item' + (count === 1 ? '' : 's'));

  const $list = $('#cart-drawer-items-list');

  if (items.length > 0) {
    let html = '';
    items.forEach(function(item) {
      const cid = item.id || item.cart_id || (`p_${item.product_id}_v_${item.variant_id || 0}`);
      const pid = item.product_id;
      const vid = item.variant_id || null;
      const name = item.product_name || 'Product';
      const img = item.thumb_image || '{{ asset("uploads/no-image.svg") }}';
      const vendor = item.vendor_name || 'Primary Supplier';
      const sku = item.sku ? `SKU: ${item.sku}` : '';
      const qty = parseFloat(item.quantity) || 1;
      const variantName = item.variant_name || '';

      html += `
        <div class="cart-item-card" id="drawer_item_${cid}">
          <img src="${img}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" class="cart-item-img" alt="${name}">
          <div class="cart-item-info">
            <div class="cart-item-name" title="${name}">${name}</div>
            <div class="cart-item-meta mb-1">
              <span><i class="fas fa-store mr-1 text-muted"></i>${vendor}</span>
              ${sku ? `<span>&bull;</span><span>${sku}</span>` : ''}
            </div>
            ${variantName ? `<div class="mb-1"><span class="badge badge-warning text-dark border font-weight-bold" style="font-size: 10px; padding: 2px 6px;"><i class="fas fa-layer-group mr-1"></i>${variantName}</span></div>` : ''}
            <div class="cart-item-qty-wrapper">
              <div class="cart-item-qty-control">
                <button type="button" class="cart-qty-btn" onclick="updateDrawerCartItemQty('${cid}', ${pid}, ${vid}, -1)" title="Decrease Qty"><i class="fas fa-minus"></i></button>
                <input type="number" class="cart-qty-input" id="qty_input_${cid}" value="${qty}" min="0.01" step="any" onchange="setDrawerCartItemQty('${cid}', ${pid}, ${vid}, this.value)">
                <button type="button" class="cart-qty-btn" onclick="updateDrawerCartItemQty('${cid}', ${pid}, ${vid}, 1)" title="Increase Qty"><i class="fas fa-plus"></i></button>
              </div>
            </div>
          </div>
          <button type="button" class="cart-item-remove-btn" onclick="removeDrawerCartItem('${cid}', ${pid}, ${vid})" title="Remove item">
            <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
          </button>
        </div>
      `;
    });
    $list.html(html);
  } else {
    const isBooking = (activeDrawerType === 'booking');
    const emptyIcon = isBooking ? 'fa-shopping-basket' : 'fa-file-invoice-dollar';
    $list.html(`
      <div class="cart-drawer-empty">
        <div class="empty-icon-circle">
          <i class="fas ${emptyIcon} fa-2x" style="color: ${isBooking ? '#eab308' : '#3b82f6'};"></i>
        </div>
        <h6 class="font-weight-bold text-dark">Your ${isBooking ? 'Procurement' : 'Sales Quotation'} Cart is Empty</h6>
        <p class="text-muted small">Select products from the catalog to add them to this list.</p>
      </div>
    `);
  }
}

window.updateDrawerCartItemQty = function(cid, productId, variantId, delta) {
  const store = window.cartStore[activeDrawerType];
  if (!store || !store.items) return;

  const numPid = Number(productId);
  const numVid = variantId ? Number(variantId) : null;

  const item = store.items.find(i => (i.id && i.id == cid) || (Number(i.product_id) === numPid && ((Number(i.variant_id) || null) === numVid)));
  if (!item) return;

  let newQty = (parseFloat(item.quantity) || 1) + delta;
  if (newQty < 0.01) newQty = 0.01;

  item.quantity = newQty;
  const $input = $('#qty_input_' + cid);
  if ($input.length) $input.val(newQty);

  // Debounced server sync
  const timerKey = cid;
  clearTimeout(qtyUpdateTimers[timerKey]);
  qtyUpdateTimers[timerKey] = setTimeout(function() {
    $.ajax({
      url: "{{ route('admin.cart.update-quantity') }}",
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: {
        cart_id: item.id || null,
        product_id: numPid,
        variant_id: numVid,
        cart_type: activeDrawerType,
        quantity: newQty
      }
    });
  }, 250);
};

window.setDrawerCartItemQty = function(cid, productId, variantId, value) {
  let val = parseFloat(value);
  if (isNaN(val) || val < 0.01) val = 1;

  const store = window.cartStore[activeDrawerType];
  if (!store || !store.items) return;

  const numPid = Number(productId);
  const numVid = variantId ? Number(variantId) : null;

  const item = store.items.find(i => (i.id && i.id == cid) || (Number(i.product_id) === numPid && ((Number(i.variant_id) || null) === numVid)));
  if (!item) return;

  item.quantity = val;
  const $input = $('#qty_input_' + cid);
  if ($input.length) $input.val(val);

  $.ajax({
    url: "{{ route('admin.cart.update-quantity') }}",
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: {
      cart_id: item.id || null,
      product_id: numPid,
      variant_id: numVid,
      cart_type: activeDrawerType,
      quantity: val
    }
  });
};

window.removeDrawerCartItem = function(cid, productId, variantId) {
  const store = window.cartStore[activeDrawerType];
  const numPid = Number(productId);
  const numVid = variantId ? Number(variantId) : null;

  if (store) {
    store.items = store.items.filter(i => !((i.id && i.id == cid) || (Number(i.product_id) === numPid && ((Number(i.variant_id) || null) === numVid))));
    
    // Check if any other variant of this product remains
    const otherProductItems = store.items.filter(i => Number(i.product_id) === numPid);
    if (otherProductItems.length === 0) {
      store.ids = store.ids.filter(id => Number(id) !== numPid);
    }
    store.count = store.items.length;
    
    if (activeDrawerType === 'booking') {
      window.updateGlobalCartBadges(store.count, undefined);
    } else {
      window.updateGlobalCartBadges(undefined, store.count);
    }
  }

  // Fast optimistic toastr
  if (window.toastr) toastr.info('Item removed from cart');

  // Instant DOM card removal (0ms)
  const $card = $('#drawer_item_' + cid);
  $card.fadeOut(150, function() {
    $(this).remove();
    renderDrawerItems();
  });

  if (window.reapplyCartButtonStates) {
    window.reapplyCartButtonStates();
  }

  // Background MySQL delete
  $.ajax({
    url: "{{ route('admin.cart.remove') }}",
    type: 'POST',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: {
      product_id: numPid,
      variant_id: numVid,
      cart_type: activeDrawerType
    }
  });
};

window.clearActiveDrawerCart = function() {
  const store = window.cartStore[activeDrawerType];
  if (!store || store.count === 0) return;

  const cartName = activeDrawerType === 'booking' ? 'Procurement Cart' : 'Sales Quotation Cart';

  const doClear = function() {
    store.items = [];
    store.ids = [];
    store.count = 0;

    if (activeDrawerType === 'booking') {
      window.updateGlobalCartBadges(0, undefined);
    } else {
      window.updateGlobalCartBadges(undefined, 0);
    }

    if (window.toastr) toastr.info(`${cartName} cleared`);
    renderDrawerItems();

    if (window.reapplyCartButtonStates) {
      window.reapplyCartButtonStates();
    }

    $.ajax({
      url: "{{ route('admin.cart.clear') }}",
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: { cart_type: activeDrawerType }
    });
  };

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      title: "Clear Cart?",
      text: `Are you sure you want to clear all items from your ${cartName}?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Yes, clear all!",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        doClear();
      }
    });
  } else {
    doClear();
  }
};

// Initial sync on page load
document.addEventListener('DOMContentLoaded', function() {
  @auth
  if (window.fetch) {
    fetch("{{ route('admin.cart.all-state') }}")
      .then(function(res) { return res.json(); })
      .then(function(res) {
        if (res && res.booking && res.request) {
          const mergeStore = function(type, incoming) {
            if (!window.cartStore[type] || !window.cartStore[type].items.length) {
              window.cartStore[type] = incoming;
            } else {
              // Merge items gracefully if user already clicked add before fetch finished
              (incoming.items || []).forEach(function(incItem) {
                const exists = window.cartStore[type].items.some(function(locItem) {
                  return Number(locItem.product_id) === Number(incItem.product_id) && 
                         (Number(locItem.variant_id) || null) === (Number(incItem.variant_id) || null);
                });
                if (!exists) {
                  window.cartStore[type].items.push(incItem);
                }
              });
              window.cartStore[type].ids = [...new Set(window.cartStore[type].items.map(function(i) { return Number(i.product_id); }))];
              window.cartStore[type].count = window.cartStore[type].items.length;
            }
          };

          mergeStore('booking', res.booking);
          mergeStore('request', res.request);

          window.updateGlobalCartBadges(window.cartStore.booking.count, window.cartStore.request.count);
          if (window.reapplyCartButtonStates) window.reapplyCartButtonStates();
        }
      })
      .catch(function() {});
  }

  // Close drawer on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeCartDrawer();
    }
  });
  @endauth
});
</script>
