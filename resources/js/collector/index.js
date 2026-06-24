/*
 * Collector PWA bootstrap.
 *
 * Loaded only on the collector surface (via the collector shell layout). It:
 *   1. Registers the /collector-scoped service worker for offline asset
 *      loading + transparent background updates.
 *   2. Starts the payment sync engine (queue-first, proactive flushing).
 *   3. Renders a small, unobtrusive connectivity / queue indicator.
 *   4. Exposes window.collectorApp so the record-payment screen can capture
 *      every payment queue-first, online or offline (build spec 9.10).
 */
import { db } from './db.js';
import { flush, refreshRoute, startSync } from './sync.js';

function uuid() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return window.crypto.randomUUID();
    }
    // RFC4122-ish fallback for older budget-device webviews.
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

/**
 * Capture a payment queue-first. Always writes to IndexedDB, then immediately
 * attempts a flush. Online, this resolves like a normal round-trip; offline, it
 * stays queued and syncs automatically later. Returns the queued record.
 */
async function queuePayment({ loanId, amount, collectedAt = null }) {
    const record = {
        idempotency_key: uuid(),
        loan_id: loanId,
        amount: Number(amount),
        collected_at: collectedAt || new Date().toISOString(),
        device_identifier: null,
        state: 'queued',
        created_at: Date.now(),
    };
    await db.queuePayment(record);
    // Fire-and-forget; the queued state is already durable.
    flush();
    return record;
}

function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/collector-sw.js', { scope: '/collector' })
            .then((registration) => {
                // Cache-then-network update: a new worker installs in the
                // background and takes over on the next app open.
                registration.addEventListener('updatefound', () => {
                    const installing = registration.installing;
                    if (!installing) { return; }
                    installing.addEventListener('statechange', () => {
                        if (installing.state === 'installed' && navigator.serviceWorker.controller) {
                            // A fresh version is ready; it activates next launch.
                            installing.postMessage('SKIP_WAITING');
                        }
                    });
                });
            })
            .catch(() => { /* SW registration is best-effort */ });
    });
}

function mountIndicator() {
    const el = document.createElement('div');
    el.id = 'collector-net-indicator';
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');
    el.style.cssText = [
        'position:fixed', 'left:50%', 'transform:translateX(-50%)',
        'bottom:92px', 'z-index:60', 'max-width:92%',
        'padding:8px 14px', 'border-radius:9999px',
        'font:600 12px/1.2 system-ui,-apple-system,sans-serif',
        'display:none', 'align-items:center', 'gap:8px',
        'box-shadow:0 4px 16px rgba(0,0,0,.4)', 'transition:opacity .2s',
        'pointer-events:none', 'white-space:nowrap',
    ].join(';');
    document.body.appendChild(el);

    function paint(detail) {
        const { online, queued, needsAttention } = detail;
        let bg = '', fg = '', text = '', show = true;

        if (needsAttention > 0) {
            bg = '#5a1212'; fg = '#ffb4ab';
            text = `⚠ ${needsAttention} payment${needsAttention > 1 ? 's' : ''} need attention`;
        } else if (!online) {
            bg = '#3c4d00'; fg = '#c3f400';
            text = queued > 0
                ? `● Offline — ${queued} payment${queued > 1 ? 's' : ''} queued`
                : '● Offline — payments will queue';
        } else if (queued > 0) {
            bg = '#1f2a00'; fg = '#c3f400';
            text = `↻ Syncing ${queued} payment${queued > 1 ? 's' : ''}…`;
        } else {
            show = false;
        }

        el.style.display = show ? 'flex' : 'none';
        el.style.background = bg;
        el.style.color = fg;
        el.textContent = text;
    }

    document.addEventListener('collector:sync-status', (e) => paint(e.detail));
    // Seed initial state.
    paint({ online: navigator.onLine, queued: 0, needsAttention: 0 });
}

window.collectorApp = { db, flush, refreshRoute, queuePayment, uuid };

mountIndicator();
registerServiceWorker();
startSync();
