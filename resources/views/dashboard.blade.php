<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@include('partials.header')
<style>
    .counter {
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .counter.show {
        opacity: 1;
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

                {{-- Date range filter --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">From</label>
                                <input type="date" id="dash-date-from" class="form-control form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">To</label>
                                <input type="date" id="dash-date-to" class="form-control form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Carrier Name</label>
                                <select id="dash-carrier" class="form-select form-select-sm">
                                    <option value="">All Carriers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Manager / Supervisor</label>
                                <select id="dash-supervisor" class="form-select form-select">
                                    <option value="">All</option>
                                    <option value="my_team">My Team</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="button" id="dash-apply" class="btn btn-sm btn-primary">Apply</button>
                                <button type="button" id="dash-reset" class="btn btn-sm btn-light">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-xl">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Total Evaluations</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="total-evaluations">0</h4>
                                    </div>
                                    <div class="">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                <i class="bx bx-cylinder font-size-24 mb-0 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Total LDA</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="total-lda">0</h4>
                                    </div>
                                    <div class="">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                <i class="bx bx-stats font-size-24 mb-0 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Above Average 75.00%</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="above-average">0</h4>
                                    </div>
                                    <div class="">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                <i class="bx bx-check-double font-size-24 mb-0 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Below Average 75.00%</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="below-average">0</h4>
                                    </div>
                                    <div class="">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                <i class="bx bx-minus font-size-24 mb-0 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Overall Average</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="overall-average">0</h4>
                                    </div>
                                    <div class="">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                <i class="bx bx-line-chart font-size-24 mb-0 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Evaluations Trend (last 12 months)</h4>
                            </div>
                            <div class="card-body">
                                <div id="evalTrendChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row h-100">
                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Recent Audit Ticket</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="table-gridjs"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Numbers of Accountable in Impact Factors</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="simple_pie_chart" data-colors='["#1f58c7", "#28b765","#f4c238", "#ed5555","#974be0"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row h-100">
                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Cause Issue</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="custom_datalabels_bar" data-colors='["#52c6ea", "#495057", "#e83e8c", "#28b765", "#ed5555", "#2b908f", "#f9a3a4", "#974be0",
                                        "#f1734f", "#1f58c7"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Root Cause Analysis</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="simple_dount_chart" data-colors='["#1f58c7", "#28b765","#f4c238", "#ed5555","#974be0"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <!-- apexcharts -->
    <!-- Sweet Alerts js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        function animateCount(element, start, end, duration = 800) {
            let startTime = null;

            function update(currentTime) {
                if (!startTime) startTime = currentTime;

                const progress = Math.min((currentTime - startTime) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);

                element.textContent = value;

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            requestAnimationFrame(update);
        }


        // Build the shared query string from every dashboard filter.
        function dashFilterParams() {
            const from       = document.getElementById("dash-date-from").value;
            const to         = document.getElementById("dash-date-to").value;
            const carrier    = document.getElementById("dash-carrier").value;
            const scope      = document.getElementById("dash-supervisor").value;

            const params = new URLSearchParams();
            if (from) params.append("date_from", from);
            if (to) params.append("date_to", to);
            if (carrier) params.append("carrier_name", carrier);
            if (scope) params.append("scope", scope);
            return params;
        }

        function loadDashboardCards() {
            fetch("/dashboard/cards?" + dashFilterParams().toString(), {
                headers: { "Accept": "application/json" }
            })
            .then(res => res.json())
            .then(data => {
                animateCount(document.getElementById("total-evaluations"), 0, data.total || 0);
                animateCount(document.getElementById("total-lda"), 0, data.total_lda || 0);
                animateCount(document.getElementById("above-average"), 0, data.above_average || 0);
                animateCount(document.getElementById("below-average"), 0, data.below_average || 0);
                document.getElementById("overall-average").textContent =
                    (data.overall_average ?? 0) + "%";
            })
            .catch(err => console.error(err));
        }

        // Evaluations trend (last 12 months) — re-rendered when filters change.
        let trendChart = null;
        function loadTrend() {
            fetch("/dashboard/trend?" + dashFilterParams().toString(), {
                headers: { "Accept": "application/json" }
            })
            .then(res => res.json())
            .then(data => {
                if (trendChart) {
                    trendChart.updateOptions({
                        series: [{ name: "Evaluations", data: data.counts || [] }],
                        xaxis: { categories: data.labels || [] }
                    });
                } else {
                    trendChart = new ApexCharts(document.querySelector("#evalTrendChart"), {
                        chart: { type: "line", height: 320, toolbar: { show: false } },
                        series: [{ name: "Evaluations", data: data.counts || [] }],
                        xaxis: { categories: data.labels || [] },
                        stroke: { curve: "smooth", width: 3 },
                        colors: ["#1f58c7"],
                        markers: { size: 4 },
                        dataLabels: { enabled: true }
                    });
                    trendChart.render();
                }
            })
            .catch(err => console.error("Trend chart error:", err));
        }

        function reloadDashboard() {
            loadDashboardCards();
            loadTrend();
            loadRecentTicket();
            loadAccountableFactor();
            loadCauseIssue();
            loadRootCause();
        }

        // Searchable Choices.js on the Carrier dropdown (many options).
        // Manager/Supervisor stays a plain 2-option select (All / My Team).
        const dashChoices = {};
        dashChoices["dash-carrier"] = new Choices(document.getElementById("dash-carrier"), {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            allowHTML: false
        });

        // Populate Carrier Name options.
        fetch("/dashboard/filter-options", { headers: { "Accept": "application/json" } })
            .then(res => res.json())
            .then(opts => {
                if (dashChoices["dash-carrier"] && Array.isArray(opts.carriers)) {
                    dashChoices["dash-carrier"].setChoices(
                        opts.carriers.map(c => ({ value: c, label: c })),
                        "value", "label", false
                    );
                }
            })
            .catch(err => console.warn("Could not load dashboard filter options:", err));


        // ============================================================
        // Recent Audit Ticket table (filter-aware)
        // ============================================================
        let recentGrid = null;
        function loadRecentTicket() {
            const url = '/dashboard/recent-ticket?' + dashFilterParams().toString();
            const server = {
                url: url,
                headers: { 'Accept': 'application/json' },
                then: data => data.recent_ticket.map(row => [
                    gridjs.html(`
                        <a href="/ticket/view/${row.audit_id}"
                        target="_blank"
                        style="color:#1f58c7; text-decoration: underline;">
                            <strong>${row.invoice_id}</strong>
                        </a>
                    `),
                    row.employee_name,
                    row.audit_date_1,
                    row.created_by_name ?? '—'
                ])
            };

            if (recentGrid) {
                recentGrid.updateConfig({ server }).forceRender();
            } else {
                recentGrid = new gridjs.Grid({
                    columns: ["Invoice ID", "Employee Name", "Audit Date 1", "Created By"],
                    pagination: { limit: 20 },
                    search: false,
                    sort: false,
                    server
                });
                recentGrid.render(document.getElementById('table-gridjs'));
            }
        }

        // ============================================================
        // ApexCharts helper — (re)render a chart into a selector
        // ============================================================
        const dashCharts = {};
        function renderChart(key, selector, options, hasData) {
            const el = document.querySelector(selector);
            if (dashCharts[key]) {
                dashCharts[key].destroy();
                dashCharts[key] = null;
            }
            if (!hasData) {
                el.innerHTML = '<div class="text-muted text-center py-5">No data for the selected filters.</div>';
                return;
            }
            el.innerHTML = '';
            dashCharts[key] = new ApexCharts(el, options);
            dashCharts[key].render();
        }

        // 🎨 Chart colors
        const chartColors = ["#4CAF50", "#FF9800", "#2196F3", "#E91E63", "#974be0"];

        function loadAccountableFactor() {
          fetch("/dashboard/accountable-factor?" + dashFilterParams().toString(), {
            headers: {
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {

            // 🧠 Transform API data
            const labels = data.accountable_factor.map(i => i.accountable_factors);
            const series = data.accountable_factor.map(i => Number(i.total_rows));

            // 📊 Chart options
            const options = {
                series: series,
                chart: { 
                    height: 350, 
                    type: "pie",
                    animations: {
                        enabled: true,
                        easing: "easeinout",
                        speed: 800
                    }
                },
                labels: labels,

                legend: { 
                    position: "bottom" 
                },

                // ✅ percentage + true number
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        const value = opts.w.globals.series[opts.seriesIndex];
                        return `${value} (${val.toFixed(1)}%)`;
                    },
                    style: {
                        fontSize: "13px",
                        fontWeight: "600"
                    }
                },

                // ✅ tooltip shows real values
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return value + " cases";
                        }
                    }
                },

                colors: chartColors
            };

            renderChart('pie', "#simple_pie_chart", options, labels.length && series.length);

        })
        .catch(err => console.error("API Chart Error:", err));
        }

        // Cause Issue Chart
        // 🎨 Colors for distributed bars
        const chartColorsRoot = [
            "#4CAF50", "#FF9800", "#2196F3", "#9C27B0", "#E91E63",
            "#3F51B5", "#009688", "#FFC107", "#795548", "#607D8B"
        ];

        function loadCauseIssue() {
        fetch("/dashboard/cause-issue?" + dashFilterParams().toString(), {
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {

            // 🧠 Handle nested array
            const apiData = data[0] || [];   // <-- important

            // 🧠 Transform API data
            const labels = apiData.map(i => i.cause_issue);
            const values = apiData.map(i => Number(i.total_rows));

            const options = {
                series: [{
                    data: values
                }],

                chart: {
                    type: "bar",
                    height: 350,
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: "easeinout",
                        speed: 800
                    }
                },

                plotOptions: {
                    bar: {
                        barHeight: "100%",
                        distributed: true,
                        horizontal: true,
                        dataLabels: {
                            position: "bottom"
                        }
                    }
                },

                colors: chartColorsRoot,

                // ✅ Custom labels inside bars
                dataLabels: {
                    enabled: true,
                    textAnchor: "start",
                    style: {
                        colors: ["#fff"],
                        fontSize: "12px",
                        fontWeight: "600"
                    },
                    formatter: function(val, opts) {
                        const label = opts.w.globals.labels[opts.dataPointIndex];
                        return `${label}: ${val}`;   // true number
                    },
                    offsetX: 0,
                    dropShadow: { enabled: false }
                },

                stroke: {
                    width: 1,
                    colors: ["#fff"]
                },

                xaxis: {
                    categories: labels
                },

                yaxis: {
                    labels: { show: false }
                },

                title: {
                    text: "Cause Issues",
                    align: "center",
                    floating: true,
                    style: { fontWeight: 600 }
                },

                // subtitle: {
                //     text: "Category Names as DataLabels inside bars",
                //     align: "center"
                // },

                tooltip: {
                    theme: "dark",
                    x: { show: false },
                    y: {
                        formatter: function(val) {
                            return val + " cases";
                        },
                        title: {
                            formatter: function() { return ""; }
                        }
                    }
                }
            };

            renderChart('cause', "#custom_datalabels_bar", options, labels.length && values.length);

        })
        .catch(err => console.error("Cause Issues Chart API Error:", err));
        }

        // Root Cause
        // 🎨 Donut colors
        const chartColorsRootCause = ["#E53935", "#43A047"];

        function loadRootCause() {
        fetch("/dashboard/root-cause?" + dashFilterParams().toString(), {
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {

            // 🧠 handle nested API array
            const apiData = data[0] || [];   // <-- important

            const labels = apiData.map(i => i.root_cause);
            const series = apiData.map(i => Number(i.total_rows));

            const options = {
                series: series,

                chart: {
                    height: 350,
                    type: "donut",
                    animations: {
                        enabled: true,
                        easing: "easeinout",
                        speed: 800
                    }
                },

                labels: labels,

                legend: {
                    position: "bottom",
                    formatter: function(seriesName, opts) {
                        const value = opts.w.globals.series[opts.seriesIndex];
                        return `${seriesName}: ${value}`;
                    }
                },

                // ✅ true number + %
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        const value = opts.w.globals.series[opts.seriesIndex];
                        return `${value} (${val.toFixed(1)}%)`;
                    },
                    style: {
                        fontSize: "13px",
                        fontWeight: "600"
                    },
                    dropShadow: { enabled: false }
                },

                // ✅ Center total
                plotOptions: {
                    pie: {
                        donut: {
                            size: "65%",
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: "Total",
                                    fontSize: "14px",
                                    fontWeight: 600,
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },

                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value + " cases";
                        }
                    }
                },

                colors: chartColorsRootCause
            };

            renderChart('root', "#simple_dount_chart", options, labels.length && series.length);

        })
        .catch(err => console.error("Root Cause Donut API Error:", err));
        }

        // ============================================================
        // Initial load + filter wiring (runs after all declarations)
        // ============================================================
        reloadDashboard();

        document.getElementById("dash-apply").addEventListener("click", reloadDashboard);
        document.getElementById("dash-reset").addEventListener("click", function () {
            document.getElementById("dash-date-from").value = "";
            document.getElementById("dash-date-to").value = "";
            document.getElementById("dash-supervisor").value = "";
            if (dashChoices["dash-carrier"]) dashChoices["dash-carrier"].setChoiceByValue("");
            reloadDashboard();
        });

    </script>
</body>

</html>