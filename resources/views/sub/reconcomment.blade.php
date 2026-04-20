@forelse($comment as $c)

<div class="border-bottom mt-3 pb-3">
    <div class="mb-2">
        <p class="float-sm-end text-muted font-size-13">{{ \Carbon\Carbon::parse($c->created_at)->format('F d, Y h:i A') }}</p>
        <h5 class="font-size-16 mb-0">{{ $c->employee_first_name }} {{ $c->employee_last_name }}</h5>
    </div>

    <p class="text-muted mb-4">{{ $c->comments }}</p>
</div>
@empty
<p>No comments found.</p>
@endforelse