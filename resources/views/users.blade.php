<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                            <h5 class="card-title">Users List <span class="text-muted fw-normal ms-2"></span></h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3  text-end">
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
                        <input type="text" class="form-control" placeholder="Email Address" id="email">
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
                            <option value="Audit Supervisor">Audit Supervisor</option>
                            <option value="Vendor Manager">Vendor Manager</option>
                            <option value="Duplicate">Duplicate</option>
                            <option value="LDA">LDA</option>
                            <option value="Duplicate Manager">Duplicate Manager</option>
                            <option value="GSS Supervisor">GSS Supervisor</option>
                            <option value="Audit Manager">Audit Manager</option>
                            <option value="VP, Audit">VP, Audit</option>
                            <option value="Rate Loading Supervisor">Rate Loading Supervisor</option>
                            <option value="Post Audit Supervisor">Post Audit Supervisor</option>
                            <option value="Audit Sr. Manager">Audit Sr. Manager</option>
                            <option value="SME">SME</option>
                            <option value="GSS">GSS</option>
                            <option value="Post Audit">Post Audit</option>
                            <option value="GSS Manager">GSS Manager</option>
                            <option value="AI Prompting Engineer">AI Prompting Engineer</option>
                            <option value="Rate Loading Analyst">Rate Loading Analyst</option>
                            <option value="Ops Analytics Manager">Ops Analytics Manager</option>
                            <option value="Service">Service</option>
                            <option value="Chief Operating Officer">Chief Operating Officer</option>
                            <option value="Chief Executive Officer">Chief Executive Officer</option>
                            <option value="Chief Financial Officer">Chief Financial Officer</option>
                            <option value="Chief Technology Officer">Chief Technology Officer</option>
                            <option value="Chief Product Officer">Chief Product Officer</option>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-assigned-to">Update</button>
                </div>
            </div>
        </div>
    </div>
    <!-- gridjs js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script src="{{ asset('assets/js/user.js') }}"></script>

</body>

</html>