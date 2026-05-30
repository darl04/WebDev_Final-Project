(function () {
    'use strict';

    const config = window.ORDER_POLL_CONFIG;
    if (!config || !config.url) {
        return;
    }

    const INTERVAL_MS = config.intervalMs || 5000;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function formatPeso(amount) {
        const n = Number(amount) || 0;
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function startPolling(onData) {
        const tick = async function () {
            if (document.hidden) {
                return;
            }
            try {
                const response = await fetch(config.url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    return;
                }
                onData(await response.json());
            } catch (err) {
                console.warn('Order poll failed:', err);
            }
        };

        tick();
        return window.setInterval(tick, INTERVAL_MS);
    }

    function showToast(message) {
        let toast = document.getElementById('order-poll-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'order-poll-toast';
            toast.style.cssText =
                'position:fixed;bottom:24px;right:24px;z-index:9999;padding:14px 20px;' +
                'background:#662d91;color:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);' +
                'font-size:14px;font-weight:500;opacity:0;transition:opacity 0.3s ease;max-width:320px;';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.opacity = '1';
        window.clearTimeout(toast._hideTimer);
        toast._hideTimer = window.setTimeout(function () {
            toast.style.opacity = '0';
        }, 4000);
    }

    function renderStatusBadge(status, statusClass) {
        return (
            '<span class="status-badge status-badge-' +
            escapeHtml(statusClass) +
            '">' +
            escapeHtml(status) +
            '</span>'
        );
    }

    function renderMyOrderCard(order) {
        const sk = order.statusKey || (order.status || '').toLowerCase();
        const sc = order.statusClass || sk.replace(/\s+/g, '-');

        return (
            '<article class="my-order-card" data-order-id="' +
            escapeHtml(order.id) +
            '">' +
            '<div class="my-order-main">' +
            '<div class="order-status-icon order-status-icon-' +
            escapeHtml(sc) +
            '" aria-hidden="true">' +
            (order.iconHtml || '') +
            '</div>' +
            '<div>' +
            '<div class="my-order-id">Order #' +
            escapeHtml(order.id) +
            '</div>' +
            '<div class="my-order-meta">' +
            escapeHtml(order.createdAt || '—') +
            ' · ' +
            escapeHtml(order.itemCount) +
            ' item' +
            (order.itemCount !== 1 ? 's' : '') +
            '</div>' +
            '</div>' +
            '</div>' +
            renderStatusBadge(order.status, sc) +
            '<a href="' +
            escapeHtml(order.showUrl) +
            '" class="btn-view-order"><i class="fas fa-eye"></i> View details</a>' +
            '</article>'
        );
    }

    function initDashboardPoll() {
        const statsRoot = document.getElementById('dashboard-poll-root');
        if (!statsRoot) {
            return;
        }

        let lastOrderCount = Number(statsRoot.dataset.initialOrders || 0);
        let knownOrderIds = new Set(
            (statsRoot.dataset.initialOrderIds || '')
                .split(',')
                .filter(Boolean)
                .map(String),
        );

        function updateStats(data) {
            const map = {
                totalProducts: data.totalProducts,
                totalCustomers: data.totalCustomers,
                totalOrders: data.totalOrders,
            };
            Object.keys(map).forEach(function (key) {
                const el = document.querySelector('[data-dashboard-stat="' + key + '"]');
                if (el) {
                    el.textContent = map[key];
                }
            });

            const revenueEl = document.querySelector('[data-dashboard-stat="revenue"]');
            if (revenueEl) {
                revenueEl.textContent = formatPeso(data.revenue);
            }
            const breakdownEl = document.getElementById('dashboard-revenue-breakdown');
            if (breakdownEl) {
                breakdownEl.textContent =
                    'Orders: ' +
                    formatPeso(data.orderRevenue) +
                    ' | Rentals: ' +
                    formatPeso(data.rentalRevenue);
            }

            const list = document.getElementById('dashboard-recent-orders');
            if (list && Array.isArray(data.recentOrders)) {
                if (data.recentOrders.length === 0) {
                    list.innerHTML =
                        '<p class="recent-orders-empty">No orders yet. New orders will appear here automatically.</p>';
                } else {
                    list.innerHTML = data.recentOrders
                        .map(function (order) {
                            const sc = order.statusClass || (order.status || '').toLowerCase();
                            const products =
                                (order.productNames || []).join(', ') || 'No products';
                            return (
                                '<div class="recent-order-row" data-order-id="' +
                                escapeHtml(order.id) +
                                '">' +
                                '<div class="recent-order-main">' +
                                '<strong>Order #' +
                                escapeHtml(order.id) +
                                '</strong>' +
                                '<span class="recent-order-meta">' +
                                escapeHtml(order.customerName) +
                                ' · ' +
                                escapeHtml(order.createdAt || '') +
                                '</span>' +
                                '<span class="recent-order-products">' +
                                escapeHtml(products) +
                                '</span>' +
                                '</div>' +
                                renderStatusBadge(order.status, sc) +
                                (order.showUrl
                                    ? '<a href="' +
                                      escapeHtml(order.showUrl) +
                                      '" class="recent-order-link"><i class="fas fa-eye"></i></a>'
                                    : '') +
                                '</div>'
                            );
                        })
                        .join('');
                }

                const newIds = data.recentOrders.map(function (o) {
                    return String(o.id);
                });
                newIds.forEach(function (id) {
                    if (!knownOrderIds.has(id)) {
                        knownOrderIds.add(id);
                        showToast('New order #' + id + ' received');
                    }
                });
            }

            if (data.totalOrders > lastOrderCount) {
                showToast('New order received — total orders: ' + data.totalOrders);
                lastOrderCount = data.totalOrders;
            }
        }

        startPolling(updateStats);
    }

    function initMyOrdersPoll() {
        const root = document.getElementById('my-orders-poll-root');
        if (!root) {
            return;
        }

        const emptyHtml = root.dataset.emptyHtml || '';
        let lastSnapshot = '';
        let hasLoaded = false;

        function applyOrders(orders) {
            const snapshot = JSON.stringify(orders);
            if (snapshot === lastSnapshot) {
                return;
            }

            const prev = hasLoaded && lastSnapshot ? JSON.parse(lastSnapshot) : [];
            orders.forEach(function (order) {
                const old = prev.find(function (p) {
                    return String(p.id) === String(order.id);
                });
                if (!old && hasLoaded) {
                    showToast('Order #' + order.id + ' was added to your list');
                } else if (old && old.status !== order.status) {
                    showToast('Order #' + order.id + ' is now ' + order.status);
                }
            });

            lastSnapshot = snapshot;
            hasLoaded = true;

            if (!orders.length) {
                root.innerHTML = emptyHtml;
                return;
            }

            root.innerHTML =
                '<div class="my-orders-list">' +
                orders.map(renderMyOrderCard).join('') +
                '</div>';
        }

        startPolling(function (data) {
            if (!Array.isArray(data.orders)) {
                return;
            }
            applyOrders(data.orders);
        });
    }

    function initMyOrderShowPoll() {
        const banner = document.getElementById('my-order-status-banner');
        if (!banner) {
            return;
        }

        let lastStatus = banner.dataset.status || '';

        startPolling(function (data) {
            const order = data.order;
            if (!order) {
                return;
            }

            if (order.status !== lastStatus) {
                lastStatus = order.status;
                showToast('Order #' + order.id + ' is now ' + order.status);
            }

            const sc = order.statusClass || order.statusKey;
            const iconWrap = document.getElementById('my-order-status-icon');
            if (iconWrap) {
                iconWrap.className = 'order-status-icon order-status-icon-' + sc;
                iconWrap.innerHTML = order.iconHtml || '';
            }

            const titleEl = document.getElementById('my-order-status-title');
            if (titleEl) {
                titleEl.textContent = order.title || '';
            }

            const badgeEl = document.getElementById('my-order-status-badge');
            if (badgeEl) {
                badgeEl.className = 'status-badge status-badge-' + sc;
                badgeEl.textContent = order.status;
            }
        });
    }

    if (config.mode === 'dashboard') {
        initDashboardPoll();
    } else if (config.mode === 'my-orders') {
        initMyOrdersPoll();
    } else if (config.mode === 'my-order-show') {
        initMyOrderShowPoll();
    }
})();
