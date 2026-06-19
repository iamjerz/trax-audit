<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
@include('partials.header')

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
                        <a href="{{ route('export.recon') }}" class="btn btn-sm btn-success mb-3">
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
                                        <div class="d-flex gap-2">
                                            <input type="date" id="filter-date-from" class="form-control" placeholder="From">
                                            <input type="date" id="filter-date-to" class="form-control" placeholder="To">
                                        </div>
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

        // ============================================================
        // Grid.js Setup
        // ============================================================
        const grid = new gridjs.Grid({
            columns: [{
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
            ],

            server: {
                url: buildQuery(),
                then: data => data.data.map(item => [
                    item.submission_id,
                    item.full_name || '',
                    item.recon_call_date,
                    item.client_code,
                    item.carrier_code,
                    item.region,
                    item.status,
                    item.created_at
                ]),
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
        }).render(document.getElementById('table-recon'));

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
                    then: data => data.data.map(item => [
                        item.submission_id,
                        item.full_name || '',
                        item.recon_call_date,
                        item.client_code,
                        item.carrier_code,
                        item.region,
                        item.status,
                        item.created_at
                    ]),
                    total: data => data.total
                }
            }).forceRender();
        }

        // Name (debounced text input)
        document.getElementById('filter-name').addEventListener('input', debounce((e) => {
            filters.name = e.target.value.trim();
            reloadGrid();
        }, 500));

        // Selects + dates - reload immediately on change
        // Note: Choices.js dispatches 'change' on the underlying <select>, so this works for both
        ['filter-client-code', 'filter-carrier-code', 'filter-status', 'filter-date-from', 'filter-date-to'].forEach(id => {
            document.getElementById(id).addEventListener('change', (e) => {
                const key = id.replace('filter-', '').replace(/-/g, '_');
                filters[key] = e.target.value;
                reloadGrid();
            });
        });

        // Reset button
        document.getElementById('btn-reset-filters').addEventListener('click', () => {
            Object.keys(filters).forEach(k => filters[k] = '');

            // Plain inputs
            document.getElementById('filter-name').value = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';

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