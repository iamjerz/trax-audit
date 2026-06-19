<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<style>
    .tl { list-style: none; padding-left: 0; margin: 0; }
    .tl-item { position: relative; padding: 0 0 1.25rem 1.75rem; border-left: 2px solid #e5e7eb; }
    .tl-item:last-child { border-left-color: transparent; }
    .tl-dot { position: absolute; left: -7px; top: 2px; width: 12px; height: 12px; border-radius: 50%; }
    .tl-diff { font-size: 12px; white-space: pre-wrap; margin: .25rem 0 0; }
    .tl-diff .old { color: #d63939; }
    .tl-diff .new { color: #2fb344; }
</style>
<body>
    <div id="layout-wrapper">@include('partials.bodyheader')</div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 font-size-18">Activity Timeline</h4>
                    <a href="/ticket/view/{{ $audit->audit_id }}" class="btn btn-sm btn-light">View evaluation</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Invoice: <strong>{{ $audit->invoice_id }}</strong> &middot; LDA: {{ $audit->lda_name }}</p>
                        <p class="text-muted">Reference: {{ $audit->audit_id }}</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        @php
                            $colors = [
                                'created' => '#1f58c7', 'updated' => '#52c6ea', 'acknowledged' => '#34c38f',
                                'dispute_raised' => '#f1b44c', 'dispute_resolved' => '#34c38f',
                                'score_corrected' => '#f1734f',
                            ];
                        @endphp
                        @if($events->isEmpty())
                            <p class="text-muted mb-0">No recorded activity for this evaluation yet.</p>
                        @else
                            <ul class="tl">
                                @foreach($events as $e)
                                    <li class="tl-item">
                                        <span class="tl-dot" style="background: {{ $colors[$e->event] ?? '#adb5bd' }}"></span>
                                        <div class="d-flex justify-content-between">
                                            <strong class="text-capitalize">{{ str_replace('_', ' ', $e->event) }}</strong>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($e->created_at)->format('Y-m-d H:i') }}</small>
                                        </div>
                                        <div class="text-muted font-size-13">
                                            {{ $e->description }}
                                            @if($e->actor_name) — <span class="fw-semibold">{{ $e->actor_name }}</span> @endif
                                        </div>
                                        @php
                                            $old = json_decode($e->old_values ?? 'null', true);
                                            $new = json_decode($e->new_values ?? 'null', true);
                                            $keys = array_unique(array_merge(array_keys($old ?? []), array_keys($new ?? [])));
                                        @endphp
                                        @if(!empty($keys))
                                            <pre class="tl-diff">@foreach($keys as $k)<strong>{{ $k }}:</strong> @php $o = data_get($old,$k); $n = data_get($new,$k); @endphp@if(!is_null($o))<span class="old">{{ is_array($o) ? json_encode($o) : $o }}</span> → @endif<span class="new">{{ is_array($n) ? json_encode($n) : ($n ?? '') }}</span>
@endforeach</pre>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.script')
</body>
</html>
