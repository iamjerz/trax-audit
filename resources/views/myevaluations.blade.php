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
                            <h4 class="mb-0 font-size-18">My Evaluations</h4>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">
                            These are the QA evaluations recorded for you. Please review each one and click
                            <strong>Acknowledge</strong> to confirm you've seen it.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Audit Date</th>
                                        <th>Overall Score</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $r)
                                        <tr>
                                            <td>{{ $r->invoice_id }}</td>
                                            <td>{{ $r->audit_date_1 }}</td>
                                            <td>
                                                <span class="badge {{ $r->overall >= 75 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $r->overall }}%
                                                </span>
                                                @if ($r->corrected)
                                                    <span class="badge bg-info-subtle text-info" title="Scores were corrected">Corrected</span>
                                                @endif
                                                @if ($r->correction_pending)
                                                    <span class="badge bg-warning-subtle text-warning" title="A score correction is awaiting admin approval">Correction pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($r->acknowledged)
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bx bx-check-circle"></i> Acknowledged
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                                @endif

                                                @if (!empty($r->dispute))
                                                    @php $dc = ['open'=>'warning','resolved'=>'success','rejected'=>'danger'][$r->dispute->status] ?? 'secondary'; @endphp
                                                    <div class="mt-1">
                                                        <span class="badge bg-{{ $dc }}-subtle text-{{ $dc }}">Dispute: {{ ucfirst($r->dispute->status) }}</span>
                                                        @if ($r->dispute->status === 'open')
                                                            <div class="text-muted font-size-11 mt-1">Under review by your supervisor.</div>
                                                        @else
                                                            <div class="text-muted font-size-11 mt-1">
                                                                <strong>Outcome:</strong> {{ $r->dispute->resolution_note ?: ucfirst($r->dispute->status) }}
                                                                @if ($r->dispute->resolved_at)
                                                                    <span class="d-block">on {{ \Carbon\Carbon::parse($r->dispute->resolved_at)->format('Y-m-d') }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end" style="white-space: nowrap;">
                                                <a href="{{ route('my-evaluations.show', $r->audit_id) }}" class="btn btn-sm btn-light">View</a>
                                                @if (! $r->acknowledged && (empty($r->dispute) || $r->dispute->status !== 'open'))
                                                    <form method="POST" action="{{ route('my-evaluations.acknowledge', $r->audit_id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Confirm you have reviewed this evaluation?');">
                                                            Acknowledge
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (! $r->acknowledged && (empty($r->dispute) || $r->dispute->status !== 'open'))
                                                    <button type="button" class="btn btn-sm btn-outline-danger dispute-btn"
                                                        data-audit="{{ $r->audit_id }}" data-invoice="{{ $r->invoice_id }}">
                                                        Dispute
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">You have no evaluations yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Dispute modal --}}
    <div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="dispute-form" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Dispute Evaluation <span id="dispute-invoice" class="text-muted font-size-13"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Explain why you disagree with this evaluation. Your supervisor will review it.</p>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Reason for dispute..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Dispute</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.script')
    <script>
        (function () {
            const modalEl = document.getElementById('disputeModal');
            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('dispute-form');
            const base = "{{ url('my-evaluations') }}";

            document.querySelectorAll('.dispute-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    form.action = base + '/' + btn.dataset.audit + '/dispute';
                    document.getElementById('dispute-invoice').textContent = btn.dataset.invoice ? '— ' + btn.dataset.invoice : '';
                    modal.show();
                });
            });
        })();
    </script>
</body>
</html>
