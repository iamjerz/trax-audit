<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css">
@include('partials.header')
<style>
    /* Date Range Picker (daterangepicker.com) theming — matches this app's
       primary accent (#556ee6), same as the QA/Recon dashboards. */
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
</style>
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
                                <label class="form-label font-size-13 mb-1">Date Range</label>
                                <input type="text" id="ap-date-range" class="form-control form-control-sm" placeholder="All dates" readonly>
                                <input type="hidden" name="date_from" id="ap-date-from" value="{{ $from }}">
                                <input type="hidden" name="date_to" id="ap-date-to" value="{{ $to }}">
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
                                <label class="form-label font-size-13 mb-1 d-block">Calibration</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="exclude_calibration" value="1" id="ap-exclude-calibration" @checked($excludeCalibration)>
                                    <label class="form-check-label" for="ap-exclude-calibration">Remove calibration tickets</label>
                                </div>
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
    <!-- Date Range Picker (daterangepicker.com) — same widget as dashboard-qa,
         needs jQuery + Moment.js loaded first. Kept in its own separate
         script block, apart from the ApexCharts/Choices.js block below, so a
         problem in either of those can't stop this one from running. -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
    <script>
        console.log('[auditor-productivity] date range script loaded', {
            jquery: typeof window.jQuery,
            moment: typeof window.moment,
            daterangepicker: typeof (window.jQuery && window.jQuery.fn.daterangepicker)
        });

        function apDaysAgo(n) {
            return moment().startOf('day').subtract(n, 'days');
        }
        function apMonthsAgo(n) {
            return moment().startOf('day').subtract(n, 'months');
        }

        const apDateInput = $('#ap-date-range');

        apDateInput.daterangepicker({
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
                'Yesterday': [apDaysAgo(1), apDaysAgo(1)],
                'Last 7 days': [apDaysAgo(7), moment().startOf('day')],
                'Last 30 days': [apDaysAgo(30), moment().startOf('day')],
                'Last 6 months': [apMonthsAgo(6), moment().startOf('day')],
                'Last 1 year': [apMonthsAgo(12), moment().startOf('day')]
            }
        });

        // Restore the visible label after a server round-trip (the hidden
        // inputs already carry $from/$to via their `value` attributes).
        @if($from && $to)
        apDateInput.val(
            moment('{{ $from }}').format('MMM D, YYYY') + ' - ' + moment('{{ $to }}').format('MMM D, YYYY')
        );
        @endif

        apDateInput.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY'));
            document.getElementById('ap-date-from').value = picker.startDate.format('YYYY-MM-DD');
            document.getElementById('ap-date-to').value = picker.endDate.format('YYYY-MM-DD');
            this.form.submit();
        });

        apDateInput.on('cancel.daterangepicker', function () {
            $(this).val('');
            document.getElementById('ap-date-from').value = '';
            document.getElementById('ap-date-to').value = '';
            this.form.submit();
        });
    </script>

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
