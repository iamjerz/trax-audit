<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('assets/libs/gridjs/theme/mermaid.min.css') }}">
@include('partials.header')

<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h5 class="card-title mb-1">Client &amp; Carrier Codes</h5>
                            <p class="text-muted mb-0 font-size-13">
                                The canonical lookup lists behind the Client Code / Carrier Code dropdowns used
                                across Recon tickets and audit forms. Renaming or removing a code here only changes
                                what's offered going forward — it doesn't touch any tickets or audits already saved
                                with that code.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Client Codes</h4>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm waves-effect" id="open-bulk-client-code-btn">
                                        <i class="bx bx-upload font-size-16 align-middle me-1"></i> Bulk Add
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm waves-effect waves-light" id="open-add-client-code-btn">
                                        <i class="bx bx-plus font-size-16 align-middle me-1"></i> Add Client Code
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table" id="table-client-codes"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Carrier Codes</h4>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm waves-effect" id="open-bulk-carrier-code-btn">
                                        <i class="bx bx-upload font-size-16 align-middle me-1"></i> Bulk Add
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm waves-effect waves-light" id="open-add-carrier-code-btn">
                                        <i class="bx bx-plus font-size-16 align-middle me-1"></i> Add Carrier Code
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table" id="table-carrier-codes"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add / Edit Client Code Modal -->
    <div class="modal fade" id="addClientCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="client-code-modal-title">Add Client Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="new-client-code-name" placeholder="e.g. 000-0293">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="add-client-code-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add / Edit Carrier Code Modal -->
    <div class="modal fade" id="addCarrierCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="carrier-code-modal-title">Add Carrier Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="new-carrier-code-name" placeholder="e.g. 11DX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <select class="form-select" id="new-carrier-code-client-name">
                            <option value="">-- Select Client Code --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="add-carrier-code-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Add Client Codes Modal -->
    <div class="modal fade" id="bulkAddClientCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Add Client Codes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">One client code per line</label>
                    <textarea class="form-control" id="bulk-client-codes-text" rows="10" placeholder="000-0293&#10;000-0512&#10;000-0748"></textarea>
                    <p class="text-muted font-size-12 mt-2 mb-0">
                        Paste a column straight from Excel/CSV. Codes that already exist are skipped automatically.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="bulk-add-client-codes-btn">Add All</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Add Carrier Codes Modal -->
    <div class="modal fade" id="bulkAddCarrierCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Add Carrier Codes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">One per line: <code>carrier_code</code> or <code>carrier_code,client_name</code></label>
                    <textarea class="form-control" id="bulk-carrier-codes-text" rows="10" placeholder="11DX,Acme Corp&#10;22XY&#10;33ZZ,Globex Inc"></textarea>
                    <p class="text-muted font-size-12 mt-2 mb-0">
                        Paste straight from Excel/CSV (two columns become "code,client name"). Codes that already
                        exist are skipped automatically.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="bulk-add-carrier-codes-btn">Add All</button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.script')
    <script src="{{ asset('assets/libs/gridjs/gridjs.umd.js') }}"></script>
    <script src="{{ asset('assets/js/client-carrier-codes.js') }}"></script>
</body>

</html>
