// Client & Carrier Codes admin page. Both tables render via Grid.js (same
// library/request contract as /monitoring-ticket): each grid pulls paged +
// searched data from its own *-data endpoint rather than the page rendering
// ~10k rows server-side. Add/Edit share one modal per table (title + submit
// action swap depending on whether an id is currently being edited);
// Delete confirms then calls the grid's forceRender() to refresh in place.

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function fetchJson(url, method, body) {
        return fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: body ? JSON.stringify(body) : undefined
        }).then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        });
    }

    function errorMessage(err) {
        if (err && err.errors) return Object.values(err.errors).flat().join('\n');
        return (err && err.message) || 'Something went wrong';
    }

    // Splits a large bulk-add paste into fixed-size chunks sent one at a
    // time (never in parallel — duplicate-detection reads the DB fresh on
    // each request, so concurrent requests could both miss the same
    // not-yet-committed duplicate and insert it twice). At ~2,000 rows per
    // request, even a very large paste (tens of thousands of rows) stays
    // well clear of PHP's post_max_size and keeps each request fast, rather
    // than sending everything in one giant request.
    const BULK_BATCH_SIZE = 2000;

    function chunkArray(arr, size) {
        const out = [];
        for (let i = 0; i < arr.length; i += size) {
            out.push(arr.slice(i, i + size));
        }
        return out;
    }

    // Runs one bulk-add request per batch, sequentially, updating the
    // button label with progress. If a batch fails, stops there and
    // reports how many rows made it in before the error — re-running with
    // the same input is safe since already-added rows are skipped as
    // duplicates, not re-inserted.
    async function submitBulkInBatches({ items, endpoint, buildPayload, btn, noun }) {
        const batches = chunkArray(items, BULK_BATCH_SIZE);
        const originalLabel = btn.textContent;
        btn.disabled = true;
        let totalAdded = 0;
        let totalSkipped = 0;

        try {
            for (let i = 0; i < batches.length; i++) {
                btn.textContent = batches.length > 1
                    ? `Uploading ${i + 1}/${batches.length}...`
                    : 'Uploading...';
                const res = await fetchJson(endpoint, 'POST', buildPayload(batches[i]));
                totalAdded += res.added || 0;
                totalSkipped += res.skipped || 0;
            }
            notifySuccess(
                `Added ${totalAdded} ${noun}(s)` + (totalSkipped > 0 ? `, skipped ${totalSkipped} duplicate(s).` : '.')
            );
            return true;
        } catch (err) {
            notifyError(
                (totalAdded > 0 ? `Added ${totalAdded} before this error: ` : '') + errorMessage(err)
            );
            return false;
        } finally {
            btn.disabled = false;
            btn.textContent = originalLabel;
        }
    }

    function paginationServerConfig() {
        return {
            url: (prev, page, limit) => {
                const url = new URL(prev, window.location.origin);
                url.searchParams.set('limit', limit);
                url.searchParams.set('offset', page * limit);
                return `${url.pathname}?${url.searchParams.toString()}`;
            }
        };
    }

    // ---- Client Codes grid ---------------------------------------------
    const clientCodeCache = new Map(); // id -> { id, name, added_by }

    const clientGrid = new gridjs.Grid({
        columns: [
            'Name',
            'Added By',
            {
                name: 'Actions',
                sort: false,
                formatter: (id) => gridjs.html(`
                    <button type="button" class="btn btn-sm btn-link p-0 me-2" onclick="editClientCode(${id})" title="Edit"><i class="bx bx-edit font-size-16"></i></button>
                    <button type="button" class="btn btn-sm btn-link p-0 text-danger" onclick="deleteClientCode(${id})" title="Delete"><i class="bx bx-trash font-size-16"></i></button>
                `)
            },
        ],
        server: {
            url: '/client-codes/data',
            then: data => data.data.map(item => {
                clientCodeCache.set(item.id, item);
                return [item.name, item.added_by || '', item.id];
            }),
            total: data => data.total
        },
        pagination: {
            enabled: true,
            limit: 10,
            server: paginationServerConfig()
        },
        search: {
            debounceTimeout: 500,
            server: {
                url: (prev, keyword) => `/client-codes/data?limit=10&offset=0&search=${encodeURIComponent(keyword)}`
            }
        },
        sort: false
    });
    clientGrid.render(document.getElementById('table-client-codes'));

    // ---- Carrier Codes grid ---------------------------------------------
    const carrierCodeCache = new Map(); // id -> { id, name, client_name, added_by }

    const carrierGrid = new gridjs.Grid({
        columns: [
            'Name',
            'Client Name',
            'Added By',
            {
                name: 'Actions',
                sort: false,
                formatter: (id) => gridjs.html(`
                    <button type="button" class="btn btn-sm btn-link p-0 me-2" onclick="editCarrierCode(${id})" title="Edit"><i class="bx bx-edit font-size-16"></i></button>
                    <button type="button" class="btn btn-sm btn-link p-0 text-danger" onclick="deleteCarrierCode(${id})" title="Delete"><i class="bx bx-trash font-size-16"></i></button>
                `)
            },
        ],
        server: {
            url: '/carrier-codes/data',
            then: data => data.data.map(item => {
                carrierCodeCache.set(item.id, item);
                return [item.name, item.client_name || '', item.added_by || '', item.id];
            }),
            total: data => data.total
        },
        pagination: {
            enabled: true,
            limit: 10,
            server: paginationServerConfig()
        },
        search: {
            debounceTimeout: 500,
            server: {
                url: (prev, keyword) => `/carrier-codes/data?limit=10&offset=0&search=${encodeURIComponent(keyword)}`
            }
        },
        sort: false
    });
    carrierGrid.render(document.getElementById('table-carrier-codes'));

    // ---- Client Name picker on the Carrier Code modal -------------------
    // Searchable over the client codes already added (Choices.js, same lib
    // used elsewhere in this app). With ~10k client codes it can't preload
    // them all, so it queries /client-codes/search as the admin types.
    let carrierClientChoices = null;
    let loadClientCodeChoices = null;

    const carrierClientNameSelect = document.getElementById('new-carrier-code-client-name');
    if (carrierClientNameSelect && typeof Choices !== 'undefined') {
        carrierClientChoices = new Choices(carrierClientNameSelect, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            allowHTML: false,
            searchResultLimit: 20,
            noResultsText: 'No matching client codes',
            noChoicesText: 'Type to search client codes',
        });

        loadClientCodeChoices = (term, extra) => {
            fetch(`/client-codes/search?q=${encodeURIComponent(term || '')}`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(body => {
                    const names = Array.isArray(body.data) ? body.data : [];
                    if (extra && !names.includes(extra)) names.unshift(extra);
                    const options = [
                        { value: '', label: '-- Select Client Code --' },
                        ...names.map(n => ({ value: n, label: n })),
                    ];
                    carrierClientChoices.setChoices(options, 'value', 'label', true);
                    if (extra) carrierClientChoices.setChoiceByValue(extra);
                })
                .catch(() => {});
        };

        let clientSearchDebounce = null;
        carrierClientNameSelect.addEventListener('search', function (e) {
            clearTimeout(clientSearchDebounce);
            const term = e.detail.value;
            clientSearchDebounce = setTimeout(() => loadClientCodeChoices(term), 250);
        });

        const addCarrierModalEl = document.getElementById('addCarrierCodeModal');
        if (addCarrierModalEl) {
            addCarrierModalEl.addEventListener('shown.bs.modal', () => {
                // editCarrierCode() already seeds the current value itself;
                // only reseed the plain default list when adding fresh.
                if (editingCarrierCodeId === null) loadClientCodeChoices('');
            });
        }
    }

    // ---- Add / Edit Client Code modal -----------------------------------
    let editingClientCodeId = null;
    const clientCodeModalEl = document.getElementById('addClientCodeModal');
    const clientCodeModal = clientCodeModalEl ? new bootstrap.Modal(clientCodeModalEl) : null;

    const openAddClientBtn = document.getElementById('open-add-client-code-btn');
    if (openAddClientBtn) {
        openAddClientBtn.addEventListener('click', () => {
            editingClientCodeId = null;
            document.getElementById('client-code-modal-title').textContent = 'Add Client Code';
            document.getElementById('new-client-code-name').value = '';
            clientCodeModal && clientCodeModal.show();
        });
    }

    window.editClientCode = function (id) {
        const rec = clientCodeCache.get(id);
        if (!rec) return;
        editingClientCodeId = id;
        document.getElementById('client-code-modal-title').textContent = 'Edit Client Code';
        document.getElementById('new-client-code-name').value = rec.name;
        clientCodeModal && clientCodeModal.show();
    };

    window.deleteClientCode = function (id) {
        const rec = clientCodeCache.get(id);
        const label = rec ? rec.name : id;
        if (!confirm(`Delete "${label}"? This cannot be undone.`)) return;
        fetchJson(`/client-codes/${id}`, 'DELETE')
            .then(res => {
                notifySuccess(res.message || 'Deleted.');
                clientGrid.forceRender();
            })
            .catch(err => notifyError(errorMessage(err)));
    };

    const addClientBtn = document.getElementById('add-client-code-btn');
    if (addClientBtn) {
        addClientBtn.addEventListener('click', function () {
            const name = document.getElementById('new-client-code-name').value.trim();
            if (!name) {
                notifyWarning('Enter a client code name.');
                return;
            }
            const isEdit = editingClientCodeId !== null;
            const url = isEdit ? `/client-codes/${editingClientCodeId}` : '/client-codes';
            fetchJson(url, isEdit ? 'PUT' : 'POST', { name })
                .then(res => {
                    notifySuccess(res.message);
                    clientCodeModal && clientCodeModal.hide();
                    clientGrid.forceRender();
                })
                .catch(err => notifyError(errorMessage(err)));
        });
    }

    // ---- Bulk Add Client Codes ------------------------------------------
    const bulkClientModalEl = document.getElementById('bulkAddClientCodeModal');
    const bulkClientModal = bulkClientModalEl ? new bootstrap.Modal(bulkClientModalEl) : null;

    const openBulkClientBtn = document.getElementById('open-bulk-client-code-btn');
    if (openBulkClientBtn) {
        openBulkClientBtn.addEventListener('click', () => {
            document.getElementById('bulk-client-codes-text').value = '';
            bulkClientModal && bulkClientModal.show();
        });
    }

    const bulkAddClientBtn = document.getElementById('bulk-add-client-codes-btn');
    if (bulkAddClientBtn) {
        bulkAddClientBtn.addEventListener('click', async function () {
            const raw = document.getElementById('bulk-client-codes-text').value;
            const names = raw.split('\n').map(s => s.trim()).filter(Boolean);
            if (!names.length) {
                notifyWarning('Paste at least one client code.');
                return;
            }
            const ok = await submitBulkInBatches({
                items: names,
                endpoint: '/client-codes/bulk',
                buildPayload: (batch) => ({ names: batch }),
                btn: this,
                noun: 'client code',
            });
            clientGrid.forceRender();
            if (ok) {
                bulkClientModal && bulkClientModal.hide();
            }
        });
    }

    // ---- Bulk Add Carrier Codes ------------------------------------------
    const bulkCarrierModalEl = document.getElementById('bulkAddCarrierCodeModal');
    const bulkCarrierModal = bulkCarrierModalEl ? new bootstrap.Modal(bulkCarrierModalEl) : null;

    const openBulkCarrierBtn = document.getElementById('open-bulk-carrier-code-btn');
    if (openBulkCarrierBtn) {
        openBulkCarrierBtn.addEventListener('click', () => {
            document.getElementById('bulk-carrier-codes-text').value = '';
            bulkCarrierModal && bulkCarrierModal.show();
        });
    }

    const bulkAddCarrierBtn = document.getElementById('bulk-add-carrier-codes-btn');
    if (bulkAddCarrierBtn) {
        bulkAddCarrierBtn.addEventListener('click', async function () {
            const raw = document.getElementById('bulk-carrier-codes-text').value;
            const rows = raw.split('\n')
                .map(line => line.trim())
                .filter(Boolean)
                .map(line => {
                    const idx = line.indexOf(',');
                    if (idx === -1) return { name: line, client_name: null };
                    return {
                        name: line.slice(0, idx).trim(),
                        client_name: line.slice(idx + 1).trim() || null,
                    };
                })
                .filter(r => r.name);
            if (!rows.length) {
                notifyWarning('Paste at least one carrier code.');
                return;
            }
            const ok = await submitBulkInBatches({
                items: rows,
                endpoint: '/carrier-codes/bulk',
                buildPayload: (batch) => ({ rows: batch }),
                btn: this,
                noun: 'carrier code',
            });
            carrierGrid.forceRender();
            if (ok) {
                bulkCarrierModal && bulkCarrierModal.hide();
            }
        });
    }

    // ---- Add / Edit Carrier Code modal -----------------------------------
    let editingCarrierCodeId = null;
    const carrierCodeModalEl = document.getElementById('addCarrierCodeModal');
    const carrierCodeModal = carrierCodeModalEl ? new bootstrap.Modal(carrierCodeModalEl) : null;

    const openAddCarrierBtn = document.getElementById('open-add-carrier-code-btn');
    if (openAddCarrierBtn) {
        openAddCarrierBtn.addEventListener('click', () => {
            editingCarrierCodeId = null;
            document.getElementById('carrier-code-modal-title').textContent = 'Add Carrier Code';
            document.getElementById('new-carrier-code-name').value = '';
            carrierCodeModal && carrierCodeModal.show();
        });
    }

    window.editCarrierCode = function (id) {
        const rec = carrierCodeCache.get(id);
        if (!rec) return;
        editingCarrierCodeId = id;
        document.getElementById('carrier-code-modal-title').textContent = 'Edit Carrier Code';
        document.getElementById('new-carrier-code-name').value = rec.name;
        carrierCodeModal && carrierCodeModal.show();
        if (loadClientCodeChoices) {
            loadClientCodeChoices('', rec.client_name || '');
        }
    };

    window.deleteCarrierCode = function (id) {
        const rec = carrierCodeCache.get(id);
        const label = rec ? rec.name : id;
        if (!confirm(`Delete "${label}"? This cannot be undone.`)) return;
        fetchJson(`/carrier-codes/${id}`, 'DELETE')
            .then(res => {
                notifySuccess(res.message || 'Deleted.');
                carrierGrid.forceRender();
            })
            .catch(err => notifyError(errorMessage(err)));
    };

    const addCarrierBtn = document.getElementById('add-carrier-code-btn');
    if (addCarrierBtn) {
        addCarrierBtn.addEventListener('click', function () {
            const name = document.getElementById('new-carrier-code-name').value.trim();
            const clientName = document.getElementById('new-carrier-code-client-name').value.trim();
            if (!name) {
                notifyWarning('Enter a carrier code name.');
                return;
            }
            const isEdit = editingCarrierCodeId !== null;
            const url = isEdit ? `/carrier-codes/${editingCarrierCodeId}` : '/carrier-codes';
            fetchJson(url, isEdit ? 'PUT' : 'POST', { name, client_name: clientName })
                .then(res => {
                    notifySuccess(res.message);
                    carrierCodeModal && carrierCodeModal.hide();
                    carrierGrid.forceRender();
                })
                .catch(err => notifyError(errorMessage(err)));
        });
    }
});
