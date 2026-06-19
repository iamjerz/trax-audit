<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>

    @php
        // Outcome options, matching the QA Monitoring form
        $verOpts = ['100' => 'Pass', '0' => 'Fail'];
        $pcOpts = [
            1 => ['10' => 'Met', '5' => 'Coached', '0' => 'Not Met'],
            2 => ['15' => 'Met', '8' => 'Coached', '0' => 'Not Met'],
            3 => ['15' => 'Met', '8' => 'Coached', '0' => 'Not Met'],
            4 => ['10' => 'Met', '5' => 'Coached', '0' => 'Not Met'],
        ];
        $engOpts = [
            1 => ['10' => 'Met', '5' => 'Coached', '0' => 'Not Met'],
            2 => ['10' => 'Met', '5' => 'Coached', '0' => 'Not Met'],
            3 => ['15' => 'Met', '8' => 'Coached', '0' => 'Not Met'],
            4 => ['15' => 'Met', '8' => 'Coached', '0' => 'Not Met'],
        ];

        // Question descriptions (match the QA Monitoring form / ticket view)
        $verDesc = [
            1 => 'LDA completed Verification checks before sending recon report or email communication',
            2 => 'Was there any Zero Tolerance violation in relation to this exception?',
        ];
        $pcDesc = [
            1 => 'Used all available tools and information on hand to help resolve all areas accurately',
            2 => 'Took all necessary corrective and preventive actions to fully resolve the exception and mitigate the risk of recurrence',
            3 => 'Correct remedial action/s identified, carried out and agreed with the carrier, customer or internal team',
            4 => 'Quality of Transfer/Hand off - Was the transfer necessary and correctly processed?',
        ];
        $engDesc = [
            1 => 'Effective and positive communication with the audience',
            2 => 'Appropriate questioning to arrive at correct root cause and resolution',
            3 => 'Set clear expectations with the audience relevant to the topics discussed',
            4 => 'Showed sense of ownership and urgency relevant to the topics discussed',
        ];
    @endphp

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 font-size-18">Correct Evaluation Scores</h4>
                    <a href="{{ route('reports.disputes') }}" class="btn btn-sm btn-light">&larr; Back to disputes</a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-4 mb-3 p-3 rounded bg-light border">
                            <div>
                                <small class="text-muted text-uppercase d-block">Invoice</small>
                                <span class="fw-bold font-size-20 text-primary">{{ $audit->invoice_id }}</span>
                            </div>
                            <div class="border-start ps-4">
                                <small class="text-muted text-uppercase d-block">Reference</small>
                                <span class="fw-bold font-size-20">{{ $audit->audit_id }}</span>
                            </div>
                        </div>
                        <p class="text-muted">Adjust the ratings and comments below. Section totals and the overall score recalculate automatically, and a before/after record is kept.</p>

                        <form method="POST" action="{{ route('evaluations.correct.save', $audit->audit_id) }}">
                            @csrf
                            <input type="hidden" name="dispute_id" value="{{ $disputeId }}">

                            {{-- Verification --}}
                            <h6 class="mt-3 fw-semibold font-size-16">Verification</h6>
                            @foreach([1,2] as $i)
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="fw-semibold mb-2">{{ $i }}. {{ $verDesc[$i] }}</div>
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-3">
                                            <label class="form-label font-size-13">Rating</label>
                                            <select name="ver_outcome_{{ $i }}" class="form-control dropdown-choices">
                                                <option value="">Select Rating</option>
                                                @foreach($verOpts as $val => $label)
                                                    <option value="{{ $val }}" @selected((string) optional($ver)->{"ver_outcome_$i"} === (string) $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label font-size-13">Comment</label>
                                            <textarea name="ver_comment_{{ $i }}" class="form-control" rows="2">{{ optional($ver)->{"ver_comment_$i"} }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Process Compliance --}}
                            <h6 class="mt-4 fw-semibold font-size-16">Process Compliance</h6>
                            @foreach([1,2,3,4] as $i)
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="fw-semibold mb-2">{{ $i }}. {{ $pcDesc[$i] }}</div>
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-3">
                                            <label class="form-label font-size-13">Rating</label>
                                            <select name="pc_outcome_{{ $i }}" class="form-control dropdown-choices">
                                                <option value="">Select Rating</option>
                                                @foreach($pcOpts[$i] as $val => $label)
                                                    <option value="{{ $val }}" @selected((string) optional($pc)->{"pc_outcome_$i"} === (string) $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label font-size-13">Comment</label>
                                            <textarea name="pc_comment_{{ $i }}" class="form-control" rows="2">{{ optional($pc)->{"pc_comment_$i"} }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Engagement --}}
                            <h6 class="mt-4 fw-semibold font-size-16">Engagement</h6>
                            @foreach([1,2,3,4] as $i)
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="fw-semibold mb-2">{{ $i }}. {{ $engDesc[$i] }}</div>
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-3">
                                            <label class="form-label font-size-13">Rating</label>
                                            <select name="eng_outcome_{{ $i }}" class="form-control dropdown-choices">
                                                <option value="">Select Rating</option>
                                                @foreach($engOpts[$i] as $val => $label)
                                                    <option value="{{ $val }}" @selected((string) optional($eng)->{"eng_outcome_$i"} === (string) $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label font-size-13">Comment</label>
                                            <textarea name="eng_comment_{{ $i }}" class="form-control" rows="2">{{ optional($eng)->{"eng_comment_$i"} }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-4">
                                <label class="form-label">Reason for correction <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why these scores are being corrected..."></textarea>
                            </div>

                            <div class="alert alert-info mt-3 mb-3 font-size-13">
                                <i class="bx bx-info-circle"></i>
                                Submitting sends this correction for <strong>admin approval</strong>. The evaluation scores
                                won't change — and the dispute won't be resolved — until an administrator approves it.
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Submit these scores for admin approval?');">
                                    Submit for Approval
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Correction history --}}
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Correction History</h5></div>
                    <div class="card-body">
                        @if($history->isEmpty())
                            <p class="text-muted mb-0">No corrections recorded for this evaluation.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>When</th>
                                            <th>By</th>
                                            <th>Overall (old → new)</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($history as $h)
                                            <tr>
                                                <td class="font-size-12">{{ \Carbon\Carbon::parse($h->created_at)->format('Y-m-d H:i') }}</td>
                                                <td class="font-size-12">{{ $h->changed_by }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ data_get($h->old_values, 'overall', '—') }}%</span>
                                                    →
                                                    <span class="badge bg-primary">{{ data_get($h->new_values, 'overall', '—') }}%</span>
                                                </td>
                                                <td>
                                                    @php $sc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$h->status ?? 'approved'] ?? 'secondary'; @endphp
                                                    <span class="badge bg-{{ $sc }}">{{ ucfirst($h->status ?? 'approved') }}</span>
                                                </td>
                                                <td class="font-size-12" style="white-space:normal;">{{ $h->reason }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.script')
    <script>
        document.querySelectorAll('.dropdown-choices').forEach(function (el) {
            new Choices(el, { searchEnabled: false, itemSelectText: '', shouldSort: false });
        });
    </script>
</body>
</html>
