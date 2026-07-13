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
                            <h5 class="card-title">QA Monitoring Ticket</h5>
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
        const canDelete = @json($canDelete ?? false);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const columns = [
            {
                name: 'Audit ID',
                formatter: (cell) => {
                    const safe = String(cell).replace(/"/g, '&quot;');
                    return gridjs.html(`
                <a href="/ticket/view/${safe}" class="text-primary fw-bold">
                    ${safe}
                </a>
                `);
                }
            },
            'Invoice ID',
            'LDA',
            'Carrier',
            {
                name: 'Audit Date',
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
        ];

        if (canDelete) {
            columns.push({
                name: 'Actions',
                sort: false,
                formatter: (cell) => {
                    const safe = String(cell).replace(/"/g, '&quot;');
                    return gridjs.html(`
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="deleteAudit('${safe}')">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    `);
                }
            });
        }

        const grid = new gridjs.Grid({
            columns: columns,

            server: {
                url: '/monitoring-data',

                then: data => data.data.map(item => {
                    const row = [
                        item.audit_id,
                        item.invoice_id || '',
                        item.lda_name || '',
                        item.carrier_name || '',
                        item.audit_date_1,
                        item.created_by_name || ''
                    ];
                    if (canDelete) row.push(item.audit_id);
                    return row;
                }),

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

                        return `/monitoring-data?${params.toString()}`;
                    }
                }
            },

            search: {
                debounceTimeout: 500,
                server: {
                    url: (prev, keyword) => {
                        return `/monitoring-data?limit=10&offset=0&search=${keyword}`;
                    }
                }
            },

            sort: false
        });

        grid.render(document.getElementById('table-recon'));

        function deleteAudit(id) {
            if (!confirm('Delete audit ' + id + '? This will also remove its scores and cannot be undone.')) {
                return;
            }

            fetch('/monitoring-ticket/' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json().then(body => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok || !body.success) {
                    alert(body.message || 'Failed to delete the record.');
                    return;
                }
                grid.forceRender();
            })
            .catch(() => alert('Failed to delete the record.'));
        }
    </script>


</body>

</html>
