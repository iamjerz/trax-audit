<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
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
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Total Evaluations</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="total-evaluations">0 <span class="text-success fw-medium font-size-14 align-middle"></h4>
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

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Total LDA</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="total-lda">0 <span class="text-success fw-medium font-size-14 align-middle"></h4>
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

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Above Average 75.00%</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="above-average">0 <span class="text-success fw-medium font-size-14 align-middle"></h4>
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

                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-15">Below Average 75.00%</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="below-average">0 <span class="text-success fw-medium font-size-14 align-middle"></h4>
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


        fetch("/dashboard/cards", {
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {
            const totalEvaluationsEl = document.getElementById("total-evaluations");
            const totalLdaEl = document.getElementById("total-lda");

            animateCount(totalEvaluationsEl, 0, data.total);
            animateCount(totalLdaEl, 0, data.total_lda);

            console.log("Total audits:", data.total);
            console.log("Total LDA:", data.total_lda);
        })
        .catch(err => console.error(err));


        document.addEventListener('DOMContentLoaded', () => {
            new gridjs.Grid({
                columns: [
                    "Invoice ID",
                    "Employee Name",
                    "Audit Date 1",
                    "Created By"
                ],
                pagination: {
                    limit: 20
                },
                search: false,
                sort: false,
                server: {
                    url: '/dashboard/recent-ticket',
                    headers: {
                        'Accept': 'application/json'
                    },
                    then: data => {

                        return data.recent_ticket.map(row => [
                        // ✅ Clickable Invoice Number
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
                        ]);
                    }
                }
            }).render(document.getElementById('table-gridjs'));
        });
        // 🎨 Chart colors
        const chartColors = ["#4CAF50", "#FF9800", "#2196F3", "#E91E63", "#974be0"];

        fetch("/dashboard/accountable-factor", {
            headers: { 
                "Accept": "application/json" 
            }
        })
        .then(res => res.json())
        .then(data => {

            // 🧠 Transform API data
            const labels = data.accountable_factor.map(i => i.accountable_factors);
            const series = data.accountable_factor.map(i => Number(i.total_rows));

            // 🛡 Safety check
            if (!labels.length || !series.length) {
                console.warn("No data available for pie chart");
                return;
            }

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

            // 🚀 Render chart
            new ApexCharts(
                document.querySelector("#simple_pie_chart"),
                options
            ).render();

        })
        .catch(err => console.error("API Chart Error:", err));

        // Cause Issue Chart
        // 🎨 Colors for distributed bars
        const chartColorsRoot = [
            "#4CAF50", "#FF9800", "#2196F3", "#9C27B0", "#E91E63",
            "#3F51B5", "#009688", "#FFC107", "#795548", "#607D8B"
        ];

        fetch("/dashboard/cause-issue", {
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {

            // 🧠 Handle nested array
            const apiData = data[0];   // <-- important

            // 🧠 Transform API data
            const labels = apiData.map(i => i.cause_issue);
            const values = apiData.map(i => Number(i.total_rows));

            if (!labels.length || !values.length) {
                console.warn("No data for cause issues bar chart");
                return;
            }

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

            // 🚀 Render chart
            new ApexCharts(
                document.querySelector("#custom_datalabels_bar"),
                options
            ).render();

        })
        .catch(err => console.error("Cause Issues Chart API Error:", err));

        // Root Cause
        // 🎨 Donut colors
        const chartColorsRootCause = ["#E53935", "#43A047"];

        fetch("/dashboard/root-cause", {
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {

            // 🧠 handle nested API array
            const apiData = data[0];   // <-- important

            const labels = apiData.map(i => i.root_cause);
            const series = apiData.map(i => Number(i.total_rows));

            if (!labels.length || !series.length) {
                console.warn("No data for donut chart");
                return;
            }

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

            // 🚀 Render donut
            new ApexCharts(
                document.querySelector("#simple_dount_chart"),
                options
            ).render();

        })
        .catch(err => console.error("Root Cause Donut API Error:", err));


        

    </script>
</body>

</html>