<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
                <div class="row">
                   <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="fw-semibold font-size-18">
                                    <h3 class="card-title"><h3 class="fw-semibold mb-0">Invoice# - {{ $data->invoice_id }}</h3></h3>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-5">
                                        <table class="table align-middle table-sm table-nowrap table-borderless table-centered mb-0">
                                            <tbody>
                                                <tr>
                                                    <th class="fw-bold">Reference Number :</th>
                                                    <td class="text-muted">{{ $data->audit_id }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="fw-bold">
                                                        LDA Name:</th>
                                                    <td class="text-muted">{{ $data->lda_name }}</td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">
                                                        Employee ID:</th>
                                                    <td class="text-muted">{{ $data->lda_id }}</td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">
                                                        Audit Supervisor Name :</th>
                                                    <td class="text-muted">{{ $data->lda_sup_name }}</td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">Auditor Name :</th>
                                                    <td class="text-muted">{{ $data->lda_auditors_name }}</td>
                                                </tr>
                                                <!-- end tr -->

                                                <tr>
                                                    <th class="fw-bold">Audit Date :</th>
                                                    <td class="text-muted">{{ $data->audit_date_1 }}</td>
                                                </tr>
                                                <!-- end tr -->

                                                <tr>
                                                    <th class="fw-bold">Email :</th>
                                                    <td class="text-muted">{{ $data->email }}</td>
                                                </tr>
                                                
                                                <!-- end tr -->
                                            </tbody><!-- end tbody -->
                                        </table>
                                    </div>
                                    <div class="col-lg-5">
                                        <table class="table align-middle table-sm table-nowrap table-borderless table-centered mb-0">
                                            <tbody>
                                                <tr>
                                                    <th class="fw-bold">Audit Month:</th>
                                                    <td class="text-muted">{{ \Carbon\Carbon::parse($data->audit_date_1)->format('F') }} </td>
                                                </tr>
                                                <!-- end tr -->
                                                
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">Carrier Name :</th>
                                                    <td class="text-muted">{{ $data->carrier_name }}</td>
                                                </tr>
                                                <!-- end tr -->
                                                <tr>
                                                    <th class="fw-bold">Exception Status :</th>
                                                    <td class="text-muted">{{ $data->exception_status }}</td>
                                                </tr>
                                                <!-- end tr -->

                                                <tr>
                                                    <th class="fw-bold">Exception Owner :</th>
                                                    <td class="text-muted">{{ $data->exception_owner }}</td>
                                                </tr>
                                                <!-- end tr -->

                                                <tr>
                                                    <th class="fw-bold">Created At</th>
                                                    <td class="text-muted">{{ $data->created_at }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="fw-bold">Coaching</th>
                                                    <td class="text-muted">
                                                        @if($coaching_exists)
                                                            <span class="badge bg-success-subtle text-success  mb-0">Done</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary  mb-0">Not yet</span>
                                                        @endif


                                                        
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="fw-bold">Triad</th>
                                                    <td class="text-muted">
                                                        @if($triad_exists)
                                                            <span class="badge bg-success-subtle text-success  mb-0">Done</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary  mb-0">Not yet</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <!-- end tr -->
                                            </tbody><!-- end tbody -->
                                        </table>
                                    </div>
                                    <div class="col-lg-2 text-center">
                                        
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h3 class="fw-medium mb-0">OverAll Score</h3>
                                            </div>
                                            <div class="col-lg-12 text-center mx-0 my-5">
                                                <h1 class="fw-medium mb-0 display-1">
                                                    @php
                                                        $verification = (float) ($data->verification?->total_score ?? 0);
                                                        $process = (float) ($data->processCompliance?->total_score ?? 0);
                                                        $engagement = (float) ($data->engagement?->total_score ?? 0);

                                                        $sum = $process + $engagement;
                                                    @endphp

                                                    @if($verification < 200)
                                                        0%
                                                    @else
                                                        {{ $sum }}%
                                                    @endif
                                                </h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                

                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- RC OUTCOME -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header mb-0">
                                <div class="float-end">
                                    @php
                                        $ver_total_score = (float) ($data->verification?->total_score ?? 0);
                                    @endphp

                                    @if($ver_total_score < 200)
                                        SCORE: 0% 
                                        |
                                        <span class="badge bg-danger font-size-12 ms-2">FAILED</span>             
                                    @else
                                        SCORE: 100% 
                                        |
                                        <span class="badge bg-success font-size-12 ms-2">PASSED</span>                 
                                    @endif


                                </div>
                                <div class="fw-semibold font-size-18">
                                    RISK AND COMPLIANCE	
                                </div>
                                
                            </div>
                            <div class="card-body">   <!-- 👈 add this -->
                                <div class="row">
                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                    LDA completed Verification checks before sending recon report or email communication
                                                </h5>

                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Client Name
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Region (APAC,EMEA,AMR,LA etc)
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Carrier contact information (email address)
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $score_1 = (float) ($data->verification?->ver_outcome_1 ?? 0);
                                                    @endphp

                                                    @if($score_1 < 90)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $data->verification?->ver_outcome_1 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2"><i class="mdi mdi-counter"></i> FAILED</div>
                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $data->verification?->ver_outcome_1 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2"><i class="mdi mdi-counter"></i> PASSED</div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->verification?->ver_comment_1 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                    Was there any Zero Tolerance violation in relation to this exception?
                                                </h5>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $score_2 = (float) ($data->verification?->ver_outcome_2 ?? 0);
                                                    @endphp

                                                    @if($score_2 < 90)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $data->verification?->ver_outcome_2 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2"><i class="mdi mdi-counter"></i> FAILED</div>
                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $data->verification?->ver_outcome_2 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2"><i class="mdi mdi-counter"></i> PASSED</div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->verification?->ver_comment_2 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- PC OUTCOME -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header mb-0">
                                
                                <div class="fw-semibold font-size-18">
                                    PROCESS COMPLIANCE (ANALYSIS & EXCEPTION MANAGEMENT)
                                </div>
                                
                            </div>
                            <div class="card-body">   <!-- 👈 add this -->
                                <div class="row">
                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                    Used all available tools and information on hand to help resolve all areas accurately
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Utilized tools, systems, or templates available for identifying, diagnosing, and resolving issues
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Use all information to understand the situation and avoid unnecessary back and forth communications
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Checked and investigated relevant context 
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Accurate analysis completed to action the invoice/s in exception
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $pc_raw = $data->processCompliance?->pc_outcome_1 ?? 0;
                                                        $pc_score1 = (int) $pc_raw;   // force integer logic
                                                    @endphp

                                                    @if($pc_score1 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score1 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($pc_score1 === 5)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score1 }}%
                                                        </div>
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score1 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->processCompliance?->pc_comment_1 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                    Took all necessary corrective and preventive actions to fully resolve the exception and mitigate the risk of recurrence
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Decision was made through thorough, objective, and factual evidence gathering, ensuring that the outcome or result was reliable and trustworthy
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Raised appropriate ticket for logic correction or enhancement
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Requested proper approvals based on process, as necessary
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Cleared the exception before the due date
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Provided the carrier with the correct process guidance to prevent future recurrence
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $pc_raw2 = $data->processCompliance?->pc_outcome_2 ?? 0;
                                                        $pc_score2 = (int) $pc_raw2;   // force integer logic
                                                    @endphp

                                                    @if($pc_score2 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score2 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($pc_score2 === 5)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score2 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score2 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->processCompliance?->pc_comment_2 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                   Correct remedial action/s identified, carried out and agreed with the carrier, customer or internal team 
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                               Correctly escalated to the proper approver or owner
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Correctly followed the EMAIL FOLLOW UP  PROCESS (SLA)
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Raised appropritate request to correct/enhance logic, correct mapping, and other Trax related issues to be resolved
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Invoices denied or released correctly
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Educated the carrier with remedial actions for carrier owned root cause/issues
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Educated the customer with remedial actions for customer owned root cause/issues
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $pc_raw3 = $data->processCompliance?->pc_outcome_3 ?? 0;
                                                        $pc_score3 = (int) $pc_raw3;   // force integer logic
                                                    @endphp

                                                    @if($pc_score3 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score3 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($pc_score3 === 8)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score3 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score3 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->processCompliance?->pc_comment_3 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                   Quality of Transfer/Hand off - Was the transfer necessary and correctly processed?
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                               Raised ticket to the correct team (GSS, AC, Dups, Post Audit)
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Jira ticket included sufficient information for investigation and internal team support
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                 CN requested with all the neccesary details in the email
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Other transfers
                                                            </li>
                                                            
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $pc_raw4 = $data->processCompliance?->pc_outcome_4 ?? 0;
                                                        $pc_score4 = (int) $pc_raw4;   // force integer logic
                                                    @endphp

                                                    @if($pc_score4 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score4 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($pc_score4 === 5)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score4 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $pc_score4 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->processCompliance?->pc_comment_4 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- ENGAGEMENT OUTCOME -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header mb-0">
                                
                                <div class="fw-semibold font-size-18">
                                    ENGAGEMENT (RECON CALL & EMAIL COMMUNICATIONS)
                                </div>
                                
                            </div>
                            <div class="card-body">   <!-- 👈 add this -->
                                <div class="row">
                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                    Effective and positive communication with the audience
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Took the lead in the the reconcililation call (applicable to CMS) or meeting
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Communication (call/email/chat) was in line with the needs of the audience (carrier or customer)
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Appropriate acknowledgement and statements made based on the current status of the discussion (provided timeline, next steps, etc)
                                                            </li>
                                                            
                                                        </ul>
                                                    </div>
                                                </div>

                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $eng_raw = $data->engagement?->eng_outcome_1 ?? 0;
                                                        $eng_score1 = (int) $eng_raw;   // force integer logic
                                                    @endphp

                                                    @if($eng_score1 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score1 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($eng_score1 === 5)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score1 }}%
                                                        </div>
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score1 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->engagement?->eng_comment_1 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                    Appropriate questioning to arrive at correct root cause and resolution
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Probed /paraphrased appropriately to ensure understanding before providing information/answer
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Any follow up questions were relevant or aligned to identify correction action, when needed
                                                            </li>
                                                            
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                    @php
                                                        $eng_raw2 = $data->engagement?->eng_outcome_2 ?? 0;
                                                        $eng_score2 = (int) $eng_raw2;   // force integer logic
                                                    @endphp

                                                    @if($eng_score2 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score2 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($eng_score2 === 5)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score2 }}%
                                                        </div>
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score2 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->engagement?->eng_comment_2 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                   Set clear expectations with the audience relevant to the topics discussed
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                               Clearly articulated in the email the rationale for decision (clear, fair and not misleading)
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Explained the reason for any status or action being or to be made
                                                            </li>
                                                            
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                     @php
                                                        $eng_raw3 = $data->engagement?->eng_outcome_3 ?? 0;
                                                        $eng_score3 = (int) $eng_raw3;   // force integer logic
                                                    @endphp

                                                    @if($eng_score3 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score3 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($eng_score3 === 8)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score3 }}%
                                                        </div>
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score3 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->engagement?->eng_comment_3 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 d-flex">
                                        <div class="card w-100">
                                            <div class="card-body">
                                                <h5 class="font-size-15">
                                                   Showed sense of ownership and urgency relevant to the topics discussed
                                                </h5>
                                                <div class="row">
                                                    <div class="col">
                                                        <ul class="list-unstyled mb-0 pt-1">
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                               Response showed urgency or provided updates when necessary
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Remedial action was promptly and accurately carried out by the LDA
                                                            </li>
                                                            <li class="py-1">
                                                                <i class="mdi mdi-circle-medium me-1 text-success align-middle"></i>
                                                                Response was provided within the required timeframe (24–48 hours, or within the agreed upon SLA)
                                                            </li>
                                                            
                                                            
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="border-bottom pb-1 mt-1">
                                                    
                                                     @php
                                                        $eng_raw4 = $data->engagement?->eng_outcome_4 ?? 0;
                                                        $eng_score4 = (int) $eng_raw4;   // force integer logic
                                                    @endphp

                                                    @if($eng_score4 === 0)
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score4 }}%
                                                        </div>
                                                        <div class="badge bg-danger mb-2">
                                                            <i class="mdi mdi-counter"></i> Not Met
                                                        </div>

                                                    @elseif($eng_score4 === 8)
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score4 }}%
                                                        </div>
                                                        <div class="badge bg-warning mb-2">
                                                            <i class="mdi mdi-counter"></i> Coached
                                                        </div>

                                                    @else
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-star"></i> {{ $eng_score4 }}%
                                                        </div>
                                                        <div class="badge bg-success mb-2">
                                                            <i class="mdi mdi-counter"></i> Met
                                                        </div>
                                                    @endif
                                                    
                                                    
                                                    <p class="text-muted mb-4">{{ $data->engagement?->eng_comment_4 }}</p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Bussiness Analytics -->
                     <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="fw-semibold font-size-18">
                                    BUSINESS ANALYTICS (Identify problems early and optimize processes)
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">Is there a sign that carrier may complain?</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->sign_carrier }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">Is this a follow up that remains open/not addressed?</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->follow_up }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">If so, how many days?</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->many_days }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">What caused the issue, and why weren’t the invoices cleared from exceptions?</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->cause_issue }}</p>
                                        </div>
                                        
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">Impact Areas</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->impact_area }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">Impact Factors</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->impact_factor }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">Who is accountable for impact factors?</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->accountable_factors }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <h5 class="text-truncate font-size-14 mb-1">Root Cause Analysis (RCA)</h5>
                                            <p class="text-muted mb-0">{{ $data->businessAnalytic->root_cause }}</p>
                                        </div>
                                    </div>
                                </div>
                                

                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>



                </div>
                

                
                <!-- end modal -->

            </div>
        </div>
    </div>
    @include('partials.script')
</body>

</html>