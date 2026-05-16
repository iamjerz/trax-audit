<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
@include('partials.header')

<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Triad Ticket</h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table" id="table-recon"></div>
                            </div>
                        </div>

                    </div>

                </div>


            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script>
        const limit = 10;

        new gridjs.Grid({
            columns: [
                {
                    name: 'Triad Reference',
                    formatter: (cell) => {
                        const safe = String(cell).replace(/"/g, '&quot;');
                        return gridjs.html(`
                <a href="/triad-ticket-view/${safe}" class="text-primary fw-bold">
                    ${safe}
                </a>
                `);
                    }
                },
                'Coaching Reference',
                {
                    name: 'Triad Date',
                    formatter: (cell) => {
                        if (!cell) return '';

                        const date = new Date(cell);
                        if (isNaN(date)) return cell;

                        return date.toLocaleDateString('en-PH', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                    }
                },
                'Created By',
            ],

            server: {
                url: '/triad-data',

                then: data => data.data.map(item => [
                    item.reference_id,
                    item.reference,
                    item.created_at,
                    item.full_name || ''
                    
                ]),

                total: data => data.total
            },

            pagination: {
                enabled: true,
                limit: 10,
                server: {
                    url: (prev, page, limit) => {
                        const url = new URL(prev, window.location.origin);
                        const params = url.searchParams;

                        params.set('limit', limit);
                        params.set('offset', page * limit);

                        return `/triad-data?${params.toString()}`;
                    }
                }
            },

            search: {
                debounceTimeout: 500,
                server: {
                    url: (prev, keyword) => {
                        return `/triad-data?limit=10&offset=0&search=${keyword}`;
                    }
                }
            },

            sort: false
        }).render(document.getElementById('table-recon'));
    </script>


</body>

</html>