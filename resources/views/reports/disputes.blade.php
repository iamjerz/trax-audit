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
                            <h4 class="mb-0 font-size-18">Evaluation Disputes</h4>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card">
                    <div class="card-body">
                        {{-- Filters --}}
                        <form method="GET" action="{{ route('reports.disputes') }}" class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Status</label>
                                <select name="status" class="form-control form-select-sm dropdown-choices">
                                    @foreach(['open' => 'Open', 'resolved' => 'Resolved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
                                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-size-13 mb-1">Raised By</label>
                                <select name="user" class="form-control form-select-sm dropdown-choices">
                                    <option value="">All</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->employeeid }}" @selected($user === $u->employeeid)>{{ $u->first_name }} {{ $u->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <a href="{{ route('reports.disputes') }}" class="btn btn-sm btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="text-muted mb-2 font-size-12">{{ $disputes->count() }} dispute(s)</div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Raised By</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Raised</th>
                                        <th style="width:320px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($disputes as $d)
                                        <tr>
                                            <td>
                                                {{ $d->invoice_id ?? $d->audit_id }}
                                                <a href="/ticket/view/{{ $d->audit_id }}" target="_blank" class="d-block font-size-11">view evaluation</a>
                                            </td>
                                            <td>{{ $d->raiser_name }} <span class="text-muted">({{ $d->employeeid }})</span></td>
                                            <td style="max-width:280px; white-space:normal;">{{ $d->reason }}</td>
                                            <td>
                                                @php
                                                    $c = ['open' => 'warning', 'resolved' => 'success', 'rejected' => 'danger'][$d->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $c }}">{{ ucfirst($d->status) }}</span>
                                                @if(isset($pendingCorrections[$d->audit_id]))
                                                    <span class="badge bg-info-subtle text-info">Correction pending approval</span>
                                                @endif
                                                @if($d->resolution_note)
                                                    <div class="text-muted font-size-11 mt-1">{{ $d->resolution_note }}</div>
                                                @endif
                                            </td>
                                            <td class="font-size-12">{{ \Carbon\Carbon::parse($d->created_at)->format('Y-m-d') }}</td>
                                            <td>
                                                @php $locked = $d->status === 'open' && isset($pendingCorrections[$d->audit_id]); @endphp
                                                @if($locked && ! ($isAdmin ?? false))
                                                    <span class="text-muted font-size-12">
                                                        <i class="bx bx-lock-alt"></i>
                                                        Locked — a score correction is awaiting admin approval.
                                                    </span>
                                                @elseif($d->status === 'open')
                                                    @if($locked)
                                                        <div class="text-warning font-size-11 mb-1">
                                                            <i class="bx bx-shield-quarter"></i> Admin override — a correction is pending approval.
                                                        </div>
                                                    @endif
                                                    <form method="POST" action="{{ route('reports.disputes.resolve', $d->id) }}">
                                                        @csrf
                                                        <div class="input-group input-group-sm mb-1">
                                                            <select name="status" class="form-select">
                                                                <option value="resolved">Resolve</option>
                                                                <option value="rejected">Reject</option>
                                                            </select>
                                                        </div>
                                                        <textarea name="resolution_note" class="form-control form-control-sm mb-1" rows="1" placeholder="Resolution note (optional)"></textarea>
                                                        <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                                    </form>
                                                    @unless($locked)
                                                        <a href="{{ route('evaluations.correct', ['auditId' => $d->audit_id, 'dispute_id' => $d->id]) }}"
                                                           class="btn btn-sm btn-outline-warning mt-1">Correct Scores</a>
                                                    @endunless
                                                @else
                                                    <span class="text-muted font-size-12">
                                                        by {{ $d->resolved_by }}
                                                        @if($d->resolved_at) on {{ \Carbon\Carbon::parse($d->resolved_at)->format('Y-m-d') }} @endif
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">No disputes.</td></tr>
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
