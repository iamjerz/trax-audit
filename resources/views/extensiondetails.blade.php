<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
@include('partials.header')
<style>
    .hist-diff { font-size: 12px; white-space: pre-wrap; margin: 0; }
    .hist-diff .old { color: #d63939; }
    .hist-diff .new { color: #2fb344; }
</style>
<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Extension Details</h4>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-primary" id="add-entry-btn">
                                <i class="bx bx-plus align-middle"></i> Add Entry
                            </button>
                        </div>

                        <table class="table table-striped table-centered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Version</th>
                                        <th>Item ID</th>
                                        <th>Status</th>
                                        <th class="text-end" style="min-width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($details as $d)
                                        <tr>
                                            <td>{{ $d->version }}</td>
                                            <td>{{ $d->item_id }}</td>
                                            <td>
                                                <span class="badge {{ $d->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                    {{ ucfirst($d->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button type="button" class="dropdown-item ext-edit-btn"
                                                                data-id="{{ $d->id }}"
                                                                data-version="{{ $d->version }}"
                                                                data-item_id="{{ $d->item_id }}"
                                                                data-status="{{ $d->status }}">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item ext-history-btn"
                                                                data-id="{{ $d->id }}"
                                                                data-item_id="{{ $d->item_id }}">
                                                                <i class="bx bx-history me-1"></i> History
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No extension details found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Add / Edit modal ===== --}}
    <div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="entryModalTitle">Add Extension Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="entry-error" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="entry-id">
                    <div class="mb-3">
                        <label class="form-label" for="entry-version">Version</label>
                        <input type="text" class="form-control" id="entry-version" placeholder="e.g. 1.0.3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="entry-item_id">Item ID</label>
                        <input type="text" class="form-control" id="entry-item_id" placeholder="Extension item ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="entry-status">Status</label>
                        <select class="form-select" id="entry-status">
                            <option value="active">active</option>
                            <option value="inactive">inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="entry-save-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== History modal ===== --}}
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change History <span class="text-muted font-size-13" id="history-subtitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="history-body">
                        <div class="text-center text-muted py-3">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.script')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const entryModal = new bootstrap.Modal(document.getElementById('entryModal'));
        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));

        function showError(msg) {
            const box = document.getElementById('entry-error');
            box.textContent = msg;
            box.classList.remove('d-none');
        }

        // ---- Add ----
        document.getElementById('add-entry-btn').addEventListener('click', function () {
            document.getElementById('entryModalTitle').textContent = 'Add Extension Detail';
            document.getElementById('entry-id').value = '';
            document.getElementById('entry-version').value = '';
            document.getElementById('entry-item_id').value = '';
            document.getElementById('entry-status').value = 'active';
            document.getElementById('entry-error').classList.add('d-none');
            entryModal.show();
        });

        // ---- Edit ----
        document.querySelectorAll('.ext-edit-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('entryModalTitle').textContent = 'Edit Extension Detail';
                document.getElementById('entry-id').value = btn.dataset.id;
                document.getElementById('entry-version').value = btn.dataset.version;
                document.getElementById('entry-item_id').value = btn.dataset.item_id;
                document.getElementById('entry-status').value = btn.dataset.status;
                document.getElementById('entry-error').classList.add('d-none');
                entryModal.show();
            });
        });

        // ---- Save (add or update) ----
        document.getElementById('entry-save-btn').addEventListener('click', function () {
            const id = document.getElementById('entry-id').value;
            const payload = {
                version: document.getElementById('entry-version').value.trim(),
                item_id: document.getElementById('entry-item_id').value.trim(),
                status:  document.getElementById('entry-status').value
            };

            if (!payload.version || !payload.item_id) {
                showError('Version and Item ID are required.');
                return;
            }

            const url = id ? `/extension-details/${id}` : '/extension-details';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async r => { const res = await r.json(); if (!r.ok) throw res; return res; })
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    showError(res.message || 'Save failed.');
                }
            })
            .catch(err => {
                showError(err.message || 'Something went wrong.');
            });
        });

        // ---- History ----
        const eventColors = {
            created: 'primary', updated: 'info', deleted: 'danger'
        };

        document.querySelectorAll('.ext-history-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.dataset.id;
                document.getElementById('history-subtitle').textContent = '— ' + btn.dataset.item_id;
                document.getElementById('history-body').innerHTML = '<div class="text-center text-muted py-3">Loading...</div>';
                historyModal.show();

                fetch(`/extension-details/${id}/history`, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(res => renderHistory(res.data || []))
                    .catch(() => {
                        document.getElementById('history-body').innerHTML =
                            '<div class="text-center text-danger py-3">Failed to load history.</div>';
                    });
            });
        });

        function renderDiff(oldV, newV) {
            oldV = oldV || {};
            newV = newV || {};
            const keys = [...new Set([...Object.keys(oldV), ...Object.keys(newV)])];
            if (!keys.length) return '<span class="text-muted">—</span>';
            let out = '<pre class="hist-diff">';
            keys.forEach(k => {
                const o = oldV[k];
                const n = newV[k];
                out += `<strong>${k}:</strong> `;
                if (o !== undefined && o !== null) out += `<span class="old">${o}</span> → `;
                out += `<span class="new">${n ?? ''}</span>\n`;
            });
            out += '</pre>';
            return out;
        }

        function renderHistory(logs) {
            if (!logs.length) {
                document.getElementById('history-body').innerHTML =
                    '<div class="text-center text-muted py-3">No history for this entry.</div>';
                return;
            }

            let rows = '';
            logs.forEach(log => {
                const color = eventColors[log.event] || 'dark';
                const when = log.created_at ? new Date(log.created_at).toLocaleString() : '';
                rows += `
                    <tr>
                        <td class="font-size-12">${when}</td>
                        <td>${log.actor_name || '—'}</td>
                        <td><span class="badge bg-${color}">${log.event}</span></td>
                        <td>${renderDiff(log.old_values, log.new_values)}</td>
                    </tr>`;
            });

            document.getElementById('history-body').innerHTML = `
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>When</th><th>Modified By</th><th>Event</th><th>Changes</th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        }
    </script>
</body>

</html>
