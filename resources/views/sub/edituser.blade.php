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
                            <h5 class="card-title">User Information<span class="text-muted fw-normal ms-2"></span></h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxl-3">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="user-profile-img">
                                    <img src="{{ asset('assets/images/pattern-bg.jpg') }}" class="profile-img profile-foreground-img rounded-top" style="height: 120px;" alt="">
                                    <div class="overlay-content rounded-top">
                                        <div>

                                        </div>
                                    </div>
                                </div>
                                <!-- end user-profile-img -->


                                <div class="p-4 pt-0">

                                    <div class="mt-n5 position-relative text-center border-bottom pb-3">
                                        <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" class="avatar-xl rounded-circle img-thumbnail">

                                        <div class="mt-3">
                                            <h5 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h5>
                                            <p class="text-muted mb-0">
                                                {{ $user->email }}
                                            </p>
                                        </div>

                                    </div>

                                    <div class="table-responsive mt-3 border-bottom pb-3">
                                        <table class="table align-middle table-sm table-nowrap table-borderless table-centered mb-0">
                                            <tbody>
                                                <tr>
                                                    <th class="fw-bold">
                                                        Employee ID :</th>
                                                    <td class="text-muted">{{ $user->employeeid }}</td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">
                                                        Position :</th>
                                                    <td class="text-muted">{{ $user->position }}</td>
                                                </tr>
                                                
                                                <!-- end tr -->
                                                

                                                <tr>
                                                    <th class="fw-bold">Sup ID :</th>
                                                    <td class="text-muted">{{ $user->supervisor_id }}</td>
                                                </tr>
                                                <!-- end tr -->

                                                <tr>
                                                    <th class="fw-bold">Sup Email :</th>
                                                    <td class="text-muted">{{ $user->supervisor_email  }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="fw-bold">Sup Name :</th>
                                                    <td class="text-muted">{{ $user->supervisor_first_name }} {{ $user->supervisor_last_name }}</td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">Status :</th>
                                                    <td class="text-muted">
                                                        <span class="badge 
                                                            {{ $user->status === 'active' ? 'bg-success-subtle text-success' : 
                                                            ($user->status === 'inactive' ? 'bg-warning-subtle text-warning' : 
                                                            'bg-danger-subtle text-danger') }} mb-0">
                                                            {{ ucfirst($user->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">
                                                        Role :</th>
                                                    <td class="text-muted">{{ $user->role }}</td>
                                                </tr>
                                                <!-- end tr -->
                                            </tbody><!-- end tbody -->
                                        </table>
                                    </div>
                                    <div class="p-3 mt-3">
                                        @php
                                                $selectedAccess = old('access', $access ?? []);
                                                $options = [
                                                    'admin',
                                                    'web_user_manager',
                                                    'web_user_sup',
                                                    'web_user_sme',
                                                    'web_user_lda',
                                                    'web_managers',
                                                    'web_score_approval',
                                                    'web_reports',
                                                    'web_report_monitoring',
                                                    'web_report_action_register',
                                                    'web_report_triad',
                                                    'web_report_coaching',
                                                    'web_forms',
                                                    'web_dashboard',
                                                    'extension_action_register',
                                                    'extension_monitoring',
                                                    'extension_coaching',
                                                    'extension_triad'
                                                ];
                                        @endphp
                                        <div class="">
                                            <div class="fw-bold mb-2">
                                                Access
                                            </div>
                                            
                                            @foreach ($options as $opt)
                                                @if(in_array($opt, $selectedAccess))
                                                    <span class="badge bg-primary-subtle text-primary mb-1">
                                                        {{ $opt }}
                                                    </span>
                                                @endif
                                            @endforeach
                                            
                                        </div>
                                    </div>


                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Employee ID</label>
                                            <input type="text" class="form-control" placeholder="Employee ID" value="{{ $user->employeeid }}" disabled id="employeeid">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Email</label>
                                            <input type="text" class="form-control" placeholder="Email" value="{{ $user->email }}" id="email">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Firt Name</label>
                                            <input type="text" class="form-control" placeholder="First Name" value="{{ $user->first_name }}" id="first-name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" placeholder="Last Name" value="{{ $user->last_name }}" id="last-name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Supervisor</label>
                                            <select class="form-control dropdown-choices" data-trigger id="supervisor" placeholder="This is a search placeholder">
                                                <option value="">Select Supervisor</option>
                                                @foreach ($supervisors as $supervisor)
                                                    <option value="{{ $supervisor->employeeid }}"
                                                        {{ ($supervisor->employeeid == ($user->supervisor_id ?? '')) ? 'selected' : '' }}>
                                                        
                                                        {{ $supervisor->first_name }} {{ $supervisor->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Status</label>
                                            <select class="form-control dropdown-choices" data-trigger id="status" placeholder="This is a search placeholder">
                                                <option value="">Select Status</option>
                                                <option value="active" {{ ($user->status ?? '') == 'active' ? 'selected' : '' }}>
                                                    active
                                                </option>

                                                <option value="inactive" {{ ($user->status ?? '') == 'inactive' ? 'selected' : '' }}>
                                                    inactive
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Role</label>
                                            <select class="form-control dropdown-choices" data-trigger id="role" placeholder="This is a search placeholder">
                                                <option value="">Select Role</option>
                                                <option value="user" {{ ($user->role ?? '') == 'user' ? 'selected' : '' }}>
                                                    user
                                                </option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Position</label>
                                            @php
                                            $positions = [
                                                'user',
                                                'Audit Supervisor',
                                                'Vendor Manager',
                                                'Duplicate',
                                                'LDA',
                                                'Duplicate Manager',
                                                'GSS Supervisor',
                                                'Audit Manager',
                                                'VP, Audit',
                                                'Rate Loading Supervisor',
                                                'Post Audit Supervisor',
                                                'Audit Sr. Manager',
                                                'SME',
                                                'GSS',
                                                'Post Audit',
                                                'GSS Manager',
                                                'AI Prompting Engineer',
                                                'Rate Loading Analyst',
                                                'Ops Analytics Manager',
                                                'Service',
                                            ];
                                            @endphp

                                            <select class="form-control dropdown-choices" data-trigger id="position" name="position">
                                                <option value="">Select Position</option>

                                                @foreach($positions as $position)
                                                    <option value="{{ $position }}"
                                                        {{ ($user->position ?? '') == $position ? 'selected' : '' }}>
                                                        {{ $position }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <button type="submit" id="edit-user" class="btn btn-primary w-md">Update</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label">Password</label>
                                            <div>
                                                <button type="button" id="reset-password-btn" class="btn btn-danger w-md">Reset</button>
                                                <small class="d-block text-muted mt-1">Resets the password to the default.</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="formrow-firstname-input" class="form-label">Access</label>
                                        

                                        <select name="access[]" class="form-control dropdown-choices" id="access" multiple>
                                            @foreach ($options as $opt)
                                                <option value="{{ $opt }}" {{ in_array($opt, $selectedAccess) ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="button" id="update-access-btn" class="btn btn-primary">
                                    Update Access
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- gridjs js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script src="{{ asset('assets/js/user-edit.js') }}"></script>
</body>

</html>