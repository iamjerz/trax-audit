// ===== Triad Dashboard =====

let criteriaChart = null;

$(document).ready(function () {

    // Smooth count animation
    function animateCount(el, end, duration = 800) {
        let startTime = null;
        end = Number(end) || 0;

        function step(now) {
            if (!startTime) startTime = now;
            const progress = Math.min((now - startTime) / duration, 1);
            el.text(Math.floor(progress * end));
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.text(end);
            }
        }
        requestAnimationFrame(step);
    }

    // ---- Cards ----
    function loadCards() {
        $.ajax({
            url: '/dashboard-triad-cards',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                animateCount($('#triad-total'), data.total);
                animateCount($('#triad-pass'), data.pass);
                animateCount($('#triad-fail'), data.fail);
                animateCount($('#triad-month'), data.this_month);
                $('#triad-pass-rate').text(data.pass_rate ?? 0);
            },
            error: function () {
                ['#triad-total', '#triad-pass', '#triad-fail', '#triad-month', '#triad-pass-rate']
                    .forEach(id => $(id).text(0));
            }
        });
    }

    // ---- Criterion chart + table ----
    function loadCriteria() {
        $.ajax({
            url: '/dashboard-triad-criteria',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                const labels = data.map(d => d.label);
                const passData = data.map(d => d.pass);
                const failData = data.map(d => d.fail);

                renderChart(labels, passData, failData);

                let rows = '';
                data.forEach(d => {
                    rows += `
                        <tr>
                            <td>${d.label}</td>
                            <td>${d.total}</td>
                            <td class="text-success fw-semibold">${d.pass}</td>
                            <td class="text-danger fw-semibold">${d.fail}</td>
                            <td>
                                <span class="badge ${d.pass_rate >= 80 ? 'bg-success' : (d.pass_rate >= 50 ? 'bg-warning' : 'bg-danger')}">
                                    ${d.pass_rate}%
                                </span>
                            </td>
                        </tr>`;
                });
                $('#criteria-body').html(rows || '<tr><td colspan="5" class="text-center text-muted py-3">No data</td></tr>');
            },
            error: function () {
                $('#criteria-body').html('<tr><td colspan="5" class="text-center text-muted py-3">Failed to load</td></tr>');
            }
        });
    }

    function renderChart(labels, passData, failData) {
        const options = {
            chart: {
                type: 'bar',
                height: 460,
                stacked: true,
                toolbar: { show: false },
                animations: { enabled: true, speed: 700 }
            },
            series: [
                { name: 'Pass', data: passData },
                { name: 'Fail', data: failData }
            ],
            colors: ['#34c38f', '#f46a6a'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '70%'
                }
            },
            xaxis: {
                categories: labels,
                title: { text: 'Number of Triads' }
            },
            legend: { position: 'top' },
            dataLabels: { enabled: true },
            tooltip: { shared: true, intersect: false }
        };

        if (criteriaChart) {
            criteriaChart.updateOptions({ xaxis: { categories: labels } });
            criteriaChart.updateSeries([
                { name: 'Pass', data: passData },
                { name: 'Fail', data: failData }
            ]);
        } else {
            criteriaChart = new ApexCharts(document.querySelector('#criteriaChart'), options);
            criteriaChart.render();
        }
    }

    // ---- Evaluator table ----
    function loadEvaluators() {
        $.ajax({
            url: '/dashboard-triad-evaluators',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let rows = '';
                data.forEach(d => {
                    rows += `
                        <tr>
                            <td>${d.evaluator ?? '-'}</td>
                            <td>${d.count}</td>
                            <td>
                                <span class="badge ${d.pass_rate >= 80 ? 'bg-success' : (d.pass_rate >= 50 ? 'bg-warning' : 'bg-danger')}">
                                    ${d.pass_rate}%
                                </span>
                            </td>
                        </tr>`;
                });
                $('#evaluator-body').html(rows || '<tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>');
            },
            error: function () {
                $('#evaluator-body').html('<tr><td colspan="3" class="text-center text-muted py-3">Failed to load</td></tr>');
            }
        });
    }

    function loadAll() {
        loadCards();
        loadCriteria();
        loadEvaluators();
    }

    // Initial load
    loadAll();

    // Auto refresh
    setInterval(loadAll, 30000);
});
