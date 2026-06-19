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
                            <h4 class="mb-0 font-size-18">Overdue Action Items <small class="text-muted">(open 7+ days)</small></h4>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('recon-overdue') }}" class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">LDA Name</label>
                                <input type="text" name="name" value="{{ $f_name }}" class="form-control form-control-sm" placeholder="Search name...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Client</label>
                                <select name="client_code" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($clientOptions as $opt)
                                        <option value="{{ $opt }}" @selected($f_client === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Carrier</label>
                                <select name="carrier_code" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($carrierOptions as $opt)
                                        <option value="{{ $opt }}" @selected($f_carrier === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Region</label>
                                <select name="region" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($regionOptions as $opt)
                                        <option value="{{ $opt }}" @selected($f_region === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Status</label>
                                <select name="status" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($statusOptions as $opt)
                                        <option value="{{ $opt }}" @selected($f_status === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label font-size-13 mb-1">Min days open</label>
                                <input type="number" min="7" name="min_days" value="{{ $minDays }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <a href="{{ route('recon-overdue') }}" class="btn btn-sm btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="text-muted mb-2 font-size-12">{{ $rows->count() }} overdue item(s)</div>

                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Submission ID</th>
                                        <th>Client</th>
                                        <th>Carrier</th>
                                        <th>Region</th>
                                        <th>Status</th>
                                        <th>Call Date</th>
                                        <th>Days Open</th>
                                        <th>LDA</th>
                                        <th class="text-end">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $r)
                                        <tr>
                                            <td>{{ $r->submission_id }}</td>
                                            <td>{{ $r->client_code }}</td>
                                            <td>{{ $r->carrier_code }}</td>
                                            <td>{{ $r->region }}</td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $r->status }}</span></td>
                                            <td>{{ $r->recon_call_date }}</td>
                                            <td>
                                                <span class="badge {{ $r->days_open >= 15 ? 'bg-danger' : 'bg-warning' }}">
                                                    {{ $r->days_open }} day(s)
                                                </span>
                                            </td>
                                            <td>{{ $r->lda_name }}</td>
                                            <td class="text-end">
                                                <a href="/recon-ticket-view/{{ $r->submission_id }}" target="_blank" class="btn btn-sm btn-light">Open</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted py-4">No overdue items. 🎉</td></tr>
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
