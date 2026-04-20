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
                            <h5 class="card-title">Recon Ticket</h5>
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
                name: 'Submission ID',
                formatter: (cell) => {
                    return gridjs.html(`
                        <a href="/recon-ticket-view/${cell}" class="text-primary fw-bold">
                            ${cell}
                        </a>
                    `);
                }
            },
            'Recon Date',
            'Client Code',
            'Carrier Code',
            'Region',
            'Status',
            'Created At'
        ],

        server: {
            url: '/recon-data',

            then: data => data.data.map(item => [
            item.submission_id,
            item.recon_call_date,
            item.client_code,
            item.carrier_code,
            item.region,
            item.status,
            item.created_at
            ]),

            total: data => data.total
        },

        pagination: {
            enabled: true,
            limit: limit,
            server: {
            url: (prev, page, limit) => {
                const url = new URL(prev, window.location.origin);
                const search = url.searchParams.get('search') || '';

                const offset = page * limit;

                return `/recon-data?limit=${limit}&offset=${offset}&search=${search}`;
            }
            }
        },

        search: {
            debounceTimeout: 500,
            server: {
            url: (prev, keyword) => {
                return `http://127.0.0.1:8000/recon-data?limit=${limit}&offset=0&search=${keyword}`;
            }
            }
        },

        sort: false
        }).render(document.getElementById('table-recon'));
    </script>


</body>

</html>