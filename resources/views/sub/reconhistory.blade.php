@forelse($history as $h)

<div class="border-bottom mt-3 pb-3">
    <div class="mb-2">
        <p class="float-sm-end text-muted font-size-13">{{ \Carbon\Carbon::parse($h->created_at)->format('F d, Y h:i A') }}</p>
        <h5 class="font-size-16 mb-0">
            {{ $h->actor_name ?? 'System' }}
            <span class="badge bg-info-subtle text-info font-size-11 align-middle ms-1">{{ ucfirst(str_replace('_', ' ', $h->event)) }}</span>
        </h5>
    </div>

    <p class="text-muted mb-4">{{ $h->description }}</p>
</div>
@empty
<p>No history found.</p>
@endforelse
