<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<body>
    <div id="layout-wrapper">@include('partials.bodyheader')</div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="page-title-box"><h4 class="mb-0 font-size-18">Auditor Productivity</h4></div>

                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('analytics.auditor-productivity') }}" class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">From</label>
                                <input type="date" name="date_from" value="{{ $from }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">To</label>
                                <input type="date" name="date_to" value="{{ $to }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Auditor</label>
                                <select name="user" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->employeeid }}" @selected($user === $u->employeeid)>{{ $u->first_name }} {{ $u->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <a href="{{ route('analytics.auditor-productivity') }}" class="btn btn-sm btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="text-muted mb-2 font-size-12">{{ count($rows) }} auditor(s)</div>
                        <div id="auditorChart"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Auditor</th>
                                        <th>Evaluations</th>
                                        <th>Avg Score Given</th>
                                        <th>Pass Rate</th>
                                        <th>Last Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $r)
                                        <tr>
                                            <td>{{ $r['auditor'] }}</td>
                                            <td>{{ $r['count'] }}</td>
                                            <td>{{ $r['avg'] }}%</td>
                                            <td>
                                                <span class="badge {{ $r['pass_rate'] >= 75 ? 'bg-success' : 'bg-warning' }}">{{ $r['pass_rate'] }}%</span>
                                            </td>
                                            <td>{{ $r['last'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No evaluations in range.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.script')
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        new ApexCharts(document.querySelector("#auditorChart"), {
            chart: { type: 'bar', height: 360, toolbar: { show: false } },
            series: [{ name: 'Evaluations', data: @json($chartCounts) }],
            xaxis: { categories: @json($chartLabels), labels: { rotate: -45 } },
            colors: ['#1f58c7'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            dataLabels: { enabled: true }
        }).render();

        document.querySelectorAll('.dropdown-choices').forEach(function (el) {
            new Choices(el, { itemSelectText: '', shouldSort: false });
        });
    </script>
</body>
</html>
