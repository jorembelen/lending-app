/*
 * Minimal IndexedDB wrapper for the collector PWA.
 *
 * Two object stores (build spec 9.8):
 *   - cached_route:     today's borrower list + per-borrower detail/schedule,
 *                       keyed by date. Written once when the route is fetched
 *                       online; read thereafter regardless of connectivity.
 *   - pending_payments: queue of payment records keyed by a client-generated
 *                       idempotency key, each with a `synced` state flag.
 *
 * IndexedDB (not localStorage) is used deliberately: structured, larger, and
 * more durable. We keep cached data minimal — today's route + pending payments
 * only — and purge stale routes to respect tight quotas on budget devices.
 */
const DB_NAME = 'collector';
const DB_VERSION = 1;

let dbPromise = null;

function openDB() {
    if (dbPromise) {
        return dbPromise;
    }
    dbPromise = new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('cached_route')) {
                db.createObjectStore('cached_route', { keyPath: 'date' });
            }
            if (!db.objectStoreNames.contains('pending_payments')) {
                const store = db.createObjectStore('pending_payments', { keyPath: 'idempotency_key' });
                store.createIndex('state', 'state', { unique: false });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
    return dbPromise;
}

function promisify(request) {
    return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function withStore(name, mode, fn) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(name, mode);
        const store = tx.objectStore(name);
        let result;
        Promise.resolve(fn(store))
            .then((value) => { result = value; })
            .catch(reject);
        tx.oncomplete = () => resolve(result);
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error);
    });
}

function todayKey() {
    // Local date (YYYY-MM-DD) so "today's route" matches the collector's day.
    const d = new Date();
    const tzAdjusted = new Date(d.getTime() - d.getTimezoneOffset() * 60000);
    return tzAdjusted.toISOString().slice(0, 10);
}

export const db = {
    todayKey,

    /** Persist today's fetched route, purging any other day's cached route. */
    async saveRoute(routeData) {
        const date = routeData.date || todayKey();
        // Read in its own transaction, then write everything synchronously in a
        // second one — issuing requests across an await would let the write
        // transaction auto-commit early on some IndexedDB implementations.
        const existing = await withStore('cached_route', 'readonly', (store) => promisify(store.getAll()));
        await withStore('cached_route', 'readwrite', (store) => {
            existing.forEach((row) => {
                if (row.date !== date) {
                    store.delete(row.date);
                }
            });
            store.put({ ...routeData, date });
        });
    },

    /** Read a cached route (defaults to today). Returns null if absent. */
    async getRoute(date = todayKey()) {
        return withStore('cached_route', 'readonly', (store) => promisify(store.get(date)))
            .then((row) => row || null);
    },

    /** Resolve a borrower from the cached route by id. */
    async getBorrower(borrowerId) {
        const route = await this.getRoute();
        if (!route) { return null; }
        return route.borrowers.find((b) => String(b.borrower_id) === String(borrowerId)) || null;
    },

    /** Resolve a borrower from the cached route by scanned QR / code. */
    async resolveByQr(value) {
        const route = await this.getRoute();
        if (!route) { return null; }
        return route.borrowers.find(
            (b) => b.qr_reference === value || b.borrower_code === value
        ) || null;
    },

    /** Add a payment to the queue. Throws if the key already exists. */
    async queuePayment(record) {
        await withStore('pending_payments', 'readwrite', (store) => store.add(record));
        return record;
    },

    async getPayment(key) {
        return withStore('pending_payments', 'readonly', (store) => promisify(store.get(key)))
            .then((row) => row || null);
    },

    /** All payments still needing a sync attempt (queued, not synced/rejected). */
    async getQueued() {
        return withStore('pending_payments', 'readonly', (store) => promisify(store.getAll()))
            .then((rows) => rows.filter((r) => r.state === 'queued'));
    },

    async getAllPayments() {
        return withStore('pending_payments', 'readonly', (store) => promisify(store.getAll()));
    },

    async updatePayment(key, patch) {
        const row = await withStore('pending_payments', 'readonly', (store) => promisify(store.get(key)));
        if (!row) { return null; }
        const next = { ...row, ...patch };
        await withStore('pending_payments', 'readwrite', (store) => { store.put(next); });
        return next;
    },

    /** Patch the cached borrower's balance so the UI reflects a synced payment. */
    async applyCachedBalance(loanId, remainingBalance) {
        const route = await this.getRoute();
        if (!route) { return; }
        let changed = false;
        route.borrowers.forEach((b) => {
            if (String(b.loan_id) === String(loanId)) {
                b.remaining_balance = remainingBalance;
                changed = true;
            }
        });
        if (changed) {
            await this.saveRoute(route);
        }
    },

    /** Drop synced payments older than the retention window (default 24h). */
    async purgeSynced(maxAgeMs = 24 * 60 * 60 * 1000) {
        const cutoff = Date.now() - maxAgeMs;
        const rows = await withStore('pending_payments', 'readonly', (store) => promisify(store.getAll()));
        await withStore('pending_payments', 'readwrite', (store) => {
            rows.forEach((row) => {
                if (row.state === 'synced' && (row.synced_at || 0) < cutoff) {
                    store.delete(row.idempotency_key);
                }
            });
        });
    },
};
