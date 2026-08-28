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
                            <h5 class="card-title">Triad Details</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 text-end">
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#change-triad-employee">
                                <i class="bx bx-grid-small font-size-16 align-middle me-2"></i> Options
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <small class="text-muted">Triad ID</small>
                                        <div class="fw-semibold">
                                            {{ $data->reference_id ?? '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Triad Reference</small>
                                        <div class="fw-semibold">
                                            {{ $data->reference ?? '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Created By</small>
                                        <div class="fw-semibold">
                                            {{ $created_by->FirstName ?? '' }} {{ $created_by->LastName ?? '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Created Date</small>
                                        <div class="fw-semibold">
                                            {{ $data->created_at ?? '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Triad Employee</small>
                                        <div class="fw-semibold">
                                            {{ trim(($triadEmployee->FirstName ?? '') . ' ' . ($triadEmployee->LastName ?? '')) ?: 'N/A' }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        @php
                            $triad = is_string($data->triad) ? json_decode($data->triad, true) : $data->triad;
                        @endphp
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Body Language - set the mood</h4>
                                        <p class="card-text"> {{ $triad['body_language']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            <small class="
                                                {{ ($triad['body_language']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['body_language']['score'] ?? '' }}
                                            </small>
                                        </p>

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Clear the mind (setting of expectation)</h4>
                                        <p class="card-text"> {{ $triad['clear_mind']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['clear_mind']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['clear_mind']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Permission to take notes</h4>
                                        <p class="card-text"> {{ $triad['permission_notes']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['permission_notes']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['permission_notes']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Were the word choices and questions delivered appropriately and positively</h4>
                                        <p class="card-text"> {{ $triad['choices_question']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                           
                                            <small class="
                                                {{ ($triad['choices_question']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['choices_question']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Was the SME able to establish trust, buy-in, and, commitment?</h4>
                                        <p class="card-text"> {{ $triad['was_sme']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['was_sme']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['was_sme']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Recap/Summary provided?</h4>
                                        <p class="card-text"> {{ $triad['recap_summary']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['recap_summary']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['recap_summary']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">Did the SME adhere to the 80/20 rule?</h4>
                                        <p class="card-text"> {{ $triad['sme_adhere']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['sme_adhere']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['sme_adhere']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">DOCUMENTATION - Is SMART goal clearly defined?</h4>
                                        <p class="card-text"> {{ $triad['clearly_defined']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['clearly_defined']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['clearly_defined']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">DOCUMENTATION - Did the documentation include the RCA of the current situation?</h4>
                                        <p class="card-text"> {{ $triad['rca']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['rca']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['rca']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title">DOCUMENTATION - Are actions agreed to be identified in line with the situation?</h4>
                                        <p class="card-text"> {{ $triad['line_situation']['input'] ?? '' }}</p>
                                        <p class="card-text">
                                            
                                            <small class="
                                                {{ ($triad['line_situation']['score'] ?? '') == 'Pass' ? 'text-success' : 'text-danger' }}">
                                                {{ $triad['line_situation']['score'] ?? '' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
    <div class="modal fade" id="change-triad-employee" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="triad-employee" class="form-label">Triad Employee <span class="text-danger">*</span></label>
                    <select class="form-control" data-choice name="triad-employee" id="triad-employee" placeholder="This is a search placeholder">
                        <option value="">Select Employee</option>
                        @foreach ($usersData['allusers'] as $alluser)
                        <option value="{{ $alluser->employeeid }}">
                            {{ $alluser->first_name }} {{ $alluser->last_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-triad-employee">Update</button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('[data-choice]');

            elements.forEach(el => {
                new Choices(el);
            });
        });

        $(document).ready(function() {
            $(document).on('click', '#update-triad-employee', async function() {
                try {
                    const pathParts = window.location.pathname.split('/');
                    const id = pathParts[pathParts.length - 1];

                    const employeeId = document.getElementById('triad-employee').value;

                    const res = await fetch(`/triad/assign-employee/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            employee_id: employeeId
                        })
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP error! Status: ${res.status}`);
                    }

                    const data = await res.json();
                    if (data.status === 200) {
                        window.location.reload();
                    }

                } catch (err) {
                    console.error("Request failed:", err);
                }
            });
        });
    </script>

</body>

</html>