// ================================================================
// Recon Dashboard
// Filters: Date Range (on recon_call_date) + Carrier Code + Client Code +
// Manager/Supervisor scope (All / My Team / a specific person). Mirrors the
// QA dashboard's filter bar — a single set of filters drives the cards,
// aging card, charts and table.
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

// 🔧 Shared filter values (date range + carrier + client + scope) for every
// AJAX call — folded in here once so every endpoint (including the status
// count cards, which previously got no scope at all) picks up all of them
// uniformly, instead of each call site having to remember to merge scope in.
function reconScope() {
    return $('#chartFilter').val() || '';
}

function reconFilterData(extra) {
    const from = $('#recon-date-from').val();
    const to = $('#recon-date-to').val();
    const carrierCode = $('#recon-carrier-code').val();
    const clientCode = $('#recon-client-code').val();
    const scope = reconScope();
    const data = Object.assign({}, extra || {});
    if (from) data.date_from = from;
    if (to) data.date_to = to;
    if (carrierCode) data.carrier_code = carrierCode;
    if (clientCode) data.client_code = clientCode;
    if (scope) data.scope = scope;
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
        data: reconFilterData(),
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
        data: reconFilterData(),
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
        data: reconFilterData(),
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
        data: reconFilterData(),
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
            shouldSort: false,
            allowHTML: false
        });
    }

    const reconCarrierChoices = new Choices(document.getElementById('recon-carrier-code'), {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false,
        allowHTML: false
    });

    const reconClientChoices = new Choices(document.getElementById('recon-client-code'), {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false,
        allowHTML: false
    });

    // Populate Carrier Code + Client Code + Manager/Supervisor options.
    $.ajax({
        url: '/dashboard-recon-filter-options',
        type: 'GET',
        dataType: 'json',
        success: function (opts) {
            if (Array.isArray(opts.carrier_codes)) {
                reconCarrierChoices.setChoices(
                    opts.carrier_codes.map(c => ({ value: c, label: c })),
                    'value', 'label', false
                );
            }
            if (Array.isArray(opts.client_codes)) {
                reconClientChoices.setChoices(
                    opts.client_codes.map(c => ({ value: c, label: c })),
                    'value', 'label', false
                );
            }
            if (reconChoices && opts.managers) {
                // opts.managers is {"Managers": [{value,label}, ...], "SMEs": [...], ...}
                // Choices.js groups need {label, id, choices: [...]}, appended
                // after the two baked-in "All Tickets"/"My Team" <option>s.
                const groups = Object.keys(opts.managers).map((groupName, idx) => ({
                    label: groupName,
                    id: idx + 1,
                    choices: opts.managers[groupName]
                }));
                reconChoices.setChoices(groups, 'value', 'label', false);
            }
        },
        error: function () { console.warn('Could not load recon filter options'); }
    });

    // Date Range picker (daterangepicker.com) — same widget/config as the QA
    // dashboard: presets + calendars always visible together, one click
    // applies (whether it's a preset or a manual range), theme matched to
    // #556ee6. See dashboard.blade.php for the full rationale on the options
    // below (autoUpdateInput/autoApply/alwaysShowCalendars).
    function reconDaysAgo(n) {
        return moment().startOf('day').subtract(n, 'days');
    }

    function reconMonthsAgo(n) {
        return moment().startOf('day').subtract(n, 'months');
    }

    const reconDateInput = $('#recon-date-range');

    reconDateInput.daterangepicker({
        autoUpdateInput: false, // keep the "All dates" placeholder until a range is actually chosen
        autoApply: true, // no separate confirm click, for both presets and manual picks
        alwaysShowCalendars: true, // presets + calendars visible together
        maxDate: moment(), // this dashboard only ever has past recon data
        locale: {
            format: 'MMM D, YYYY',
            separator: ' - '
        },
        ranges: {
            'Today': [moment().startOf('day'), moment().startOf('day')],
            'Yesterday': [reconDaysAgo(1), reconDaysAgo(1)],
            'Last 7 days': [reconDaysAgo(7), moment().startOf('day')],
            'Last 30 days': [reconDaysAgo(30), moment().startOf('day')],
            'Last 6 months': [reconMonthsAgo(6), moment().startOf('day')],
            'Last 1 year': [reconMonthsAgo(12), moment().startOf('day')]
        }
    });

    reconDateInput.on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY'));
        $('#recon-date-from').val(picker.startDate.format('YYYY-MM-DD'));
        $('#recon-date-to').val(picker.endDate.format('YYYY-MM-DD'));
        reconReloadAll();
    });

    reconDateInput.on('cancel.daterangepicker', function () {
        $(this).val('');
        $('#recon-date-from').val('');
        $('#recon-date-to').val('');
        reconReloadAll();
    });

    // Initial load
    reconReloadAll();

    // Filter changes
    $('#chartFilter').on('change', reconReloadAll);
    $('#recon-carrier-code').on('change', reconReloadAll);
    $('#recon-client-code').on('change', reconReloadAll);
    $('#recon-apply').on('click', reconReloadAll);
    $('#recon-reset').on('click', function () {
        $('#recon-date-from').val('');
        $('#recon-date-to').val('');
        reconDateInput.val('');
        const reconPickerInstance = reconDateInput.data('daterangepicker');
        if (reconPickerInstance) {
            reconPickerInstance.setStartDate(moment());
            reconPickerInstance.setEndDate(moment());
        }
        if (reconChoices) reconChoices.setChoiceByValue('');
        if (reconCarrierChoices) reconCarrierChoices.setChoiceByValue('');
        if (reconClientChoices) reconClientChoices.setChoiceByValue('');
        reconReloadAll();
    });

    // Auto refresh
    setInterval(reconReloadAll, 15000);
});
