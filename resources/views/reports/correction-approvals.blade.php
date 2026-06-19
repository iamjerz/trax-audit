<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<style>
    .sc-diff { font-size: 12px; white-space: pre-wrap; margin: 0; }
    .sc-diff .old { color: #d63939; }
    .sc-diff .new { color: #2fb344; }
</style>
<body>
    <div id="layout-wrapper">@include('partials.bodyheader')</div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="page-title-box"><h4 class="mb-0 font-size-18">Score Correction Approvals</h4></div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="btn-group btn-group-sm mb-3" role="group">
                            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
                                <a href="{{ route('reports.corrections', ['status' => $key]) }}"
                                   class="btn {{ $status === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                            @endforeach
                        </div>

                        <div class="text-muted mb-2 font-size-12">{{ $rows->count() }} correction(s)</div>

                        @php
                            // Field labels for the change diff
                            $labels = [
                                'ver_outcome_1' => 'Verification 1', 'ver_outcome_2' => 'Verification 2',
                                'ver_comment_1' => 'Verification 1 — comment', 'ver_comment_2' => 'Verification 2 — comment',
                                'pc_outcome_1' => 'Process Compliance 1', 'pc_outcome_2' => 'Process Compliance 2',
                                'pc_outcome_3' => 'Process Compliance 3', 'pc_outcome_4' => 'Process Compliance 4',
                                'pc_comment_1' => 'Process Compliance 1 — comment', 'pc_comment_2' => 'Process Compliance 2 — comment',
                                'pc_comment_3' => 'Process Compliance 3 — comment', 'pc_comment_4' => 'Process Compliance 4 — comment',
                                'eng_outcome_1' => 'Engagement 1', 'eng_outcome_2' => 'Engagement 2',
                                'eng_outcome_3' => 'Engagement 3', 'eng_outcome_4' => 'Engagement 4',
                                'eng_comment_1' => 'Engagement 1 — comment', 'eng_comment_2' => 'Engagement 2 — comment',
                                'eng_comment_3' => 'Engagement 3 — comment', 'eng_comment_4' => 'Engagement 4 — comment',
                                'ver_total' => 'Verification total', 'pc_total' => 'Process Compliance total',
                                'eng_total' => 'Engagement total', 'overall' => 'Overall score',
                            ];
                            // Format a stored value into something human-readable
                            $fmt = function ($key, $val) {
                                if (str_contains($key, 'comment')) {
                                    return ($val === null || $val === '') ? '(blank)' : $val;
                                }
                                if (str_contains($key, 'outcome')) {
                                    if ($val === null || $val === '') return '—';
                                    if (str_starts_with($key, 'ver_outcome')) {
                                        return ((string) $val === '100') ? 'Pass' : (((string) $val === '0') ? 'Fail' : $val);
                                    }
                                    $v = (int) $val;
                                    return $v === 0 ? 'Not Met' : (($v === 5 || $v === 8) ? 'Coached' : 'Met');
                                }
                                return ($val === null || $val === '') ? '—' : $val;
                            };
                        @endphp

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Requested By</th>
                                        <th>Overall (current → proposed)</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th style="width:260px;">Decision</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $r)
                                        @php
                                            $changes = [];
                                            foreach ($labels as $key => $label) {
                                                $o = data_get($r->old_values, $key);
                                                $n = data_get($r->new_values, $key);
                                                if ((string) $o !== (string) $n) {
                                                    $changes[] = ['label' => $label, 'key' => $key, 'old' => $o, 'new' => $n];
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $r->invoice_id ?? $r->audit_id }}
                                                <a href="/ticket/view/{{ $r->audit_id }}" target="_blank" class="d-block font-size-11">view evaluation</a>
                                            </td>
                                            <td class="font-size-13">{{ $r->requester ?: $r->changed_by }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ data_get($r->old_values, 'overall', '—') }}%</span>
                                                →
                                                <span class="badge bg-primary">{{ data_get($r->new_values, 'overall', '—') }}%</span>
                                                <a class="d-block font-size-11 mt-1" data-bs-toggle="collapse" href="#chg{{ $r->id }}" role="button">
                                                    <i class="bx bx-list-ul"></i> What changed ({{ count($changes) }})
                                                </a>
                                            </td>
                                            <td class="font-size-12" style="max-width:240px; white-space:normal;">{{ $r->reason }}</td>
                                            <td>
                                                @php $sc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$r->status] ?? 'secondary'; @endphp
                                                <span class="badge bg-{{ $sc }}">{{ ucfirst($r->status) }}</span>
                                                @if($r->decision_note)
                                                    <div class="text-muted font-size-11 mt-1">{{ $r->decision_note }}</div>
                                                @endif
                                                @if($r->approved_by)
                                                    <div class="text-muted font-size-11">by {{ trim($r->approver) ?: $r->approved_by }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($r->status === 'pending')
                                                    <form method="POST" action="{{ route('reports.corrections.approve', $r->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Approve and apply these scores to the evaluation?');">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('reports.corrections.reject', $r->id) }}" class="mt-1">
                                                        @csrf
                                                        <textarea name="decision_note" class="form-control form-control-sm mb-1" rows="1" placeholder="Reason (optional)"></textarea>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted font-size-12">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="border-0">
                                            <td colspan="6" class="p-0 border-0">
                                                <div class="collapse" id="chg{{ $r->id }}">
                                                    <div class="p-3 bg-light border-start border-3 border-primary">
                                                        <div class="fw-semibold mb-2 font-size-13">Proposed changes</div>
                                                        @forelse($changes as $c)
                                                            <div class="mb-1 font-size-13">
                                                                <strong>{{ $c['label'] }}:</strong>
                                                                <span class="text-danger">{{ $fmt($c['key'], $c['old']) }}</span>
                                                                <i class="bx bx-right-arrow-alt"></i>
                                                                <span class="text-success">{{ $fmt($c['key'], $c['new']) }}</span>
                                                            </div>
                                                        @empty
                                                            <span class="text-muted font-size-13">No field-level differences detected.</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">No corrections.</td></tr>
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
</body>
</html>
