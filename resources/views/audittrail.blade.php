<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<style>
    .audit-diff { font-size: 11px; white-space: pre-wrap; margin: 0; }
    .audit-diff .old { color: #d63939; }
    .audit-diff .new { color: #2fb344; }
    .badge-event { text-transform: capitalize; }
    .table-audit td { vertical-align: top; }
</style>
<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Audit Trail</h4>
                            <a href="{{ route('export.audit-trail', request()->query()) }}" class="btn btn-sm btn-success">
                                <i class="bx bx-download"></i> Export to Excel
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        {{-- Filters --}}
                        <form method="GET" action="{{ route('audit-trail') }}" class="row g-2 mb-3">
                            <div class="col-md-4">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="form-control" placeholder="Search actor, action, target...">
                            </div>
                            <div class="col-md-3">
                                <select name="event" class="form-select">
                                    <option value="">All events</option>
                                    @foreach ($events as $ev)
                                        <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ ucfirst(str_replace('_', ' ', $ev)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="From date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="To date">
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>

                        <div class="text-muted mb-2 font-size-12">{{ $logs->total() }} record(s)</div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-audit mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:150px;">When</th>
                                        <th style="width:160px;">Actor</th>
                                        <th style="width:120px;">Event</th>
                                        <th>Description</th>
                                        <th style="width:240px;">Changes</th>
                                        <th style="width:120px;">IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td class="font-size-12">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                            <td class="font-size-12">
                                                {{ $log->actor_name ?: '—' }}
                                                @if ($log->employeeid)
                                                    <div class="text-muted">{{ $log->employeeid }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $colors = [
                                                        'login' => 'success', 'logout' => 'secondary', 'login_failed' => 'danger',
                                                        'created' => 'primary', 'updated' => 'info', 'deleted' => 'danger',
                                                        'status_changed' => 'warning', 'assigned' => 'info',
                                                        'password_changed' => 'warning', 'access_updated' => 'warning',
                                                        'commented' => 'secondary',
                                                    ];
                                                    $color = $colors[$log->event] ?? 'dark';
                                                @endphp
                                                <span class="badge bg-{{ $color }} badge-event">{{ str_replace('_', ' ', $log->event) }}</span>
                                            </td>
                                            <td class="font-size-12">
                                                {{ $log->description }}
                                                @if ($log->auditable_type && $log->auditable_type !== 'auth')
                                                    <div class="text-muted">{{ $log->auditable_type }} : {{ $log->auditable_id }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($log->old_values || $log->new_values)
                                                    @php
                                                        $keys = array_unique(array_merge(
                                                            array_keys($log->old_values ?? []),
                                                            array_keys($log->new_values ?? [])
                                                        ));
                                                    @endphp
                                                    <pre class="audit-diff">@foreach ($keys as $key)<strong>{{ $key }}:</strong> @php $o = data_get($log->old_values, $key); $n = data_get($log->new_values, $key); @endphp@if(!is_null($o))<span class="old">{{ is_array($o) ? json_encode($o) : $o }}</span> → @endif<span class="new">{{ is_array($n) ? json_encode($n) : ($n ?? 'null') }}</span>
@endforeach</pre>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="font-size-12">{{ $log->ip_address }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No audit records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $logs->links() }}
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.script')
</body>
</html>
