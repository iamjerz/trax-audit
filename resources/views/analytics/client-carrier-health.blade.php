<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.header')
<body>
    <div id="layout-wrapper">@include('partials.bodyheader')</div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="page-title-box"><h4 class="mb-0 font-size-18">Client / Carrier Health</h4></div>

                @php
                    $renderTable = function ($title, $label, $data) {
                        return compact('title', 'label', 'data');
                    };
                @endphp

                <div class="row">
                    @foreach([['Clients', 'Client', $clients], ['Carriers', 'Carrier', $carriers]] as [$title, $label, $data])
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ $label }}</th>
                                                    <th>Total</th>
                                                    <th>Open</th>
                                                    <th>Overdue</th>
                                                    <th>Closed</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($data as $row)
                                                    <tr>
                                                        <td><strong>{{ $row['key'] }}</strong></td>
                                                        <td>{{ $row['total'] }}</td>
                                                        <td>{{ $row['open'] }}</td>
                                                        <td>
                                                            @if($row['overdue'] > 0)
                                                                <span class="badge bg-danger">{{ $row['overdue'] }}</span>
                                                            @else
                                                                0
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['closed'] }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-center text-muted py-3">No data.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    @include('partials.script')
</body>
</html>
