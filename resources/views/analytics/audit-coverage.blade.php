<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<body>
    <div id="layout-wrapper">@include('partials.bodyheader')</div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="page-title-box"><h4 class="mb-0 font-size-18">Audit Coverage</h4></div>

                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('analytics.audit-coverage') }}" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">From</label>
                                <input type="date" name="date_from" value="{{ $from }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">To</label>
                                <input type="date" name="date_to" value="{{ $to }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">LDA</label>
                                <select name="user" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->employeeid }}" @selected($user === $u->employeeid)>{{ $u->first_name }} {{ $u->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <a href="{{ route('analytics.audit-coverage') }}" class="btn btn-sm btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card"><div class="card-body text-center">
                            <h6 class="text-muted">Coverage</h6>
                            <h2 class="mb-0 {{ $coverage >= 80 ? 'text-success' : 'text-warning' }}">{{ $coverage }}%</h2>
                            <small class="text-muted">{{ $evaluated }} of {{ $totalLda }} LDAs evaluated</small>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body text-center">
                            <h6 class="text-muted">Evaluated</h6>
                            <h2 class="mb-0 text-success">{{ $evaluated }}</h2>
                            <small class="text-muted">LDAs with ≥ 1 evaluation</small>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body text-center">
                            <h6 class="text-muted">Not Audited</h6>
                            <h2 class="mb-0 {{ $notAudited > 0 ? 'text-danger' : 'text-success' }}">{{ $notAudited }}</h2>
                            <small class="text-muted">No evaluations in range</small>
                        </div></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>LDA</th><th>Employee ID</th><th>Evaluations</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $r)
                                        <tr class="{{ $r->eval_count === 0 ? 'table-warning' : '' }}">
                                            <td>{{ $r->first_name }} {{ $r->last_name }}</td>
                                            <td>{{ $r->employeeid }}</td>
                                            <td>{{ $r->eval_count }}</td>
                                            <td>
                                                @if($r->eval_count === 0)
                                                    <span class="badge bg-warning">Not audited</span>
                                                @else
                                                    <span class="badge bg-success">Covered</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No LDAs found.</td></tr>
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
    <script>
        document.querySelectorAll('.dropdown-choices').forEach(function (el) {
            new Choices(el, { itemSelectText: '', shouldSort: false });
        });
    </script>
</body>
</html>
