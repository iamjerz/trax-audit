<div class="row">

    <!-- Full width table -->
    <div class="col-xl-12 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h4 class="card-title">List of Audited Ticket</h4>
                <footer class="blockquote-footer mt-1">
                    Verification & Identification Score is less than <code>200%</code> the Overall Score will be automatically <code>0%</code>.
                </footer>
            </div>
            <div class="card-body pb-0">
                <div id="table-gridjs"></div>
            </div>
        </div>
    </div>

    <!-- Second row -->
    <div class="col-xl-12">
        <div class="row align-items-stretch">

            <!-- Chart -->
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
                                <h4 class="card-title">Numbers of Accountable in Impact Factors</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div id="simple_pie_chart" data-colors='["#1f58c7", "#28b765","#f4c238", "#ed5555","#974be0"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

            <!-- Mini cards grid -->
            <!-- <div class="col-xl-6 d-flex">
                <div class="w-100 d-flex flex-column">

                    <div class="row flex-fill g-3">

                        <div class="col-6 d-flex">
                            <div class="card flex-fill h-100">
                                <div class="card-body">
                                    Card 4
                                </div>
                            </div>
                        </div>
                        <div class="col-6 d-flex">
                            <div class="card flex-fill h-100">
                                <div class="card-body d-flex">
                                    Card 4
                                </div>
                            </div>
                        </div>
                        <div class="col-6 d-flex">
                            <div class="card flex-fill h-100">
                                <div class="card-body d-flex">
                                    Card 4
                                </div>
                            </div>
                        </div>
                        <div class="col-6 d-flex">
                            <div class="card flex-fill h-100">
                                <div class="card-body d-flex">
                                    Card 4
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div> -->

        </div>
    </div>

</div>


<!-- <script src="{{ asset('assets/js/individualEval.js') }}"></script> -->
 <script>
    new gridjs.Grid({
    columns: [
        "Audit Date",
        "Invoice Number",
        "Verification & Identification Score",
        "Process Compliance Score",
        "Engagement Score",
        "Over All Score",
        "Audited By"
    ],
    pagination: {
        limit: 20
    },
    search: true,
    sort: false,
    server: {
        url: '/evaluation/individual-recent?id={{ $id }}&date_from={{ $date_from }}&date_to={{ $date_to }}',
        headers: {
            'Accept': 'application/json'
        },
        then: data => {
            return data.recent_ticket.map(row => {

                let score = 0;

                const total = 200;
                const verTotal = (row.ver_total / total) * 100;

                if (verTotal < 100) {
                    score = "0%";
                } else {
                    score = (parseInt(row.pro_total) + parseInt(row.eng_total)) + "%";
                }

                return [
                    row.audit_date,

                    // ✅ Clickable Invoice Number
                    gridjs.html(`
                        <a href="/ticket/view/${row.audit_id}" 
                           target="_blank" 
                           style="color:#1f58c7; text-decoration: underline;">
                            <strong>${row.invoice_id}</strong>
                        </a>
                    `),

                    row.ver_total + "%",
                    row.pro_total + "%",
                    row.eng_total + "%",
                    score,
                    row.created_by_name
                ];
            });
        }
    }
}).render(document.getElementById('table-gridjs'));


// ================================
// GLOBAL SAFE COLOR STORE
// ================================
window.chartColorsRoot = window.chartColorsRoot || [
    "#4CAF50", "#FF9800", "#2196F3", "#9C27B0", "#E91E63",
    "#3F51B5", "#009688", "#FFC107", "#795548", "#607D8B"
];

window.chartColorsPie = window.chartColorsPie || [
    "#4CAF50", "#FF9800", "#2196F3", "#E91E63", "#974be0"
];

// ================================
// HARD SANITIZER
// ================================
function sanitizeNumber(val){
    if (val === null || val === undefined) return null;
    if (typeof val === "string") {
        val = val.replace(/,/g, "").trim();
        if (val === "") return null;
    }
    var n = Number(val);
    if (!isFinite(n)) return null;
    return n;
}

// ================================
// HARD VISIBILITY CHECK
// ================================
function isRenderable(el){
    if (!el) return false;
    const rect = el.getBoundingClientRect();
    return (
        rect.width > 10 &&
        rect.height > 10 &&
        getComputedStyle(el).display !== "none" &&
        getComputedStyle(el).visibility !== "hidden"
    );
}


// ==========================================================
// CHART 1: CAUSE ISSUE BAR CHART (FIXED)
// ==========================================================
(function initCauseIssueChart() {

    var el = document.querySelector("#custom_datalabels_bar");
    if (!el) return;

    // wait until visible + sized
    if (
        el.offsetWidth === 0 ||
        el.offsetHeight === 0 ||
        getComputedStyle(el).display === "none"
    ) {
        setTimeout(initCauseIssueChart, 200);
        return;
    }

    // prevent duplicate render
    if (el.dataset.rendered === "true") return;
    el.dataset.rendered = "true";

    fetch("/evaluation/individual-cause-issue?id={{ $id }}&date_from={{ $date_from }}&date_to={{ $date_to }}", {
        headers: {
            "Accept": "application/json"
        }
    })
    .then(function(res) {
        return res.json();
    })
    .then(function(data) {

        // ✅ FIX: nested array handling
        var apiData = data[0] || [];

        var labels = apiData.map(function(i) {
            return i.cause_issue || "Unknown";
        });

        var values = apiData.map(function(i) {
            var n = Number(i.total_rows);
            return isNaN(n) ? 0 : n;
        });

        if (!labels.length || !values.length) {
            console.warn("No data for cause issues bar chart");
            return;
        }

        var options = {
            series: [{
                data: values
            }],

            chart: {
                type: "bar",
                height: 350,
                toolbar: { show: false }
            },

                title: {
                    text: "Cause Issues",
                    align: "center",
                    floating: true,
                    style: { fontWeight: 600 }
                },

                subtitle: {
                    text: "Category Names as DataLabels inside bars",
                    align: "center"
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

            colors: window.chartColorsRoot,

            dataLabels: {
                enabled: true,
                textAnchor: "start",
                style: {
                    colors: ["#fff"],
                    fontSize: "12px",
                    fontWeight: "600"
                },
                formatter: function(val, opts) {
                    var label = opts.w.globals.labels[opts.dataPointIndex];
                    return label + ": " + val;
                }
            },

            xaxis: {
                categories: labels
            },

            yaxis: {
                labels: { show: false }
            },

            tooltip: {
                theme: "dark",
                y: {
                    formatter: function(val) {
                        return val + " cases";
                    }
                }
            }
        };

        el.innerHTML = "";
        new ApexCharts(el, options).render();

    })
    .catch(function(err) {
        console.error("Cause Issues Chart API Error:", err);
    });

})();



// ==========================================================
// CHART 2: ACCOUNTABLE FACTOR PIE CHART (FIXED)
// ==========================================================
(function initAccountableFactorChart() {

    var el = document.querySelector("#simple_pie_chart");
    if (!el) return;

    // wait until visible + sized
    if (
        el.offsetWidth === 0 ||
        el.offsetHeight === 0 ||
        getComputedStyle(el).display === "none"
    ) {
        setTimeout(initAccountableFactorChart, 200);
        return;
    }

    // prevent duplicate render
    if (el.dataset.rendered === "true") return;
    el.dataset.rendered = "true";

    fetch("/evaluation/individual-accountable-factor?id={{ $id }}&date_from={{ $date_from }}&date_to={{ $date_to }}", {
        headers: {
            "Accept": "application/json"
        }
    })
    .then(function(res) {
        return res.json();
    })
    .then(function(data) {

        // ✅ FIX: nested array structure
        var api = data[0] || [];

        var labels = api.map(function(i) {
            return i.accountable_factors || "Unknown";
        });

        var series = api.map(function(i) {
            var n = Number(i.total_rows);
            return isNaN(n) ? 0 : n;
        });

        if (!labels.length || !series.length) {
            console.warn("No data available for pie chart");
            return;
        }

        var options = {
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

            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    var value = opts.w.globals.series[opts.seriesIndex];
                    return value + " (" + val.toFixed(1) + "%)";
                },
                style: {
                    fontSize: "13px",
                    fontWeight: "600"
                }
            },

            tooltip: {
                y: {
                    formatter: function(value) {
                        return value + " cases";
                    }
                }
            },

            colors: window.chartColorsPie
        };

        el.innerHTML = "";
        new ApexCharts(el, options).render();

    })
    .catch(function(err) {
        console.error("Accountable Factor Chart API Error:", err);
    });

})();


 </script>