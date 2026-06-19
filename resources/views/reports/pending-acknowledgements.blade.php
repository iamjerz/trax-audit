<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
                            <h4 class="mb-0 font-size-18">Pending Acknowledgements</h4>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.pending-acknowledgements') }}" class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Waiting at least (days)</label>
                                <input type="number" min="0" name="days" value="{{ $days }}" class="form-control form-control-sm" placeholder="0">
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
                                <a href="{{ route('reports.pending-acknowledgements') }}" class="btn btn-sm btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="text-muted mb-2 font-size-12">{{ $rows->count() }} evaluation(s) awaiting acknowledgement</div>

                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>LDA</th>
                                        <th>Supervisor</th>
                                        <th>Audit Date</th>
                                        <th>Waiting</th>
                                        <th class="text-end">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $r)
                                        <tr>
                                            <td>{{ $r->invoice_id }}</td>
                                            <td>{{ $r->lda_name }} <span class="text-muted">({{ $r->lda_id }})</span></td>
                                            <td>{{ $r->sup_name }}</td>
                                            <td>{{ $r->audit_date_1 }}</td>
                                            <td>
                                                @if(!is_null($r->days_waiting))
                                                    <span class="badge {{ $r->days_waiting >= 7 ? 'bg-danger' : 'bg-warning' }}">
                                                        {{ $r->days_waiting }} day(s)
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="/ticket/view/{{ $r->audit_id }}" target="_blank" class="btn btn-sm btn-light">Open</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Nothing pending — all evaluations acknowledged.</td></tr>
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
