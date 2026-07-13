// ================================================================
// Recon Dashboard
// Filters: Scope (All / My Team) + Date Range (on recon_call_date)
// A single set of filters drives the cards, aging card, charts and table.
// ================================================================

let reconChoices = null;
let clientChart = null;
let carrierChart = null;

// Set counts directly (animations removed)
function animateCount(el, end) {
    el.text(end);
}

function animateValue(el, start, end) {
    el.text(end);
}

// 🔧 Shared filter values (scope + date range) for every AJAX call
function reconScope() {
    return $('#chartFilter').val() || 'all';
}

function reconFilterData(extra) {
    const from = $('#recon-date-from').val();
    const to = $('#recon-date-to').val();
    const data = Object.assign({}, extra || {});
    if (from) data.date_from = from;
    if (to) data.date_to = to;
    return data;
}

// 📡 Status count cards
function loadStatusCounts() {
    $.ajax({
        url: '/dashboard-recon-cards',
        type: 'GET',
        dataType: 'json',
        data: reconFilterData(),
        success: function (data) {
            animateCount($('#total-evaluations'), data.total || 0);
            animateCount($('#todo-count'), data.todo || 0);
            animateCount($('#closed-count'), data.closed || 0);
            animateCount($('#pending-count'), data.pending || 0);
            animateCount($('#inprogress-count'), data.in_progress || 0);
        },
        error: function () {
            $('#total-evaluations, #todo-count, #closed-count, #pending-count, #inprogress-count').text(0);
        }
    });
}

// 📊 Top 10 client/carrier breakdown table
function loadTop10() {
    $.ajax({
        url: '/dashboard-recon-table-top10',
        type: 'GET',
        data: reconFilterData({ scope: reconScope() }),
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

            $('#top10-body .count').each(function () {
                const finalVal = parseInt($(this).text()) || 0;
                $(this).text(0);
                animateValue($(this), 0, finalVal);
            });
        },
        error: function () {
            console.error('Failed to load Top 10 data');
        }
    });
}

// 🔹 Common chart options builder
function buildChartOptions(title, categories, data, color) {
    return {
        chart: {
            type: 'bar',
            height: 400,
            animations: { enabled: false }
        },
        series: [{ name: 'Total Tickets', data: data }],
        xaxis: {
            categories: categories,
            labels: { rotate: -45, trim: false },
            title: { text: title }
        },
        yaxis: { title: { text: 'Count' } },
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 5 } },
        dataLabels: {
            enabled: true,
            offsetY: -10,
            formatter: function (val) { return val.toLocaleString(); }
        },
        tooltip: { y: { formatter: function (val) { return val + " tickets"; } } },
        colors: [color],
        title: { text: title, align: 'center' }
    };
}

// 🔹 Client chart
function loadClientChart() {
    $.ajax({
        url: '/dashboard-recon-chart-clientcode',
        type: 'GET',
        data: reconFilterData({ scope: reconScope() }),
        success: function (data) {
            const categories = [];
            const totals = [];
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
        error: function () { console.error('Error loading client chart'); }
    });
}

// 🔹 Carrier chart
function loadCarrierChart() {
    $.ajax({
        url: '/dashboard-recon-chart-carriercode',
        type: 'GET',
        data: reconFilterData({ scope: reconScope() }),
        success: function (data) {
            const categories = [];
            const totals = [];
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
        error: function () { console.error('Error loading carrier chart'); }
    });
}

// 🔹 Open Item Aging / SLA (excludes items dated today, server-side)
function loadAging() {
    $.ajax({
        url: '/dashboard-recon-aging',
        type: 'GET',
        data: reconFilterData({ scope: reconScope() }),
        success: function (data) {
            const b = data.buckets || {};
            $('#aging-overdue').text(data.overdue || 0);
            $('#aging-0-3').text(b['0-3'] || 0);
            $('#aging-4-7').text(b['4-7'] || 0);
            $('#aging-8-14').text(b['8-14'] || 0);
            $('#aging-15').text(b['15+'] || 0);
        },
        error: function () { console.error('Error loading aging data'); }
    });
}

// 🔁 Reload everything with the current filters
function reconReloadAll() {
    loadStatusCounts();
    loadTop10();
    loadClientChart();
    loadCarrierChart();
    loadAging();
}

$(document).ready(function () {
    const element = document.getElementById('chartFilter');
    if (element) {
        reconChoices = new Choices(element, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        });
    }

    // Initial load
    reconReloadAll();

    // Filter changes
    $('#chartFilter').on('change', reconReloadAll);
    $('#recon-apply').on('click', reconReloadAll);
    $('#recon-reset').on('click', function () {
        $('#recon-date-from').val('');
        $('#recon-date-to').val('');
        if (reconChoices) reconChoices.setChoiceByValue('all');
        reconReloadAll();
    });

    // Auto refresh
    setInterval(reconReloadAll, 15000);
});
