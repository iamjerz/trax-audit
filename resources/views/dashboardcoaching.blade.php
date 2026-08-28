<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css">
@include('partials.header')
<style>
    /* Date Range Picker (daterangepicker.com) theming — matches the other dashboards. */
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

    #dc-table-recent {
        max-width: 100%;
        overflow-x: auto;
    }
</style>
<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                {{-- Filter bar --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Date Range</label>
                                <input type="text" id="dc-date-range" class="form-control" placeholder="All dates" readonly>
                                <input type="hidden" id="dc-date-from">
                                <input type="hidden" id="dc-date-to">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Coach</label>
                                <select id="dc-coach" class="form-select form-select-sm">
                                    <option value="">All Coaches</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Coached Employee</label>
                                <select id="dc-employee" class="form-select form-select-sm">
                                    <option value="">All Employees</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Coaching Type</label>
                                <select id="dc-type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Scope</label>
                                <select id="dc-scope" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="my_team">My Team</option>
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="button" id="dc-apply" class="btn btn-sm btn-primary">Apply</button>
                                <button type="button" id="dc-reset" class="btn btn-sm btn-light">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Summary cards --}}
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Total Coaching Sessions</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="dc-total">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-message-square-detail font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Sessions This Month</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="dc-this-month">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-calendar-check font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">
                                            Unique Coaches
                                            <i class="bx bx-info-circle text-muted font-size-13"
                                               data-bs-toggle="tooltip"
                                               title="How many distinct coaches have logged at least one session in the current filter. A coach with 10 sessions still counts once."></i>
                                        </h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="dc-unique-coaches">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-user-voice font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">
                                            Unique Coached Employees
                                            <i class="bx bx-info-circle text-muted font-size-13"
                                               data-bs-toggle="tooltip"
                                               title="How many distinct employees have been coached at least once in the current filter. Sessions with no employee on record aren't counted here."></i>
                                        </h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="dc-unique-employees">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-group font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Most Coached Employee</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-16" id="dc-most-coached-employee">—</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-star font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Most Active Coach</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-16" id="dc-most-active-coach">—</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-medal font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Avg Sessions / Coach</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="dc-avg-per-coach">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-bar-chart-alt-2 font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Sessions Last 7 Days</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="dc-last-7-days">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-trending-up font-size-24 mb-0 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trend --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" id="dc-trend-title">Coaching Sessions Trend (last 12 months)</h4>
                            </div>
                            <div class="card-body">
                                <div id="dcTrendChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Breakdown charts --}}
                <div class="row h-100">
                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Sessions per Coach</h4>
                                <p class="card-title-desc mb-0 font-size-12 text-muted">Click a bar to filter the dashboard to that coach.</p>
                            </div>
                            <div class="card-body pb-0">
                                <div id="dcPerCoachChart" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Sessions per Coached Employee</h4>
                                <p class="card-title-desc mb-0 font-size-12 text-muted">Click a bar to filter the dashboard to that employee.</p>
                            </div>
                            <div class="card-body pb-0">
                                <div id="dcPerEmployeeChart" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row h-100">
                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Sessions per Coaching Type</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="dcPerTypeChart" data-colors='["#1f58c7", "#28b765","#f4c238", "#ed5555","#974be0"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Sessions by Day of Week</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="dcDayOfWeekChart" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent sessions --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Coaching Sessions</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="dc-table-recent"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
    <script>
        function animateCount(element, start, end, duration = 800) {
            let startTime = null;
            function update(currentTime) {
                if (!startTime) startTime = currentTime;
                const progress = Math.min((currentTime - startTime) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value;
                if (progress < 1) requestAnimationFrame(update);
            }
            requestAnimationFrame(update);
        }

        function dcFilterParams() {
            const from     = document.getElementById('dc-date-from').value;
            const to       = document.getElementById('dc-date-to').value;
            const coach    = document.getElementById('dc-coach').value;
            const employee = document.getElementById('dc-employee').value;
            const type     = document.getElementById('dc-type').value;
            const scope    = document.getElementById('dc-scope').value;

            const params = new URLSearchParams();
            if (from) params.append('date_from', from);
            if (to) params.append('date_to', to);
            if (coach) params.append('coach', coach);
            if (employee) params.append('coached_employee', employee);
            if (type) params.append('type', type);
            if (scope) params.append('scope', scope);
            return params;
        }

        function loadCards() {
            fetch('/dashboard-coaching-cards?' + dcFilterParams().toString(), { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    animateCount(document.getElementById('dc-total'), 0, data.total || 0);
                    animateCount(document.getElementById('dc-this-month'), 0, data.this_month || 0);
                    animateCount(document.getElementById('dc-unique-coaches'), 0, data.unique_coaches || 0);
                    animateCount(document.getElementById('dc-unique-employees'), 0, data.unique_coached_employees || 0);
                    animateCount(document.getElementById('dc-last-7-days'), 0, data.last_7_days || 0);
                    document.getElementById('dc-avg-per-coach').textContent = data.avg_sessions_per_coach ?? 0;
                    document.getElementById('dc-most-coached-employee').textContent = data.most_coached_employee_name || 'N/A';
                    document.getElementById('dc-most-active-coach').textContent = data.most_active_coach_name || 'N/A';
                })
                .catch(err => console.error('Coaching cards error:', err));
        }

        function updateTrendTitle() {
            const titleEl = document.getElementById('dc-trend-title');
            if (!titleEl) return;
            const from = document.getElementById('dc-date-from').value;
            const to = document.getElementById('dc-date-to').value;
            if (from && to) {
                const displayRange = document.getElementById('dc-date-range').value || (from + ' - ' + to);
                titleEl.textContent = 'Coaching Sessions Trend (' + displayRange + ')';
            } else {
                titleEl.textContent = 'Coaching Sessions Trend (last 12 months)';
            }
        }

        // One line per coaching type (QA/Triad/Recon/Uncategorized), instead
        // of a single combined total.
        let dcTrendChart = null;
        function loadTrend() {
            updateTrendTitle();
            fetch('/dashboard-coaching-trend?' + dcFilterParams().toString(), { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const series = data.series || [];
                    if (dcTrendChart) {
                        dcTrendChart.updateOptions({
                            series: series,
                            xaxis: { categories: data.labels || [] }
                        });
                    } else {
                        dcTrendChart = new ApexCharts(document.querySelector('#dcTrendChart'), {
                            chart: { type: 'line', height: 320, toolbar: { show: false } },
                            series: series,
                            xaxis: { categories: data.labels || [] },
                            stroke: { curve: 'smooth', width: 3 },
                            colors: ['#1f58c7', '#28b765', '#f4c238', '#ed5555', '#974be0'],
                            markers: { size: 4 },
                            // Multiple lines with a numeric label on every point gets
                            // cluttered fast, so this relies on the legend + tooltip
                            // instead of always-on dataLabels (unlike the single-line
                            // QA dashboard trend).
                            dataLabels: { enabled: false },
                            legend: { position: 'top' }
                        });
                        dcTrendChart.render();
                    }
                })
                .catch(err => console.error('Coaching trend error:', err));
        }

        const dcCharts = {};
        function renderChart(key, selector, options, hasData) {
            const el = document.querySelector(selector);
            if (dcCharts[key]) {
                dcCharts[key].destroy();
                dcCharts[key] = null;
            }
            if (!hasData) {
                el.innerHTML = '<div class="text-muted text-center py-5">No data for the selected filters.</div>';
                return;
            }
            el.innerHTML = '';
            dcCharts[key] = new ApexCharts(el, options);
            dcCharts[key].render();
        }

        // Clicking a bar drills the whole dashboard down to that one
        // coach/employee, by setting the matching filter select and
        // reloading -- skipped for the Unassigned/blank bucket, which has no
        // real employeeid to filter by.
        function loadPerCoach() {
            fetch('/dashboard-coaching-per-coach?' + dcFilterParams().toString(), { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(i => i.name);
                    const values = data.map(i => Number(i.total));
                    const options = {
                        series: [{ data: values }],
                        chart: {
                            type: 'bar', height: 320, toolbar: { show: false },
                            events: {
                                dataPointSelection: function (event, chartContext, config) {
                                    const row = data[config.dataPointIndex];
                                    if (row && row.employeeid && dcChoices['dc-coach']) {
                                        dcChoices['dc-coach'].setChoiceByValue(row.employeeid);
                                        reloadDashboard();
                                    }
                                }
                            }
                        },
                        plotOptions: { bar: { horizontal: true, barHeight: '70%', distributed: true } },
                        colors: ['#1f58c7', '#28b765', '#f4c238', '#ed5555', '#974be0', '#52c6ea', '#f1734f'],
                        dataLabels: { enabled: true },
                        legend: { show: false },
                        xaxis: { categories: labels }
                    };
                    renderChart('coach', '#dcPerCoachChart', options, labels.length && values.length);
                })
                .catch(err => console.error('Per-coach chart error:', err));
        }

        function loadPerEmployee() {
            fetch('/dashboard-coaching-per-employee?' + dcFilterParams().toString(), { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(i => i.name);
                    const values = data.map(i => Number(i.total));
                    const options = {
                        series: [{ data: values }],
                        chart: {
                            type: 'bar', height: 320, toolbar: { show: false },
                            events: {
                                dataPointSelection: function (event, chartContext, config) {
                                    const row = data[config.dataPointIndex];
                                    if (row && row.employeeid && dcChoices['dc-employee']) {
                                        dcChoices['dc-employee'].setChoiceByValue(row.employeeid);
                                        reloadDashboard();
                                    }
                                }
                            }
                        },
                        plotOptions: { bar: { horizontal: true, barHeight: '70%', distributed: true } },
                        colors: ['#28b765', '#1f58c7', '#f4c238', '#ed5555', '#974be0', '#52c6ea', '#f1734f'],
                        dataLabels: { enabled: true },
                        legend: { show: false },
                        xaxis: { categories: labels }
                    };
                    renderChart('employee', '#dcPerEmployeeChart', options, labels.length && values.length);
                })
                .catch(err => console.error('Per-employee chart error:', err));
        }

        function loadPerType() {
            fetch('/dashboard-coaching-per-type?' + dcFilterParams().toString(), { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(i => i.type);
                    const values = data.map(i => Number(i.total));
                    const options = {
                        series: values,
                        chart: { type: 'donut', height: 320 },
                        labels: labels,
                        legend: { position: 'bottom' },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val, opts) {
                                const value = opts.w.globals.series[opts.seriesIndex];
                                return value + ' (' + val.toFixed(1) + '%)';
                            }
                        },
                        colors: ['#1f58c7', '#28b765', '#f4c238', '#ed5555', '#974be0']
                    };
                    renderChart('type', '#dcPerTypeChart', options, labels.length && values.length);
                })
                .catch(err => console.error('Per-type chart error:', err));
        }

        function loadDayOfWeek() {
            fetch('/dashboard-coaching-day-of-week?' + dcFilterParams().toString(), { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const labels = data.labels || [];
                    const values = (data.counts || []).map(Number);
                    const options = {
                        series: [{ name: 'Sessions', data: values }],
                        chart: { type: 'bar', height: 320, toolbar: { show: false } },
                        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                        colors: ['#974be0'],
                        dataLabels: { enabled: true },
                        legend: { show: false },
                        xaxis: { categories: labels }
                    };
                    renderChart('dow', '#dcDayOfWeekChart', options, labels.length && values.some(v => v > 0));
                })
                .catch(err => console.error('Day-of-week chart error:', err));
        }

        let dcRecentGrid = null;
        function loadRecent() {
            const server = {
                url: '/dashboard-coaching-recent?' + dcFilterParams().toString(),
                headers: { 'Accept': 'application/json' },
                then: data => data.recent_sessions.map(row => [
                    gridjs.html(`
                        <a href="/coaching-ticket-view/${row.reference_id}"
                        target="_blank"
                        style="color:#1f58c7; text-decoration: underline;">
                            <strong>${row.reference_id}</strong>
                        </a>
                    `),
                    row.coached_employee_name || 'N/A',
                    row.coach_name || 'N/A',
                    row.reference_type || 'Uncategorized',
                    row.created_at
                ])
            };

            if (dcRecentGrid) {
                dcRecentGrid.updateConfig({ server }).forceRender();
            } else {
                dcRecentGrid = new gridjs.Grid({
                    columns: ['Coaching ID', 'Coached Employee', 'Coach', 'Type', 'Date'],
                    pagination: { limit: 20 },
                    search: false,
                    sort: false,
                    width: '100%',
                    server
                });
                dcRecentGrid.render(document.getElementById('dc-table-recent'));
            }
        }

        function reloadDashboard() {
            loadCards();
            loadTrend();
            loadPerCoach();
            loadPerEmployee();
            loadPerType();
            loadDayOfWeek();
            loadRecent();
        }

        // Bootstrap tooltips on the card info icons — not auto-activated,
        // need explicit init.
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });

        // Searchable Choices.js on the filter dropdowns.
        const dcChoices = {};
        dcChoices['dc-coach'] = new Choices(document.getElementById('dc-coach'), {
            searchEnabled: true, itemSelectText: '', shouldSort: false, allowHTML: false
        });
        dcChoices['dc-employee'] = new Choices(document.getElementById('dc-employee'), {
            searchEnabled: true, itemSelectText: '', shouldSort: false, allowHTML: false
        });
        dcChoices['dc-type'] = new Choices(document.getElementById('dc-type'), {
            searchEnabled: true, itemSelectText: '', shouldSort: false, allowHTML: false
        });
        dcChoices['dc-scope'] = new Choices(document.getElementById('dc-scope'), {
            searchEnabled: false, itemSelectText: '', shouldSort: false, allowHTML: false
        });

        fetch('/dashboard-coaching-filter-options', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(opts => {
                if (dcChoices['dc-coach'] && Array.isArray(opts.coaches)) {
                    dcChoices['dc-coach'].setChoices(
                        opts.coaches.map(c => ({ value: c.employeeid, label: c.name })),
                        'value', 'label', false
                    );
                }
                if (dcChoices['dc-employee'] && Array.isArray(opts.coached_employees)) {
                    dcChoices['dc-employee'].setChoices(
                        opts.coached_employees.map(c => ({ value: c.employeeid, label: c.name })),
                        'value', 'label', false
                    );
                }
                if (dcChoices['dc-type'] && Array.isArray(opts.types)) {
                    dcChoices['dc-type'].setChoices(
                        opts.types.map(t => ({ value: t, label: t })),
                        'value', 'label', false
                    );
                }
            })
            .catch(err => console.warn('Could not load coaching dashboard filter options:', err));

        // Date Range picker (daterangepicker.com) — same presets/behavior as dashboard-qa.
        function dcDaysAgo(n) {
            return moment().startOf('day').subtract(n, 'days');
        }
        function dcMonthsAgo(n) {
            return moment().startOf('day').subtract(n, 'months');
        }

        const dcDateInput = $('#dc-date-range');

        dcDateInput.daterangepicker({
            autoUpdateInput: false,
            autoApply: true,
            alwaysShowCalendars: true,
            maxDate: moment(),
            locale: {
                format: 'MMM D, YYYY',
                separator: ' - '
            },
            ranges: {
                'Today': [moment().startOf('day'), moment().startOf('day')],
                'Yesterday': [dcDaysAgo(1), dcDaysAgo(1)],
                'Last 7 days': [dcDaysAgo(7), moment().startOf('day')],
                'Last 30 days': [dcDaysAgo(30), moment().startOf('day')],
                'Last 6 months': [dcMonthsAgo(6), moment().startOf('day')],
                'Last 1 year': [dcMonthsAgo(12), moment().startOf('day')]
            }
        });

        dcDateInput.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY'));
            document.getElementById('dc-date-from').value = picker.startDate.format('YYYY-MM-DD');
            document.getElementById('dc-date-to').value = picker.endDate.format('YYYY-MM-DD');
            reloadDashboard();
        });

        dcDateInput.on('cancel.daterangepicker', function () {
            $(this).val('');
            document.getElementById('dc-date-from').value = '';
            document.getElementById('dc-date-to').value = '';
            reloadDashboard();
        });

        // ============================================================
        // Initial load + filter wiring
        // ============================================================
        reloadDashboard();

        document.getElementById('dc-apply').addEventListener('click', reloadDashboard);
        document.getElementById('dc-reset').addEventListener('click', function () {
            document.getElementById('dc-date-from').value = '';
            document.getElementById('dc-date-to').value = '';
            dcDateInput.val('');
            const dcPickerInstance = dcDateInput.data('daterangepicker');
            if (dcPickerInstance) {
                dcPickerInstance.setStartDate(moment());
                dcPickerInstance.setEndDate(moment());
            }
            if (dcChoices['dc-coach']) dcChoices['dc-coach'].setChoiceByValue('');
            if (dcChoices['dc-employee']) dcChoices['dc-employee'].setChoiceByValue('');
            if (dcChoices['dc-type']) dcChoices['dc-type'].setChoiceByValue('');
            if (dcChoices['dc-scope']) dcChoices['dc-scope'].setChoiceByValue('');
            reloadDashboard();
        });
    </script>
</body>
</html>
