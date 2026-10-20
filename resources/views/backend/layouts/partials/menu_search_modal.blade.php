{{-- ============================================
     GLOBAL MENU SEARCH COMMAND PALETTE (Ctrl + K)
     Modularized View Component (Spotlight / Raycast Style)
     Data served securely via MenuSearchService & MenuSearchController
     ============================================ --}}

<style>
/* ========================================================
   COMMAND PALETTE / MENU SEARCH MODAL STYLES (Ref Template)
   ======================================================== */
.ms-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(5, 8, 16, 0.78);
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
    z-index: 100000;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 70px 16px 24px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

.ms-backdrop.ms-active {
    opacity: 1;
    visibility: visible;
}

.ms-palette-modal {
    width: 100%;
    max-width: 640px;
    background: #0d111a;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 16px;
    box-shadow: 0 25px 65px -15px rgba(0, 0, 0, 0.85), 0 0 0 1px rgba(205, 160, 90, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(-14px) scale(0.98);
    transition: transform 0.24s cubic-bezier(0.16, 1, 0.3, 1);
    font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ms-backdrop.ms-active .ms-palette-modal {
    transform: translateY(0) scale(1);
}

/* Header with input */
.ms-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #0d111a;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    position: relative;
}

.ms-search-icon {
    font-size: 17px;
    color: #64748b;
    flex-shrink: 0;
    transition: color 0.15s;
}

.ms-header.is-focused .ms-search-icon {
    color: #e3bd7c;
}

.ms-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #f8fafc;
    font-size: 15.5px;
    font-weight: 500;
    letter-spacing: 0.2px;
    padding: 0;
}

.ms-input::placeholder {
    color: #64748b;
    font-weight: 400;
}

.ms-clear-btn {
    background: rgba(255, 255, 255, 0.06);
    border: none;
    color: #94a3b8;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.ms-clear-btn:hover {
    background: rgba(255, 255, 255, 0.14);
    color: #fff;
}

.ms-clear-btn.is-visible {
    display: flex;
}

.ms-esc-tag {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 6px;
    padding: 2px 7px;
    cursor: pointer;
    user-select: none;
    letter-spacing: 0.5px;
    transition: all 0.15s;
    flex-shrink: 0;
}

.ms-esc-tag:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}

/* Category Tabs (Direct match to reference image) */
.ms-tabs-bar {
    display: flex;
    align-items: center;
    padding: 8px 18px 0;
    background: #0a0e17;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    overflow-x: auto;
    scrollbar-width: none;
}

.ms-tabs-bar::-webkit-scrollbar {
    display: none;
}

.ms-tab-btn {
    background: transparent;
    border: none;
    outline: none;
    color: #94a3b8;
    font-size: 13px;
    font-weight: 500;
    padding: 7px 12px 10px;
    margin-right: 4px;
    cursor: pointer;
    position: relative;
    white-space: nowrap;
    transition: color 0.15s ease;
    border-bottom: 2px solid transparent;
}

.ms-tab-btn:hover {
    color: #f1f5f9;
}

.ms-tab-btn.active {
    color: #38bdf8;
    font-weight: 600;
    border-bottom-color: #38bdf8;
}

.ms-tab-settings-icon {
    margin-left: auto;
    color: #64748b;
    font-size: 13.5px;
    padding: 6px;
    cursor: pointer;
    transition: color 0.15s;
}

.ms-tab-settings-icon:hover {
    color: #e2e8f0;
}

/* Content Area */
.ms-body {
    max-height: 380px;
    min-height: 180px;
    overflow-y: auto;
    padding: 12px 16px 16px;
    background: #0b0f19;
    position: relative;
}

.ms-body::-webkit-scrollbar {
    width: 6px;
}
.ms-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.12);
    border-radius: 999px;
}

/* Group Headers */
.ms-group-header {
    font-size: 11.5px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: capitalize;
    letter-spacing: 0.3px;
    padding: 10px 10px 6px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.ms-group-header:first-child {
    margin-top: 0;
}

/* Result Items */
.ms-item-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 9px 12px;
    border-radius: 10px;
    text-decoration: none !important;
    color: #f1f5f9;
    background: transparent;
    transition: background 0.12s ease, transform 0.12s ease;
    cursor: pointer;
    margin-bottom: 2px;
    outline: none;
}

.ms-item-link:hover,
.ms-item-link.ms-selected {
    background: rgba(255, 255, 255, 0.06);
}

.ms-item-link.ms-selected {
    box-shadow: inset 2px 0 0 #38bdf8;
}

.ms-item-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}

.ms-item-avatar {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.ms-item-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.ms-item-title {
    font-size: 13.5px;
    font-weight: 600;
    color: #f8fafc;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ms-item-desc {
    font-size: 11.5px;
    color: #94a3b8;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

.ms-highlight {
    background: rgba(205, 160, 90, 0.28);
    color: #fcd34d;
    padding: 0 2px;
    border-radius: 3px;
    font-weight: 700;
}

.ms-item-badge {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 6px;
    padding: 3px 8px;
    flex-shrink: 0;
}

.ms-item-link.ms-selected .ms-item-badge {
    background: rgba(56, 189, 248, 0.15);
    border-color: rgba(56, 189, 248, 0.3);
    color: #38bdf8;
}

/* Loading & Empty States */
.ms-loading-state,
.ms-empty-state {
    padding: 40px 16px;
    text-align: center;
    color: #64748b;
}

.ms-loading-icon {
    font-size: 26px;
    color: #38bdf8;
    margin-bottom: 12px;
}

.ms-empty-icon {
    font-size: 32px;
    color: #475569;
    margin-bottom: 10px;
    display: block;
}

.ms-empty-title {
    font-size: 14px;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 4px;
}

.ms-empty-sub {
    font-size: 12.5px;
    color: #64748b;
}

/* Footer Instructions */
.ms-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 18px;
    background: #090d15;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    font-size: 11px;
    color: #64748b;
}

.ms-footer-hints {
    display: flex;
    align-items: center;
    gap: 14px;
}

.ms-footer kbd {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    font-size: 10px;
    padding: 1.5px 5px;
    border-radius: 4px;
    box-shadow: 0 1px 0 rgba(0,0,0,0.4);
    font-family: inherit;
}

.ms-count-badge {
    font-size: 11px;
    color: #94a3b8;
}

@media (max-width: 640px) {
    .ms-backdrop {
        padding: 16px 8px;
    }
    .ms-palette-modal {
        border-radius: 12px;
        max-height: 85vh;
    }
    .ms-footer-hints {
        display: none;
    }
}
</style>

{{-- Backdrop and Modal --}}
<div id="menuSearchBackdrop" class="ms-backdrop" onclick="handleMenuSearchBackdropClick(event)">
    <div class="ms-palette-modal" onclick="event.stopPropagation()">
        {{-- Header Search Bar --}}
        <div class="ms-header" id="msHeader">
            <i class="fas fa-search ms-search-icon"></i>
            <input type="text" id="menuSearchInput" class="ms-input" 
                   placeholder="Tap to start search" autocomplete="off" spellcheck="false" />
            <button type="button" id="menuSearchClearBtn" class="ms-clear-btn" 
                    title="Clear search (Esc)" onclick="clearMenuSearchInput()">
                <i class="fas fa-times"></i>
            </button>
            <span class="ms-esc-tag" onclick="closeMenuSearchModal()">ESC</span>
        </div>

        {{-- Filter Tabs (Exact layout as template image) --}}
        <div class="ms-tabs-bar">
            <button type="button" class="ms-tab-btn active" data-tab="mixed" onclick="setMenuSearchTab('mixed', this)">Mixed</button>
            <button type="button" class="ms-tab-btn" data-tab="settings" onclick="setMenuSearchTab('settings', this)">Settings</button>
            <button type="button" class="ms-tab-btn" data-tab="inventory" onclick="setMenuSearchTab('inventory', this)">Inventory</button>
            <button type="button" class="ms-tab-btn" data-tab="orders" onclick="setMenuSearchTab('orders', this)">Orders</button>
            <button type="button" class="ms-tab-btn" data-tab="procurement" onclick="setMenuSearchTab('procurement', this)">Procurement</button>
            <button type="button" class="ms-tab-btn" data-tab="finance" onclick="setMenuSearchTab('finance', this)">Finance</button>
            <span class="ms-tab-settings-icon" title="Quick Navigation" onclick="focusSearchInput()">
                <i class="fas fa-cog"></i>
            </span>
        </div>

        {{-- Dynamic Search Results Body --}}
        <div class="ms-body" id="menuSearchResultsBody">
            <div class="ms-loading-state">
                <i class="fas fa-circle-notch fa-spin ms-loading-icon"></i>
                <div class="ms-empty-sub">Initializing menu index...</div>
            </div>
        </div>

        {{-- Footer Controls & Hints --}}
        <div class="ms-footer">
            <div class="ms-footer-hints">
                <span><kbd>↑</kbd> <kbd>↓</kbd> Navigate</span>
                <span><kbd>↵</kbd> Open Page</span>
                <span><kbd>ESC</kbd> Close</span>
            </div>
            <div class="ms-count-badge" id="msResultCount">
                Loading menus...
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // 1. Asynchronously Loaded Secure Menu Registry
    var MENU_INDEX = [];
    var isLoaded = false;
    var isLoading = false;
    var REGISTRY_URL = '{{ route("admin.menu-search.registry") }}';

    var activeTab = 'mixed';
    var selectedIndex = 0;
    var currentVisibleItems = []; // Strictly matches the visual top-to-bottom DOM order
    var isKeyboardNavigating = false;
    var keyboardNavTimer = null;

    // DOM Elements
    var backdrop = document.getElementById('menuSearchBackdrop');
    var input = document.getElementById('menuSearchInput');
    var clearBtn = document.getElementById('menuSearchClearBtn');
    var header = document.getElementById('msHeader');
    var resultsBody = document.getElementById('menuSearchResultsBody');
    var countBadge = document.getElementById('msResultCount');

    function setKeyboardNavigating() {
        isKeyboardNavigating = true;
        clearTimeout(keyboardNavTimer);
        keyboardNavTimer = setTimeout(function() {
            isKeyboardNavigating = false;
        }, 500);
    }

    document.addEventListener('mousemove', function() {
        isKeyboardNavigating = false;
    });

    // 2. Fetch Menu Registry from Controller (Cached per session user)
    function fetchMenuRegistry(callback) {
        if (isLoaded) {
            if (callback) callback();
            return;
        }
        if (isLoading) return;
        isLoading = true;

        fetch(REGISTRY_URL, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Network error: ' + res.status);
            return res.json();
        })
        .then(function(json) {
            isLoading = false;
            isLoaded = true;
            MENU_INDEX = (json && json.data) ? json.data : [];
            if (countBadge) {
                countBadge.textContent = MENU_INDEX.length + ' menus available';
            }
            if (callback) callback();
            else if (backdrop && backdrop.classList.contains('ms-active')) {
                renderResults();
            }
        })
        .catch(function(err) {
            isLoading = false;
            console.error('Menu Search load error:', err);
            if (resultsBody && (!MENU_INDEX || MENU_INDEX.length === 0)) {
                resultsBody.innerHTML = 
                    '<div class="ms-empty-state">' +
                        '<i class="fas fa-exclamation-triangle ms-empty-icon" style="color: #ef4444;"></i>' +
                        '<div class="ms-empty-title">Failed to load menus</div>' +
                        '<div class="ms-empty-sub">Please refresh the page and try again.</div>' +
                    '</div>';
            }
        });
    }

    // Prefetch menu registry in background so Cmd+K is instantly ready
    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(function() { fetchMenuRegistry(); });
    } else {
        setTimeout(function() { fetchMenuRegistry(); }, 400);
    }

    // 3. Open / Close functions
    window.openMenuSearchModal = function() {
        if (!backdrop) return;
        backdrop.classList.add('ms-active');
        document.body.style.overflow = 'hidden';
        selectedIndex = 0;
        setTimeout(function() {
            if (input) {
                input.focus();
                input.select();
            }
        }, 50);

        if (!isLoaded) {
            resultsBody.innerHTML = 
                '<div class="ms-loading-state">' +
                    '<i class="fas fa-circle-notch fa-spin ms-loading-icon"></i>' +
                    '<div class="ms-empty-sub">Loading menus...</div>' +
                '</div>';
            fetchMenuRegistry(function() {
                renderResults();
            });
        } else {
            renderResults();
        }
    };

    window.closeMenuSearchModal = function() {
        if (!backdrop) return;
        backdrop.classList.remove('ms-active');
        document.body.style.overflow = '';
    };

    window.handleMenuSearchBackdropClick = function(e) {
        if (e.target === backdrop) {
            closeMenuSearchModal();
        }
    };

    window.clearMenuSearchInput = function() {
        if (input) {
            input.value = '';
            input.focus();
            handleSearchInput();
        }
    };

    window.focusSearchInput = function() {
        if (input) input.focus();
    };

    window.setMenuSearchTab = function(tabName, btnElem) {
        activeTab = tabName;
        var allTabs = document.querySelectorAll('.ms-tab-btn');
        allTabs.forEach(function(b) { b.classList.remove('active'); });
        if (btnElem) btnElem.classList.add('active');
        selectedIndex = 0;
        renderResults();
    };

    // 4. Highlighting Helper
    function highlightMatch(text, query) {
        if (!query) return text;
        var safeQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var regex = new RegExp('(' + safeQuery + ')', 'gi');
        return text.replace(regex, '<mark class="ms-highlight">$1</mark>');
    }

    function escapeRegex(str) {
        if (!str) return '';
        return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // 5. Smooth Auto-Scroll Helper for results container
    function scrollItemIntoView(itemElem) {
        if (!resultsBody || !itemElem) return;

        var containerRect = resultsBody.getBoundingClientRect();
        var elemRect = itemElem.getBoundingClientRect();

        // If top of item is above visible area of resultsBody
        if (elemRect.top < containerRect.top) {
            resultsBody.scrollTop -= (containerRect.top - elemRect.top + 8);
        }
        // If bottom of item is below visible area of resultsBody
        else if (elemRect.bottom > containerRect.bottom) {
            resultsBody.scrollTop += (elemRect.bottom - containerRect.bottom + 8);
        }
    }

    // 6. Filter & Render with Intelligent Relevance Scoring
    function renderResults() {
        if (!isLoaded && MENU_INDEX.length === 0) return;

        var query = (input.value || '').trim().toLowerCase();
        
        // Show/Hide clear button
        if (clearBtn) {
            if (query.length > 0) {
                clearBtn.classList.add('is-visible');
            } else {
                clearBtn.classList.remove('is-visible');
            }
        }

        // Relevance Scoring & Filtering
        var scoredItems = [];
        
        MENU_INDEX.forEach(function(item) {
            // Tab filter
            if (activeTab !== 'mixed') {
                if (item.category !== activeTab) {
                    return;
                }
            }

            // If query is empty, show default list with 0 score
            if (!query) {
                scoredItems.push({ item: item, score: 0 });
                return;
            }

            var title = (item.title || '').toLowerCase();
            var desc = (item.description || '').toLowerCase();
            var kw = (item.keywords || '').toLowerCase();
            var group = (item.group || '').toLowerCase();

            var score = 0;

            // 1. Exact Title Match
            if (title === query) {
                score += 3000;
            } else {
                // Check if any word in title starts with query
                var words = title.split(/\s+/);
                var matchedAtStart = false;
                var matchedInPrimaryWord = false;
                for (var i = 0; i < words.length; i++) {
                    if (words[i].indexOf(query) === 0) {
                        matchedAtStart = true;
                        // Boost for primary word like "Products" in "All Products Catalog"
                        if (i === 0 || (i === 1 && (words[0] === 'all' || words[0] === 'b2b'))) {
                            matchedInPrimaryWord = true;
                        }
                    }
                }
                if (matchedInPrimaryWord) score += 1800;
                else if (matchedAtStart) score += 1200;
                else if (title.indexOf(query) !== -1) score += 700;
            }

            // 2. Group matches (e.g., "Products & Catalog")
            if (group.indexOf(query) === 0) {
                score += 500;
            } else if (new RegExp('\\b' + escapeRegex(query), 'i').test(group)) {
                score += 350;
            } else if (group.indexOf(query) !== -1) {
                score += 200;
            }

            // 3. Keywords match
            if (new RegExp('\\b' + escapeRegex(query), 'i').test(kw)) {
                score += 250;
            } else if (kw.indexOf(query) !== -1) {
                score += 120;
            }

            // 4. Description match (lowest weight so casual mentions don't hijack search)
            if (new RegExp('\\b' + escapeRegex(query), 'i').test(desc)) {
                score += 40;
            } else if (desc.indexOf(query) !== -1) {
                score += 20;
            }

            // Only keep items that matched something
            if (score > 0) {
                scoredItems.push({ item: item, score: score });
            }
        });

        // Sort items by relevance score descending
        if (query) {
            scoredItems.sort(function(a, b) {
                return b.score - a.score;
            });
        }

        var filteredList = scoredItems.map(function(s) {
            return s.item;
        });

        // Update count badge
        if (countBadge) {
            countBadge.textContent = filteredList.length + ' result' + (filteredList.length === 1 ? '' : 's');
        }

        // Handle empty results
        if (filteredList.length === 0) {
            currentVisibleItems = [];
            selectedIndex = 0;
            resultsBody.innerHTML = 
                '<div class="ms-empty-state">' +
                    '<i class="fas fa-search-minus ms-empty-icon"></i>' +
                    '<div class="ms-empty-title">No matching menus found</div>' +
                    '<div class="ms-empty-sub">We couldn\'t find any menu matching "' + escapeHtml(query) + '". Try searching with different keywords like stock, order, or users.</div>' +
                '</div>';
            return;
        }

        // Group results in stable order according to first appearance in sorted list
        var groups = {};
        var groupOrder = [];
        filteredList.forEach(function(item) {
            var g = item.group || 'Menus';
            if (!groups[g]) {
                groups[g] = [];
                groupOrder.push(g);
            }
            groups[g].push(item);
        });

        // Flatten items strictly into currentVisibleItems in the exact top-to-bottom visual sequence
        currentVisibleItems = [];
        var visualIndex = 0;
        var html = '';

        // Clamp selectedIndex within valid range
        if (selectedIndex >= filteredList.length) {
            selectedIndex = Math.max(0, filteredList.length - 1);
        }

        groupOrder.forEach(function(groupName) {
            var itemsInGroup = groups[groupName];
            if (!itemsInGroup || itemsInGroup.length === 0) return;

            html += '<div class="ms-group-header">' + escapeHtml(groupName) + '</div>';
            
            itemsInGroup.forEach(function(itm) {
                var vIdx = visualIndex++;
                currentVisibleItems.push(itm);

                var isSelected = (vIdx === selectedIndex);
                var activeClass = isSelected ? ' ms-selected' : '';
                var highlightedTitle = highlightMatch(escapeHtml(itm.title), query);
                var highlightedDesc = highlightMatch(escapeHtml(itm.description), query);

                html += 
                    '<a href="' + itm.url + '" class="ms-item-link' + activeClass + '" data-index="' + vIdx + '" ' +
                       'onmouseenter="onItemMouseEnter(' + vIdx + ')">' +
                        '<div class="ms-item-left">' +
                            '<div class="ms-item-avatar" style="color: ' + (itm.color || '#38bdf8') + ';">' +
                                '<i class="' + itm.icon + '"></i>' +
                            '</div>' +
                            '<div class="ms-item-text">' +
                                '<div class="ms-item-title">' + highlightedTitle + '</div>' +
                                '<div class="ms-item-desc">' + highlightedDesc + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="ms-item-badge">' +
                            (isSelected ? '<i class="fas fa-level-down-alt fa-rotate-90 mr-1"></i> Open' : capitalize(itm.category)) +
                        '</div>' +
                    '</a>';
            });
        });

        resultsBody.innerHTML = html;

        // Auto-scroll to selected element initially
        var selectedElem = resultsBody.querySelector('.ms-item-link.ms-selected');
        if (selectedElem) {
            scrollItemIntoView(selectedElem);
        }
    }

    // 7. Navigation and Index Update
    window.onItemMouseEnter = function(idx) {
        if (isKeyboardNavigating) return;
        setMenuSearchIndex(idx, false);
    };

    window.setMenuSearchIndex = function(idx, shouldScroll) {
        if (typeof shouldScroll === 'undefined') shouldScroll = true;
        if (!currentVisibleItems || currentVisibleItems.length === 0) return;

        if (idx < 0) idx = currentVisibleItems.length - 1;
        if (idx >= currentVisibleItems.length) idx = 0;
        selectedIndex = idx;

        var links = resultsBody.querySelectorAll('.ms-item-link');
        var targetLink = null;

        links.forEach(function(link) {
            var linkIdx = parseInt(link.getAttribute('data-index'), 10);
            if (linkIdx === idx) {
                link.classList.add('ms-selected');
                targetLink = link;
                var badge = link.querySelector('.ms-item-badge');
                if (badge) badge.innerHTML = '<i class="fas fa-level-down-alt fa-rotate-90 mr-1"></i> Open';
            } else {
                link.classList.remove('ms-selected');
                var itm = currentVisibleItems[linkIdx];
                var badge = link.querySelector('.ms-item-badge');
                if (badge && itm) badge.textContent = capitalize(itm.category);
            }
        });

        if (shouldScroll && targetLink) {
            scrollItemIntoView(targetLink);
        }
    };

    function navigateDown() {
        if (!currentVisibleItems || currentVisibleItems.length === 0) return;
        setKeyboardNavigating();
        var nextIdx = (selectedIndex + 1) % currentVisibleItems.length;
        setMenuSearchIndex(nextIdx, true);
    }

    function navigateUp() {
        if (!currentVisibleItems || currentVisibleItems.length === 0) return;
        setKeyboardNavigating();
        var prevIdx = (selectedIndex - 1 + currentVisibleItems.length) % currentVisibleItems.length;
        setMenuSearchIndex(prevIdx, true);
    }

    function openSelectedMenuItem() {
        if (!currentVisibleItems || currentVisibleItems.length === 0) return;
        var selectedItem = currentVisibleItems[selectedIndex];
        if (selectedItem && selectedItem.url) {
            window.location.href = selectedItem.url;
        }
    }

    function handleSearchInput() {
        selectedIndex = 0;
        renderResults();
    }

    // 8. Event Listeners
    if (input) {
        input.addEventListener('input', handleSearchInput);
        input.addEventListener('focus', function() {
            if (header) header.classList.add('is-focused');
        });
        input.addEventListener('blur', function() {
            if (header) header.classList.remove('is-focused');
        });

        // Dedicated keyboard navigation on input field
        input.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                navigateDown();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                navigateUp();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                openSelectedMenuItem();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeMenuSearchModal();
            }
        });
    }

    // Global Keyboard Shortcuts (Ctrl+K and backup when input loses focus)
    document.addEventListener('keydown', function(e) {
        // Ctrl + K or Cmd + K to open
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (backdrop && backdrop.classList.contains('ms-active')) {
                closeMenuSearchModal();
            } else {
                openMenuSearchModal();
            }
            return;
        }

        // Only handle following shortcuts if modal is open
        if (!backdrop || !backdrop.classList.contains('ms-active')) return;

        // If focus is currently inside the input, the input's own listener already handled it
        if (document.activeElement === input) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            closeMenuSearchModal();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            navigateDown();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            navigateUp();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            openSelectedMenuItem();
        }
    });

    // Helper utilities
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function capitalize(s) {
        if (!s) return '';
        return s.charAt(0).toUpperCase() + s.slice(1);
    }
})();
</script>
