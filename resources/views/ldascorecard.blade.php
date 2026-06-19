<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
@include('partials.header')
<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="mb-0 font-size-18">LDA Performance Scorecard</h4>
                        </div>
                    </div>
                </div>

                {{-- LDA selector --}}
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('lda-scorecard') }}" class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label font-size-13 mb-1">Select LDA</label>
                                <select name="employeeid" class="form-select dropdown-choices">
                                    <option value="">Choose an analyst…</option>
                                    @foreach ($ldas as $l)
                                        <option value="{{ $l->employeeid }}" @selected($employeeid === $l->employeeid)>
                                            {{ $l->first_name }} {{ $l->last_name }} ({{ $l->employeeid }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">View Scorecard</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($scorecard)
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-1">{{ $scorecard['lda']->first_name }} {{ $scorecard['lda']->last_name }}</h5>
                            <p class="text-muted mb-0">{{ $scorecard['lda']->employeeid }} &middot; {{ $scorecard['lda']->email }}</p>
                        </div>
                    </div>

                    {{-- Metric cards --}}
                    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-6 g-3">
                        <div class="col">
                            <div class="card"><div class="card-body">
                                <h6 class="font-size-13 text-muted">Evaluations</h6>
                                <h4 class="mb-0">{{ $scorecard['eval_count'] }}</h4>
                            </div></div>
                        </div>
                        <div class="col">
                            <div class="card"><div class="card-body">
                                <h6 class="font-size-13 text-muted">Avg Score</h6>
                                <h4 class="mb-0">{{ $scorecard['avg_score'] }}%</h4>
                            </div></div>
                        </div>
                        <div class="col">
                            <div class="card"><div class="card-body">
                                <h6 class="font-size-13 text-muted">Pass Rate (≥75%)</h6>
                                <h4 class="mb-0 {{ $scorecard['pass_rate'] >= 75 ? 'text-success' : 'text-danger' }}">{{ $scorecard['pass_rate'] }}%</h4>
                                <small class="text-muted">{{ $scorecard['above'] }} pass / {{ $scorecard['below'] }} below</small>
                            </div></div>
                        </div>
                        <div class="col">
                            <div class="card"><div class="card-body">
                                <h6 class="font-size-13 text-muted">Triad Pass Rate</h6>
                                <h4 class="mb-0">{{ $scorecard['triad_pass_rate'] }}%</h4>
                                <small class="text-muted">{{ $scorecard['triad_count'] }} triad(s)</small>
                            </div></div>
                        </div>
                        <div class="col">
                            <div class="card"><div class="card-body">
                                <h6 class="font-size-13 text-muted">Coaching</h6>
                                <h4 class="mb-0">{{ $scorecard['coaching_count'] }}</h4>
                            </div></div>
                        </div>
                        <div class="col">
                            <div class="card"><div class="card-body">
                                <h6 class="font-size-13 text-muted">Open Action Items</h6>
                                <h4 class="mb-0 {{ $scorecard['open_recon'] > 0 ? 'text-warning' : '' }}">{{ $scorecard['open_recon'] }}</h4>
                            </div></div>
                        </div>
                    </div>

                    {{-- Recent evaluations --}}
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Recent Evaluations</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Audit Date</th>
                                            <th>Overall %</th>
                                            <th class="text-end">View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($scorecard['recent'] as $r)
                                            <tr>
                                                <td>{{ $r->invoice_id }}</td>
                                                <td>{{ $r->audit_date_1 }}</td>
                                                <td>
                                                    <span class="badge {{ $r->overall >= 75 ? 'bg-success' : 'bg-danger' }}">{{ $r->overall }}%</span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="/ticket/view/{{ $r->audit_id }}" target="_blank" class="btn btn-sm btn-light">Open</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No evaluations yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif ($employeeid)
                    <div class="alert alert-warning">No data found for the selected analyst.</div>
                @endif

            </div>
        </div>
    </div>

    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.querySelectorAll('.dropdown-choices').forEach(function (el) {
            new Choices(el, { searchEnabled: true, itemSelectText: '' });
        });
    </script>
</body>
</html>
