document.addEventListener('DOMContentLoaded', function () {
    const element = document.getElementById('chartFilter');

    const choices = new Choices(element, {
        searchEnabled: true,
        itemSelectText: '',
    });
});

$(document).ready(function () {

    // 🎯 Smooth Count Animation
    function animateCount(el, end, duration = 1000) {
        let start = 0;
        let startTime = null;

        function animate(currentTime) {
            if (!startTime) startTime = currentTime;

            let progress = Math.min((currentTime - startTime) / duration, 1);
            let value = Math.floor(progress * end);

            el.text(value);

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                el.text(end); // ensure exact final value
            }
        }

        requestAnimationFrame(animate);
    }

    // 📡 Load Counts from Backend
    function loadStatusCounts() {
        $.ajax({
            url: '/dashboard-recon-cards', // your route
            type: 'GET',
            dataType: 'json',

            success: function (data) {
                animateCount($('#total-evaluations'), data.total || 0);
                animateCount($('#todo-count'), data.todo || 0);
                animateCount($('#closed-count'), data.closed || 0);
                animateCount($('#pending-count'), data.pending || 0);
                animateCount($('#inprogress-count'), data.in_progress || 0);
            },

            error: function (xhr, status, error) {
                console.error('Error fetching counts:', error);

                // fallback to 0 if error
                $('#total-evaluations').text(0);
                $('#todo-count').text(0);
                $('#closed-count').text(0);
                $('#pending-count').text(0);
                $('#inprogress-count').text(0);
            }
        });
    }

    // 🚀 Initial Load
    loadStatusCounts();

    // 🔄 Auto Refresh every 10 seconds (optional)
    setInterval(loadStatusCounts, 10000);

});
function animateValue(el, start, end, duration = 600) {
    let startTime = null;

    function animate(currentTime) {
        if (!startTime) startTime = currentTime;
        let progress = Math.min((currentTime - startTime) / duration, 1);
        let value = Math.floor(progress * (end - start) + start);
        el.text(value);

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            el.text(end);
        }
    }

    requestAnimationFrame(animate);
}

function loadTop10() {

    let scope = $('#chartFilter').val() || 'all';

    $.ajax({
        url: '/dashboard-recon-table-top10',
        type: 'GET',
        data: { scope: scope },

        success: function (data) {

            let rows = '';

            data.forEach(item => {
                rows += `
                    <tr>
                        <td><strong>${item.client_code ?? '-'}</strong></td>
                        <td><strong>${item.carrier_code ?? '-'}</strong></td>

                        <td class="count total">${item.total}</td>
                        <td class="count todo">${item.todo}</td>
                        <td class="count pending">${item.pending}</td>
                        <td class="count inprogress">${item.in_progress}</td>
                        <td class="count closed">${item.closed}</td>
                    </tr>
                `;
            });

            $('#top10-body').html(rows);

            // 🔥 Animate numbers after rendering
            $('#top10-body .count').each(function () {
                let finalVal = parseInt($(this).text()) || 0;
                $(this).text(0);
                animateValue($(this), 0, finalVal);
            });
        },

        error: function () {
            console.error('Failed to load Top 10 data');
        }
    });
}
$(document).ready(function () {

    // 🚀 Initial load
    loadTop10();

    // 🔄 On dropdown change
    $('#chartFilter').on('change', function () {
        loadTop10();
    });

    // 🔁 Optional auto-refresh
    setInterval(loadTop10, 15000);
});

// Global chart instances
let clientChart = null;
let carrierChart = null;

$(document).ready(function () {

    // 🔹 Common chart options builder
    function buildChartOptions(title, categories, data, color) {
        return {
            chart: {
                type: 'bar',
                height: 400,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 1000,
                    animateGradually: {
                        enabled: true,
                        delay: 100
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 500
                    }
                }
            },
            series: [{
                name: 'Total Tickets',
                data: data
            }],
            xaxis: {
                categories: categories,
                labels: {
                    rotate: -45,
                    trim: false
                },
                title: {
                    text: title
                }
            },
            yaxis: {
                title: {
                    text: 'Count'
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false, // column style
                    columnWidth: '55%',
                    borderRadius: 5
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -10,
                formatter: function (val) {
                    return val.toLocaleString();
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " tickets";
                    }
                }
            },
            colors: [color],
            title: {
                text: title,
                align: 'center'
            }
        };
    }

    // 🔹 Load Client Chart
    function loadClientChart() {

        let scope = $('#chartFilter').val() || 'all';

        $.ajax({
            url: '/dashboard-recon-chart-clientcode',
            type: 'GET',
            data: { scope: scope },

            success: function (data) {

                let categories = [];
                let totals = [];

                data.forEach(item => {
                    categories.push(item.client_code ?? 'N/A');
                    totals.push(parseInt(item.total) || 0);
                });

                if (clientChart) {
                    clientChart.updateOptions({
                        xaxis: { categories: categories },
                        series: [{ data: totals }]
                    });
                } else {
                    clientChart = new ApexCharts(
                        document.querySelector("#clientChart"),
                        buildChartOptions('Top 20 Client Codes', categories, totals, '#556ee6')
                    );
                    clientChart.render();
                }
            },

            error: function () {
                console.error('Error loading client chart');
            }
        });
    }

    // 🔹 Load Carrier Chart
    function loadCarrierChart() {

        let scope = $('#chartFilter').val() || 'all';

        $.ajax({
            url: '/dashboard-recon-chart-carriercode',
            type: 'GET',
            data: { scope: scope },

            success: function (data) {

                let categories = [];
                let totals = [];

                data.forEach(item => {
                    categories.push(item.carrier_code ?? 'N/A');
                    totals.push(parseInt(item.total) || 0);
                });

                if (carrierChart) {
                    carrierChart.updateOptions({
                        xaxis: { categories: categories },
                        series: [{ data: totals }]
                    });
                } else {
                    carrierChart = new ApexCharts(
                        document.querySelector("#carrierChart"),
                        buildChartOptions('Top 20 Carrier Codes', categories, totals, '#34c38f')
                    );
                    carrierChart.render();
                }
            },

            error: function () {
                console.error('Error loading carrier chart');
            }
        });
    }

    // 🔹 Load Open Item Aging / SLA
    function loadAging() {
        let scope = $('#chartFilter').val() || 'all';

        $.ajax({
            url: '/dashboard-recon-aging',
            type: 'GET',
            data: { scope: scope },
            success: function (data) {
                const b = data.buckets || {};
                $('#aging-overdue').text(data.overdue || 0);
                $('#aging-0-3').text(b['0-3'] || 0);
                $('#aging-4-7').text(b['4-7'] || 0);
                $('#aging-8-14').text(b['8-14'] || 0);
                $('#aging-15').text(b['15+'] || 0);
            },
            error: function () {
                console.error('Error loading aging data');
            }
        });
    }

    // ✅ INITIAL LOAD
    loadClientChart();
    loadCarrierChart();
    loadAging();

    // ✅ DROPDOWN CHANGE
    $('#chartFilter').on('change', function () {
        loadClientChart();
        loadCarrierChart();
        loadAging();
    });

    // 🔄 AUTO REFRESH (optional)
    setInterval(function () {
        loadClientChart();
        loadCarrierChart();
        loadAging();
    }, 15000);

});