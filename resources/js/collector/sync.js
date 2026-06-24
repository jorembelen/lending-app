/*
 * Payment sync engine for the collector PWA.
 *
 * Implements the queue-first pattern (build spec 9.10): every payment is
 * written to pending_payments first, then this engine flushes the queue to the
 * collector payments API whenever connectivity is detected — on app open, on
 * screen focus, on a short interval, and on the browser `online` event. The
 * idempotency key on each record makes repeated flushes safe (the server
 * silently ignores duplicates), so a payment that was half-synced when the app
 * was killed simply re-syncs to the same outcome on the next attempt.
 *
 * Background Sync API is used as a bonus when available, but the proactive
 * checks are the reliable baseline for the budget Android devices this app
 * targets, where the OS aggressively kills background service workers.
 */
import { db } from './db.js';

const PAYMENTS_URL = '/collector/api/payments';
const ROUTE_URL = '/collector/api/route';
const INTERVAL_MS = 45000; // 45s proactive flush while the app is open

let flushing = false;
let intervalId = null;

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function emitStatus(extra = {}) {
    db.getAllPayments().then((rows) => {
        const queued = rows.filter((r) => r.state === 'queued').length;
        const needsAttention = rows.filter((r) => r.state === 'needs_attention').length;
        document.dispatchEvent(new CustomEvent('collector:sync-status', {
            detail: { online: navigator.onLine, queued, needsAttention, ...extra },
        }));
    });
}

/**
 * Attempt to sync a single queued payment.
 * @returns {Promise<'synced'|'rejected'|'retry'>}
 */
async function syncOne(record) {
    let response;
    try {
        response = await fetch(PAYMENTS_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                idempotency_key: record.idempotency_key,
                loan_id: record.loan_id,
                amount: record.amount,
                collected_at: record.collected_at,
                latitude: record.latitude ?? null,
                longitude: record.longitude ?? null,
                device_identifier: record.device_identifier ?? null,
            }),
        });
    } catch (err) {
        // Pure connectivity failure — keep it queued, try again later.
        return 'retry';
    }

    if (response.ok) {
        const data = await response.json().catch(() => ({}));
        await db.updatePayment(record.idempotency_key, {
            state: 'synced',
            synced_at: Date.now(),
            server_payment_id: data.payment_id ?? null,
            server_status: data.status ?? 'recorded',
        });
        if (data.loan_id != null && data.remaining_balance != null) {
            await db.applyCachedBalance(data.loan_id, data.remaining_balance);
        }
        return 'synced';
    }

    if (response.status === 419 || response.status === 401 || response.status >= 500) {
        // Session expired / CSRF / server hiccup: treat as transient, retry.
        return 'retry';
    }

    // Real, non-connectivity rejection (e.g. loan closed, not found): the
    // collector must see this as "needs attention", not silent retry forever.
    const data = await response.json().catch(() => ({}));
    await db.updatePayment(record.idempotency_key, {
        state: 'needs_attention',
        error_reason: data.reason ?? 'rejected',
        error_message: data.message ?? 'Payment was rejected by the server.',
    });
    return 'rejected';
}

export async function flush() {
    if (flushing || !navigator.onLine) {
        return;
    }
    flushing = true;
    let syncedCount = 0;
    try {
        const queued = await db.getQueued();
        for (const record of queued) {
            const result = await syncOne(record);
            if (result === 'synced') {
                syncedCount += 1;
            } else if (result === 'retry') {
                // Stop early on the first connectivity failure to avoid hammering.
                break;
            }
        }
        await db.purgeSynced();
    } finally {
        flushing = false;
        emitStatus({ justSynced: syncedCount });
    }
    return syncedCount;
}

/** Fetch and cache today's route from the server (online only). */
export async function refreshRoute() {
    if (!navigator.onLine) {
        return null;
    }
    try {
        const response = await fetch(ROUTE_URL, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) {
            return null;
        }
        const data = await response.json();
        await db.saveRoute(data);
        return data;
    } catch (err) {
        return null;
    }
}

export function startSync() {
    // Morning routine: flush leftover payments, then refresh today's route.
    flush().then(() => refreshRoute());

    if (intervalId === null) {
        intervalId = setInterval(() => flush(), INTERVAL_MS);
    }

    window.addEventListener('online', () => {
        emitStatus();
        flush();
    });
    window.addEventListener('offline', () => emitStatus());

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            flush();
        }
    });

    // Re-flush when a Livewire navigation lands on a new collector screen.
    document.addEventListener('livewire:navigated', () => flush());

    emitStatus();
}
