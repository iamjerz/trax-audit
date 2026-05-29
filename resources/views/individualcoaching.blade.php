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
                            <h5 class="card-title">Coaching Details</h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <small class="text-muted">Coaching ID</small>
                                        <div class="fw-semibold">
                                            {{ $data->reference_id ?? '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Coaching Reference</small>
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
                                        <small class="text-muted">Coaching Type</small>
                                        <div class="fw-semibold">
                                            {{ $data->reference_type ?? '' }}
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        @php
                            $smart = is_string($data->smart) ? json_decode($data->smart, true) : $data->smart;
                        @endphp
                        
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    SMART
                                </div>
                                <div class="card-body">
                                    <div class="row  gy-4">
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">S - Specific</h6>
                                                    <p class="card-text">The goal or issue must be clearly defined; define what needs to be changed.</p>
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1"><i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> LDAs must be able to understand the specifics, not broad/vague, of the current state of the problem or issue.  </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1"><i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> LDAs must be able to define the goal/what they want to achieve </p>
                                                        </li>
                                                    </ul>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $smart['specific'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">M - Measurable</h6>
                                                    <p class="card-text">Progress should be trackable and observable.</p>
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                Define clear and quantifiable metrics (number, percent, time, etc.) or indicators  
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                Break down the goal into milestones or checkpoints 
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                Use tools or documentation to record progress
                                                            </p>
                                                        </li>
                                                        
                                                    </ul>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $smart['measurable'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">A - Achievable</h6>
                                                    <p class="card-text">The goal must be realistic given the employee’s skills, workload, resources.</p>
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                Goals that LDAs can achieve given their current, knowledge and skills, experience, workload, and available resources (time, tools, SME/Supervisor/Colleague support, etc.) 
                                                            </p>
                                                        </li>
                                                        
                                                    </ul>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $smart['achievable'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">R - Relevant</h6>
                                                    <p class="card-text">This goal should connect directly to the team or business priorities:</p>
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                The goal should clearly contribute to the larger objectives of your team and the business, which is clearly outline in the Team/s Initiatives and Aligned OKRs
                                                            </p>
                                                        </li>
                                                        
                                                    </ul>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $smart['relevant'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">T - Time- bound</h6>
                                                    <p class="card-text">This goal should connect directly to the team or business priorities:</p>
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                The goal should include a specific date or period (Example, issue tracker indicating the Owners and Timeline of completion) 
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i> 
                                                                Creates urgency and motivation 
                                                            </p>
                                                        </li>
                                                        
                                                    </ul>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $smart['time_bound'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>



                                </div>
                            </div>
                        </div>
                        @php
                            $grow = is_string($data->grow) ? json_decode($data->grow, true) : $data->grow;
                        @endphp
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    GROW
                                </div>
                                <div class="card-body">
                                    <div class="row  gy-4">
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">G - Grow</h6>
                                                    <p class="card-text">SMART GOAL</p>
                                                    <p class="card-text">
                                                        <small class="text-muted">Define what the employee wants to achieve (paired with SMART)</small>
                                                    </p>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['grow']['input'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Input
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['grow']['comments'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">R - Reality</h6>
                                                    <p class="card-text">RCA - Explore the current situation</p>
                                                    <p class="card-text">
                                                        <small class="text-muted">Challenges</small>
                                                    </p>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['reality']['input'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Input
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['reality']['comments'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">O - Options</h6>
                                                    <p class="card-text">SMART ACTION - Brainstorm possible solutions or strategies.</p>
                                                    <p class="card-text">
                                                        <small class="text-muted">Actions & Pros and Cons</small>
                                                    </p>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['option']['input'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Input
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['option']['comments'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 d-flex">
                                            <div class="card mb-4 h-100 w-100">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title">W - Way Forward</h6>
                                                    <p class="card-text">SMART ACTION - Identify the exact next steps and commitment.</p>
                                                    <p class="card-text">
                                                        <small class="text-muted">Blockers and Actions</small>
                                                    </p>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['wayforward']['input'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Input
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                    <hr>
                                                    <div class="mt-4 mt-lg-0">
                                                        <blockquote class="blockquote font-size-16 mb-0">
                                                            <p>{{ $grow['wayforward']['comments'] ?? '' }}</p>
                                                            <footer class="blockquote-footer">Coach Comment
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
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