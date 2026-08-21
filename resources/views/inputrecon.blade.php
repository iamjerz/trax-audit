<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css">
@include('partials.header')
<style>
    /* Date Range Picker (daterangepicker.com) theming — matches this app's
       primary accent (#556ee6, same shade used on the QA and Recon
       dashboards' date filters and SweetAlert2 buttons) instead of the
       library's own default colors (#08c / #357ebd). Plain selectors, not
       CSS custom properties — this library doesn't expose theming vars —
       with !important to beat the stylesheet loaded via <link> above. */
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
                            <h5 class="card-title">Recon Ticket</h5>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('export.recon') }}" id="btn-export" class="btn btn-sm btn-success mb-3">
                            <i class="bx bx-download"></i> Export to Excel
                        </a>
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
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" id="filter-name" class="form-control" placeholder="Search name...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Client Code</label>
                                        <select id="filter-client-code" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Carrier Code</label>
                                        <select id="filter-carrier-code" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Status</label>
                                        <select id="filter-status" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                            <option value="To Do">To Do</option>
                                            <option value="Pending">Pending</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Closed">Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label class="form-label">Date Range (Recon Date)</label>
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

                <!-- ============================================================== -->
                <!-- Grid Table -->
                <!-- ============================================================== -->
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
    <!-- <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> -->
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
            name: '',
            client_code: '',
            carrier_code: '',
            status: '',
            date_from: '',
            date_to: ''
        };

        // Build query string from filters + pagination/search
        function buildQuery(extra = {}) {
            const params = new URLSearchParams();
            params.set('limit', extra.limit ?? limit);
            params.set('offset', extra.offset ?? 0);

            if (extra.search) params.set('search', extra.search);
            if (filters.name) params.set('name', filters.name);
            if (filters.client_code) params.set('client_code', filters.client_code);
            if (filters.carrier_code) params.set('carrier_code', filters.carrier_code);
            if (filters.status) params.set('status', filters.status);
            if (filters.date_from) params.set('date_from', filters.date_from);
            if (filters.date_to) params.set('date_to', filters.date_to);

            return `/recon-data?${params.toString()}`;
        }

        const columns = [{
                name: 'Submission ID',
                formatter: (cell) => {
                    const safe = String(cell).replace(/"/g, '&quot;');
                    return gridjs.html(`
                        <a href="/recon-ticket-view/${safe}" class="text-primary fw-bold">
                            ${safe}
                        </a>
                    `);
                }
            },
            'Name',
            {
                name: 'Recon Date',
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
            'Client Code',
            'Carrier Code',
            'Region',
            {
                name: 'Action Item Summary',
                formatter: (cell) => {
                    const t = cell ? String(cell) : '';
                    if (t.length <= 60) return t;
                    return gridjs.html(`<span title="${t.replace(/"/g,'&quot;')}">${t.slice(0,60)}…</span>`);
                }
            },
            {
                name: 'Action Item Details',
                formatter: (cell) => {
                    const t = cell ? String(cell) : '';
                    if (t.length <= 60) return t;
                    return gridjs.html(`<span title="${t.replace(/"/g,'&quot;')}">${t.slice(0,60)}…</span>`);
                }
            },
            'Jira Ticket',
            {
                name: 'Status',
                formatter: (cell) => {
                    let color = 'secondary';
                    if (cell === 'Pending') color = 'warning';
                    if (cell === 'To Do') color = 'secondary';
                    if (cell === 'Closed') color = 'success';
                    if (cell === 'In Progress') color = 'primary';

                    return gridjs.html(`
                        <span class="badge bg-${color}">
                            ${cell}
                        </span>
                    `);
                }
            },
            {
                name: "Created At",
                formatter: (cell) => {
                    if (!cell) return '';
                    const iso = cell.replace(' ', 'T').replace('+08', '+08:00');
                    const date = new Date(iso);
                    if (isNaN(date)) return cell;
                    return date.toLocaleString('en-PH', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                }
            }
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

        // ============================================================
        // Grid.js Setup
        // ============================================================
        const grid = new gridjs.Grid({
            columns: columns,

            server: {
                url: buildQuery(),
                then: data => data.data.map(item => {
                    const row = [
                        item.submission_id,
                        item.full_name || '',
                        item.recon_call_date,
                        item.client_code,
                        item.carrier_code,
                        item.region,
                        item.action_item_summary || '',
                        item.action_item_details || '',
                        item.jira_ticket || '',
                        item.status,
                        item.created_at
                    ];
                    if (canDelete) row.push(item.submission_id);
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
            if (!confirm('Delete recon ticket ' + id + '? This will also remove its comments and cannot be undone.')) {
                return;
            }

            fetch('/recon-ticket/' + encodeURIComponent(id), {
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

        // Keep the "Export to Excel" link in sync with the active filters so the
        // download contains exactly what the table is showing.
        const exportBase = document.getElementById('btn-export').getAttribute('href').split('?')[0];
        function updateExportLink() {
            const params = new URLSearchParams();
            if (filters.name) params.set('name', filters.name);
            if (filters.client_code) params.set('client_code', filters.client_code);
            if (filters.carrier_code) params.set('carrier_code', filters.carrier_code);
            if (filters.status) params.set('status', filters.status);
            if (filters.date_from) params.set('date_from', filters.date_from);
            if (filters.date_to) params.set('date_to', filters.date_to);

            const qs = params.toString();
            document.getElementById('btn-export').setAttribute('href', qs ? `${exportBase}?${qs}` : exportBase);
        }
        updateExportLink();

        function reloadGrid() {
            updateExportLink();
            grid.updateConfig({
                server: {
                    url: buildQuery(),
                    then: data => data.data.map(item => {
                        const row = [
                            item.submission_id,
                            item.full_name || '',
                            item.recon_call_date,
                            item.client_code,
                            item.carrier_code,
                            item.region,
                            item.action_item_summary || '',
                            item.action_item_details || '',
                            item.jira_ticket || '',
                            item.status,
                            item.created_at
                        ];
                        if (canDelete) row.push(item.submission_id);
                        return row;
                    }),
                    total: data => data.total
                }
            }).forceRender();
        }

        // Name (debounced text input)
        document.getElementById('filter-name').addEventListener('input', debounce((e) => {
            filters.name = e.target.value.trim();
            reloadGrid();
        }, 500));

        // Selects - reload immediately on change
        // Note: Choices.js dispatches 'change' on the underlying <select>, so this works for both.
        // Date range is handled separately below (daterangepicker.com), since
        // filter-date-from/filter-date-to are now hidden inputs driven by the
        // picker's own events, not something a user types/changes directly.
        ['filter-client-code', 'filter-carrier-code', 'filter-status'].forEach(id => {
            document.getElementById(id).addEventListener('change', (e) => {
                const key = id.replace('filter-', '').replace(/-/g, '_');
                filters[key] = e.target.value;
                reloadGrid();
            });
        });

        // ============================================================
        // Date Range picker (daterangepicker.com) — same widget/config as the
        // QA and Recon dashboards: presets + calendars always visible
        // together, one click applies (preset or manual range), theme
        // matched to #556ee6.
        // ============================================================
        function reconTicketDaysAgo(n) {
            return moment().startOf('day').subtract(n, 'days');
        }

        function reconTicketMonthsAgo(n) {
            return moment().startOf('day').subtract(n, 'months');
        }

        const dateRangeInput = $('#filter-date-range');

        dateRangeInput.daterangepicker({
            autoUpdateInput: false, // keep the "All dates" placeholder until a range is actually chosen
            autoApply: true, // no separate confirm click, for both presets and manual picks
            alwaysShowCalendars: true, // presets + calendars visible together
            maxDate: moment(), // this list only ever has past recon data
            locale: {
                format: 'MMM D, YYYY',
                separator: ' - '
            },
            ranges: {
                'Today': [moment().startOf('day'), moment().startOf('day')],
                'Yesterday': [reconTicketDaysAgo(1), reconTicketDaysAgo(1)],
                'Last 7 days': [reconTicketDaysAgo(7), moment().startOf('day')],
                'Last 30 days': [reconTicketDaysAgo(30), moment().startOf('day')],
                'Last 6 months': [reconTicketMonthsAgo(6), moment().startOf('day')],
                'Last 1 year': [reconTicketMonthsAgo(12), moment().startOf('day')]
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
            document.getElementById('filter-name').value = '';

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
            ['filter-client-code', 'filter-carrier-code', 'filter-status'].forEach(id => {
                if (choicesInstances[id]) {
                    choicesInstances[id].setChoiceByValue('');
                } else {
                    document.getElementById(id).value = '';
                }
            });

            reloadGrid();
        });

        // ============================================================
        // Populate Client Code & Carrier Code dropdowns from server
        // Endpoint /recon-filter-options returns:
        //   { client_codes: [...], carrier_codes: [...], statuses: [...] }
        // ============================================================
        fetch('/recon-filter-options')
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(opts => {
                // Client Code
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

                // Carrier Code
                if (choicesInstances['filter-carrier-code'] && Array.isArray(opts.carrier_codes)) {
                    choicesInstances['filter-carrier-code'].setChoices(
                        opts.carrier_codes.map(code => ({
                            value: code,
                            label: code
                        })),
                        'value',
                        'label',
                        false
                    );
                }

                // Status (optional) — uncomment if you'd rather use the server list
                // instead of the hardcoded options in the HTML:
                /*
                if (choicesInstances['filter-status'] && Array.isArray(opts.statuses)) {
                    choicesInstances['filter-status'].setChoices(
                        [{ value: '', label: 'All' }, ...opts.statuses.map(s => ({ value: s, label: s }))],
                        'value',
                        'label',
                        true // true = replace existing options
                    );
                }
                */
            })
            .catch(err => console.warn('Could not load filter options:', err));
    </script>

</body>

</html>