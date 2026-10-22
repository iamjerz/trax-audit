<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css">
@include('partials.header')
<style>
    /* Date Range Picker (daterangepicker.com) theming — same as /recon-ticket. */
    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background-color: #556ee6 !important;
    }

    .daterangepicker td.in-range {
        background-color: rgba(85, 110, 230, 0.15) !important;
    }

    .daterangepicker .ranges li.active {
        background-color: #556ee6 !important;
        color: #fff !important;
    }

    .daterangepicker .applyBtn {
        background-color: #556ee6 !important;
        border-color: #556ee6 !important;
    }
</style>

<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">QA Monitoring Ticket</h5>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Filters Card -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Filters</h6>
                                <div class="row g-3">
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Audit ID</label>
                                        <input type="text" id="filter-audit-id" class="form-control" placeholder="Search audit ID...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Invoice ID</label>
                                        <input type="text" id="filter-invoice-id" class="form-control" placeholder="Search invoice ID...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Coachee</label>
                                        <select id="filter-coachee" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Coach</label>
                                        <select id="filter-coach" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Supervisor/Manager</label>
                                        <select id="filter-manager-scope" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                            <option value="my_team">My Team</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Client Code</label>
                                        <select id="filter-client-code" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Carrier</label>
                                        <select id="filter-carrier-name" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Exception Status</label>
                                        <select id="filter-exception-status" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label class="form-label">Date Range (Audit Date)</label>
                                        <input type="text" id="filter-date-range" class="form-control" placeholder="All dates" readonly>
                                        <input type="hidden" id="filter-date-from">
                                        <input type="hidden" id="filter-date-to">
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button id="btn-reset-filters" class="btn btn-outline-secondary btn-sm">
                                            <i class="mdi mdi-refresh"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table" id="table-recon"></div>
                            </div>
                        </div>

                    </div>

                </div>


            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Date Range Picker (daterangepicker.com) — needs jQuery (above) + Moment.js loaded first -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script>
        // ============================================================
        // Initialize Choices.js on all .dropdown-choices selects
        // ============================================================
        const choicesInstances = {};
        document.querySelectorAll('.dropdown-choices').forEach((el) => {
            choicesInstances[el.id] = new Choices(el, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                allowHTML: false
            });
        });

        const limit = 10;
        const canDelete = @json($canDelete ?? false);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Holds current filter values
        const filters = {
            audit_id: '',
            invoice_id: '',
            coachee: '',
            coach: '',
            manager_scope: '',
            client_code: '',
            carrier_name: '',
            exception_status: '',
            date_from: '',
            date_to: ''
        };

        // Build query string from filters + pagination/search
        function buildQuery(extra = {}) {
            const params = new URLSearchParams();
            params.set('limit', extra.limit ?? limit);
            params.set('offset', extra.offset ?? 0);

            if (extra.search) params.set('search', extra.search);
            if (filters.audit_id) params.set('audit_id', filters.audit_id);
            if (filters.invoice_id) params.set('invoice_id', filters.invoice_id);
            if (filters.coachee) params.set('coachee', filters.coachee);
            if (filters.coach) params.set('coach', filters.coach);
            if (filters.manager_scope) params.set('manager_scope', filters.manager_scope);
            if (filters.client_code) params.set('client_code', filters.client_code);
            if (filters.carrier_name) params.set('carrier_name', filters.carrier_name);
            if (filters.exception_status) params.set('exception_status', filters.exception_status);
            if (filters.date_from) params.set('date_from', filters.date_from);
            if (filters.date_to) params.set('date_to', filters.date_to);

            return `/monitoring-data?${params.toString()}`;
        }

        const columns = [
            {
                name: 'Audit ID',
                formatter: (cell) => {
                    const safe = String(cell).replace(/"/g, '&quot;');
                    return gridjs.html(`
                <a href="/ticket/view/${safe}" class="text-primary fw-bold">
                    ${safe}
                </a>
                `);
                }
            },
            'Invoice ID',
            'LDA',
            'Carrier',
            {
                name: 'Audit Date',
                formatter: (cell) => {
                    if (!cell) return '';

                    const date = new Date(cell);
                    if (isNaN(date)) return cell;

                    return date.toLocaleDateString('en-PH', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            },
            'Created By',
        ];

        if (canDelete) {
            columns.push({
                name: 'Actions',
                sort: false,
                formatter: (cell) => {
                    const safe = String(cell).replace(/"/g, '&quot;');
                    return gridjs.html(`
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="deleteAudit('${safe}')">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    `);
                }
            });
        }

        const grid = new gridjs.Grid({
            columns: columns,

            server: {
                url: buildQuery(),

                then: data => data.data.map(item => {
                    const row = [
                        item.audit_id,
                        item.invoice_id || '',
                        item.lda_name || '',
                        item.carrier_name || '',
                        item.audit_date_1,
                        item.created_by_name || ''
                    ];
                    if (canDelete) row.push(item.audit_id);
                    return row;
                }),

                total: data => data.total
            },

            pagination: {
                enabled: true,
                limit: 10,
                server: {
                    url: (prev, page, limit) => {
                        return buildQuery({
                            limit,
                            offset: page * limit
                        });
                    }
                }
            },

            search: {
                debounceTimeout: 500,
                server: {
                    url: (prev, keyword) => {
                        return buildQuery({
                            search: keyword
                        });
                    }
                }
            },

            sort: false
        });

        grid.render(document.getElementById('table-recon'));

        function deleteAudit(id) {
            if (!confirm('Delete audit ' + id + '? This will also remove its scores and cannot be undone.')) {
                return;
            }

            fetch('/monitoring-ticket/' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json().then(body => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok || !body.success) {
                    notifyError(body.message || 'Failed to delete the record.');
                    return;
                }
                notifySuccess(body.message || 'Deleted successfully.');
                grid.forceRender();
            })
            .catch(() => notifyError('Failed to delete the record.'));
        }

        // ============================================================
        // Filter wiring
        // ============================================================

        function debounce(fn, delay = 400) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn(...args), delay);
            };
        }

        function reloadGrid() {
            grid.updateConfig({
                server: {
                    url: buildQuery(),
                    then: data => data.data.map(item => {
                        const row = [
                            item.audit_id,
                            item.invoice_id || '',
                            item.lda_name || '',
                            item.carrier_name || '',
                            item.audit_date_1,
                            item.created_by_name || ''
                        ];
                        if (canDelete) row.push(item.audit_id);
                        return row;
                    }),
                    total: data => data.total
                }
            }).forceRender();
        }

        // Audit ID / Invoice ID (debounced text inputs)
        document.getElementById('filter-audit-id').addEventListener('input', debounce((e) => {
            filters.audit_id = e.target.value.trim();
            reloadGrid();
        }, 500));

        document.getElementById('filter-invoice-id').addEventListener('input', debounce((e) => {
            filters.invoice_id = e.target.value.trim();
            reloadGrid();
        }, 500));

        // Selects - reload immediately on change
        // Note: Choices.js dispatches 'change' on the underlying <select>, so this works for both.
        // Date range is handled separately below (daterangepicker.com), since
        // filter-date-from/filter-date-to are now hidden inputs driven by the
        // picker's own events, not something a user types/changes directly.
        ['filter-coachee', 'filter-coach', 'filter-manager-scope', 'filter-client-code', 'filter-carrier-name', 'filter-exception-status'].forEach(id => {
            document.getElementById(id).addEventListener('change', (e) => {
                const key = id.replace('filter-', '').replace(/-/g, '_');
                filters[key] = e.target.value;
                reloadGrid();
            });
        });

        // ============================================================
        // Date Range picker (daterangepicker.com) — same widget/config as
        // the Recon ticket list: presets + calendars always visible
        // together, one click applies (preset or manual range), theme
        // matched to #556ee6.
        // ============================================================
        function monitoringTicketDaysAgo(n) {
            return moment().startOf('day').subtract(n, 'days');
        }

        function monitoringTicketMonthsAgo(n) {
            return moment().startOf('day').subtract(n, 'months');
        }

        const dateRangeInput = $('#filter-date-range');

        dateRangeInput.daterangepicker({
            autoUpdateInput: false, // keep the "All dates" placeholder until a range is actually chosen
            autoApply: true, // no separate confirm click, for both presets and manual picks
            alwaysShowCalendars: true, // presets + calendars visible together
            maxDate: moment(), // this list only ever has past audit data
            locale: {
                format: 'MMM D, YYYY',
                separator: ' - '
            },
            ranges: {
                'Today': [moment().startOf('day'), moment().startOf('day')],
                'Yesterday': [monitoringTicketDaysAgo(1), monitoringTicketDaysAgo(1)],
                'Last 7 days': [monitoringTicketDaysAgo(7), moment().startOf('day')],
                'Last 30 days': [monitoringTicketDaysAgo(30), moment().startOf('day')],
                'Last 6 months': [monitoringTicketMonthsAgo(6), moment().startOf('day')],
                'Last 1 year': [monitoringTicketMonthsAgo(12), moment().startOf('day')]
            }
        });

        dateRangeInput.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY'));
            filters.date_from = picker.startDate.format('YYYY-MM-DD');
            filters.date_to = picker.endDate.format('YYYY-MM-DD');
            document.getElementById('filter-date-from').value = filters.date_from;
            document.getElementById('filter-date-to').value = filters.date_to;
            reloadGrid();
        });

        dateRangeInput.on('cancel.daterangepicker', function () {
            $(this).val('');
            filters.date_from = '';
            filters.date_to = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            reloadGrid();
        });

        // Reset button
        document.getElementById('btn-reset-filters').addEventListener('click', () => {
            Object.keys(filters).forEach(k => filters[k] = '');

            // Plain inputs
            document.getElementById('filter-audit-id').value = '';
            document.getElementById('filter-invoice-id').value = '';

            // Date range picker: clear the visible input + hidden fields, and
            // reset the picker's own internal state back to today so
            // reopening the calendar doesn't still show a stale range.
            dateRangeInput.val('');
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            const dateRangePickerInstance = dateRangeInput.data('daterangepicker');
            if (dateRangePickerInstance) {
                dateRangePickerInstance.setStartDate(moment());
                dateRangePickerInstance.setEndDate(moment());
            }

            // Choices.js dropdowns - reset to empty value
            ['filter-coachee', 'filter-coach', 'filter-manager-scope', 'filter-client-code', 'filter-carrier-name', 'filter-exception-status'].forEach(id => {
                if (choicesInstances[id]) {
                    choicesInstances[id].setChoiceByValue('');
                } else {
                    document.getElementById(id).value = '';
                }
            });

            reloadGrid();
        });

        // ============================================================
        // Populate Coachee / Coach / Supervisor-Manager / Client Code /
        // Carrier / Exception Status dropdowns.
        // Endpoint /monitoring-filter-options returns:
        //   { client_codes: [...], carrier_names: [...], exception_statuses: [...],
        //     coachees: [{value,label}, ...], coaches: [{value,label}, ...],
        //     managers: {"Managers": [{value,label}, ...], "SMEs": [...], ...} }
        // ============================================================
        fetch('/monitoring-filter-options')
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(opts => {
                if (choicesInstances['filter-coachee'] && Array.isArray(opts.coachees)) {
                    choicesInstances['filter-coachee'].setChoices(
                        opts.coachees,
                        'value',
                        'label',
                        false // false = append to existing "All" option
                    );
                }

                if (choicesInstances['filter-coach'] && Array.isArray(opts.coaches)) {
                    choicesInstances['filter-coach'].setChoices(
                        opts.coaches,
                        'value',
                        'label',
                        false
                    );
                }

                if (choicesInstances['filter-manager-scope'] && opts.managers) {
                    // opts.managers is {"Managers": [{value,label}, ...], "SMEs": [...], ...}
                    // Choices.js groups need {label, id, choices: [...]} — same
                    // conversion as the QA dashboard's Manager/Supervisor picker.
                    const groups = Object.keys(opts.managers).map((groupName, idx) => ({
                        label: groupName,
                        id: idx + 1,
                        choices: opts.managers[groupName]
                    }));
                    choicesInstances['filter-manager-scope'].setChoices(groups, 'value', 'label', false);
                }

                if (choicesInstances['filter-client-code'] && Array.isArray(opts.client_codes)) {
                    choicesInstances['filter-client-code'].setChoices(
                        opts.client_codes.map(code => ({
                            value: code,
                            label: code
                        })),
                        'value',
                        'label',
                        false // false = append to existing "All" option
                    );
                }

                if (choicesInstances['filter-carrier-name'] && Array.isArray(opts.carrier_names)) {
                    choicesInstances['filter-carrier-name'].setChoices(
                        opts.carrier_names.map(name => ({
                            value: name,
                            label: name
                        })),
                        'value',
                        'label',
                        false
                    );
                }

                if (choicesInstances['filter-exception-status'] && Array.isArray(opts.exception_statuses)) {
                    choicesInstances['filter-exception-status'].setChoices(
                        opts.exception_statuses.map(s => ({
                            value: s,
                            label: s
                        })),
                        'value',
                        'label',
                        false
                    );
                }
            })
            .catch(err => console.warn('Could not load filter options:', err));
    </script>


</body>

</html>
