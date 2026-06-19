<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<body>
    <div id="layout-wrapper">@include('partials.bodyheader')</div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="page-title-box"><h4 class="mb-0 font-size-18">Root Cause Analytics</h4></div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Cause of Issue — Pareto</h5></div>
                            <div class="card-body"><div id="paretoChart"></div></div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Issues per Month</h5></div>
                            <div class="card-body"><div id="trendChart"></div></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Root Cause</h5></div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light"><tr><th>Root Cause</th><th>Count</th></tr></thead>
                                    <tbody>
                                        @forelse($rootCause as $label => $n)
                                            <tr><td>{{ $label }}</td><td>{{ $n }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted text-center py-3">No data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Accountable Factors</h5></div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light"><tr><th>Accountable Factor</th><th>Count</th></tr></thead>
                                    <tbody>
                                        @forelse($accountable as $label => $n)
                                            <tr><td>{{ $label }}</td><td>{{ $n }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted text-center py-3">No data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.script')
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        const pareto = @json($pareto);
        new ApexCharts(document.querySelector("#paretoChart"), {
            chart: { height: 380, type: 'line', toolbar: { show: false } },
            series: [
                { name: 'Count', type: 'column', data: pareto.map(p => p.count) },
                { name: 'Cumulative %', type: 'line', data: pareto.map(p => p.cum_pct) }
            ],
            stroke: { width: [0, 3] },
            colors: ['#1f58c7', '#f1734f'],
            xaxis: { categories: pareto.map(p => p.label), labels: { rotate: -45, trim: true } },
            yaxis: [
                { title: { text: 'Count' } },
                { opposite: true, max: 100, title: { text: 'Cumulative %' } }
            ],
            dataLabels: { enabled: false }
        }).render();

        new ApexCharts(document.querySelector("#trendChart"), {
            chart: { height: 380, type: 'line', toolbar: { show: false } },
            series: [{ name: 'Issues', data: @json($trendCounts) }],
            xaxis: { categories: @json($trendLabels) },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#34c38f'],
            markers: { size: 4 }
        }).render();
    </script>
</body>
</html>
