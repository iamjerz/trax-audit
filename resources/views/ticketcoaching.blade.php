<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css">
@include('partials.header')
<style>
    /* Date Range Picker (daterangepicker.com) theming — same as /recon-ticket
       and /monitoring-ticket. */
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
                            <h5 class="card-title">Coaching Ticket</h5>
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
                                        <label class="form-label">Coaching Reference ID</label>
                                        <input type="text" id="filter-reference-id" class="form-control" placeholder="Search reference ID...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Coaching Reference</label>
                                        <input type="text" id="filter-reference" class="form-control" placeholder="Search reference...">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" id="filter-name" class="form-control" placeholder="Search coached employee...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Coaching Type</label>
                                        <select id="filter-reference-type" class="form-select dropdown-choices">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label class="form-label">Date Range (Coaching Date)</label>
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
            reference_id: '',
            reference: '',
            name: '',
            reference_type: '',
            date_from: '',
            date_to: ''
        };

        // Build query string from filters + pagination/search
        function buildQuery(extra = {}) {
            const params = new URLSearchParams();
            params.set('limit', extra.limit ?? limit);
            params.set('offset', extra.offset ?? 0);

            if (extra.search) params.set('search', extra.search);
            if (filters.reference_id) params.set('reference_id', filters.reference_id);
            if (filters.reference) params.set('reference', filters.reference);
            if (filters.name) params.set('name', filters.name);
            if (filters.reference_type) params.set('reference_type', filters.reference_type);
            if (filters.date_from) params.set('date_from', filters.date_from);
            if (filters.date_to) params.set('date_to', filters.date_to);

            return `/coaching-data?${params.toString()}`;
        }

        const columns = [
            {
                name: 'Coaching Reference ID',
                formatter: (cell) => {
                    const safe = String(cell).replace(/"/g, '&quot;');
                    return gridjs.html(`
                <a href="/coaching-ticket-view/${safe}" class="text-primary fw-bold">
                    ${safe}
                </a>
                `);
                }
            },
            'Coaching Reference',
            'Coaching Type',
            'Coached Employee',
            {
                name: 'Coaching Date',
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
                        item.reference_id,
                        item.reference,
                        item.reference_type,
                        item.coached_employee_name || '',
                        item.created_at,
                        item.full_name || ''
                    ];
                    if (canDelete) row.push(item.reference_id);
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
            if (!confirm('Delete coaching ticket ' + id + '? This cannot be undone.')) {
                return;
            }

            fetch('/coaching-ticket/' + encodeURIComponent(id), {
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
                            item.reference_id,
                            item.reference,
                            item.reference_type,
                            item.coached_employee_name || '',
                            item.created_at,
                            item.full_name || ''
                        ];
                        if (canDelete) row.push(item.reference_id);
                        return row;
                    }),
                    total: data => data.total
                }
            }).forceRender();
        }

        // Coaching Reference ID / Coaching Reference / Name (debounced text inputs)
        document.getElementById('filter-reference-id').addEventListener('input', debounce((e) => {
            filters.reference_id = e.target.value.trim();
            reloadGrid();
        }, 500));

        document.getElementById('filter-reference').addEventListener('input', debounce((e) => {
            filters.reference = e.target.value.trim();
            reloadGrid();
        }, 500));

        document.getElementById('filter-name').addEventListener('input', debounce((e) => {
            filters.name = e.target.value.trim();
            reloadGrid();
        }, 500));

        // Selects - reload immediately on change
        // Note: Choices.js dispatches 'change' on the underlying <select>, so this works for both.
        // Date range is handled separately below (daterangepicker.com), since
        // filter-date-from/filter-date-to are now hidden inputs driven by the
        // picker's own events, not something a user types/changes directly.
        ['filter-reference-type'].forEach(id => {
            document.getElementById(id).addEventListener('change', (e) => {
                const key = id.replace('filter-', '').replace(/-/g, '_');
                filters[key] = e.target.value;
                reloadGrid();
            });
        });

        // ============================================================
        // Date Range picker (daterangepicker.com) — same widget/config as
        // the Recon and Monitoring ticket lists.
        // ============================================================
        function coachingTicketDaysAgo(n) {
            return moment().startOf('day').subtract(n, 'days');
        }

        function coachingTicketMonthsAgo(n) {
            return moment().startOf('day').subtract(n, 'months');
        }

        const dateRangeInput = $('#filter-date-range');

        dateRangeInput.daterangepicker({
            autoUpdateInput: false, // keep the "All dates" placeholder until a range is actually chosen
            autoApply: true, // no separate confirm click, for both presets and manual picks
            alwaysShowCalendars: true, // presets + calendars visible together
            maxDate: moment(), // this list only ever has past coaching sessions
            locale: {
                format: 'MMM D, YYYY',
                separator: ' - '
            },
            ranges: {
                'Today': [moment().startOf('day'), moment().startOf('day')],
                'Yesterday': [coachingTicketDaysAgo(1), coachingTicketDaysAgo(1)],
                'Last 7 days': [coachingTicketDaysAgo(7), moment().startOf('day')],
                'Last 30 days': [coachingTicketDaysAgo(30), moment().startOf('day')],
                'Last 6 months': [coachingTicketMonthsAgo(6), moment().startOf('day')],
                'Last 1 year': [coachingTicketMonthsAgo(12), moment().startOf('day')]
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
            document.getElementById('filter-reference-id').value = '';
            document.getElementById('filter-reference').value = '';
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
            ['filter-reference-type'].forEach(id => {
                if (choicesInstances[id]) {
                    choicesInstances[id].setChoiceByValue('');
                } else {
                    document.getElementById(id).value = '';
                }
            });

            reloadGrid();
        });

        // ============================================================
        // Populate Coaching Type dropdown from server
        // Endpoint /coaching-filter-options returns:
        //   { reference_types: [...] }
        // ============================================================
        fetch('/coaching-filter-options')
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(opts => {
                if (choicesInstances['filter-reference-type'] && Array.isArray(opts.reference_types)) {
                    choicesInstances['filter-reference-type'].setChoices(
                        opts.reference_types.map(t => ({
                            value: t,
                            label: t
                        })),
                        'value',
                        'label',
                        false // false = append to existing "All" option
                    );
                }
            })
            .catch(err => console.warn('Could not load filter options:', err));
    </script>


</body>

</html>
