<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.header')
<style>
    /* Grid.js's own search box (rendered dynamically, class
       gridjs-search-input — there's no template tag for it to hook here
       directly). text-transform only changes how it's displayed, not the
       stored value, but that's enough: Grid.js's built-in search already
       lowercases both the typed keyword and each cell's text before
       comparing them, so matching is already case-insensitive regardless —
       this just keeps what's shown consistent with that. */
    #table-gridjs .gridjs-search-input {
        text-transform: lowercase;
    }
</style>


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
                            <h5 class="card-title">Users List <span class="text-muted fw-normal ms-2"></span></h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 text-end">
                            <a href="{{ route('export.users') }}" class="btn btn-success waves-effect waves-light me-2">
                                <i class="bx bx-download font-size-16 align-middle me-2"></i> Export to Excel
                            </a>
                            <button type="button" class="btn btn-info waves-effect waves-light me-2" data-bs-toggle="modal" data-bs-target="#bulk-edit-modal">
                                <i class="bx bx-spreadsheet font-size-16 align-middle me-2"></i> Bulk Edit (Excel)
                            </button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#add-user">
                                <i class="bx bx-user-plus font-size-16 align-middle me-2"></i> Add new user
                            </button>

                        </div>
                    </div>
                </div>

                
                <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Users</h4>
                                    </div><!-- end card header -->
                                    <div class="card-body">
                                        <div id="table-gridjs"></div>
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <div class="modal fade" id="add-user" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                    <div class="mb-3 col-lg-12">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="text" class="form-control" placeholder="Email Address" id="email" style="text-transform: lowercase;">
                        <span id="email-feedback"></span>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="first-name" class="form-label">First Name</label>
                        <input type="text" class="form-control" placeholder="First Name" id="first-name">
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="last-name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" placeholder="Last Name" id="last-name">
                    </div>
                    
                    <div class="mb-3 col-lg-6">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-control dropdown-choices" data-trigger id="department" placeholder="This is a search placeholder">
                            <option value="">Select Department</option>
                            <option value="Audit Ops">Audit Ops</option>
                        </select>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="position" class="form-label">Position</label>
                        <select class="form-control dropdown-choices" data-trigger id="position" placeholder="This is a search placeholder">
                            <option value="">Select Position</option>
                            {{-- $positions comes from UserListMonitoringPage::getUsersData() (Position::orderBy('sort_order')->pluck('name')),
                                 managed at /positions instead of hardcoded here. Previously this list and
                                 edituser.blade.php's had drifted out of sync (5 Chief * titles existed only here) —
                                 both now read from the same table. --}}
                            @foreach ($positions as $position)
                                <option value="{{ $position }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control dropdown-choices" data-trigger id="role" placeholder="This is a search placeholder">
                            <option value="">Select Role</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="supervisor" class="form-label">Supervisor</label>
                        <select class="form-control dropdown-choices" data-trigger id="supervisor" placeholder="This is a search placeholder">
                            <option value="">Select Supervisor</option>
                            @foreach ($supervisors as $supervisor)
                            <option value="{{ $supervisor->employeeid }}">
                                {{ $supervisor->first_name }} {{ $supervisor->last_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="second-supervisor" class="form-label">2nd Supervisor</label>
                        <select class="form-control dropdown-choices" data-trigger id="second-supervisor" placeholder="This is a search placeholder">
                            <option value="">Select 2nd Supervisor</option>
                            @foreach ($supervisors as $supervisor)
                            <option value="{{ $supervisor->employeeid }}">
                                {{ $supervisor->first_name }} {{ $supervisor->last_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <label for="login-method" class="form-label">Login Method</label>
                        <select class="form-control dropdown-choices" data-trigger id="login-method" placeholder="This is a search placeholder">
                            <option value="password">Password only</option>
                            <option value="microsoft" selected>Microsoft only</option>
                            <option value="both">Both</option>
                        </select>
                        <small class="text-muted font-size-12">Defaults to Microsoft-only for new accounts.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-assigned-to">Update</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="bulk-edit-modal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="bulkEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkEditModalLabel">Bulk Edit Users (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ol class="font-size-13 text-muted ps-3">
                        <li>Download the template below — it includes every current user.</li>
                        <li>Edit it in Excel: change a field to update that user, or add a new row with Employee ID left blank to create a new user.</li>
                        <li>Upload the edited file. Nothing is saved yet — you'll see exactly what will be created/updated/skipped first, and confirm before anything changes.</li>
                    </ol>
                    <p class="text-muted font-size-12">
                        Supervisor / 2nd Supervisor are set by Employee ID and must already exist — the "(reference only)" name columns are just for your own reference and aren't re-imported. On an existing user, leaving a cell blank keeps that field unchanged rather than clearing it. Passwords and admin access can't be changed this way.
                    </p>

                    <a href="{{ route('users.bulk.template') }}" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bx bx-download align-middle me-1"></i> Download template
                    </a>

                    <div class="mb-3">
                        <input type="file" class="form-control" id="bulk-edit-file" accept=".xlsx,.xls,.csv">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="bulk-edit-preview-btn">Upload &amp; Preview</button>

                    <div id="bulk-edit-results" class="mt-3" style="display:none;">
                        <div id="bulk-edit-summary" class="mb-2 fw-semibold"></div>
                        <div class="table-responsive" style="max-height: 320px;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr><th>Row</th><th>Employee ID</th><th>Name</th><th>Action</th><th>Details</th></tr>
                                </thead>
                                <tbody id="bulk-edit-rows"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="bulk-edit-confirm-btn" disabled>Confirm Import</button>
                </div>
            </div>
        </div>
    </div>

    <!-- gridjs js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script>window.currentEmployeeId = @json(auth()->user()->employeeid);</script>
    <script src="{{ asset('assets/js/user.js') }}"></script>
    <script src="{{ asset('assets/js/user-bulk-import.js') }}"></script>

</body>

</html>