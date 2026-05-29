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
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>

</html>