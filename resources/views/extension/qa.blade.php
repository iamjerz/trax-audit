<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-4">QA Monitoring Form</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="col-xl-3 col-lg-4 col-sm-6">
                                <a href="#" id="back-button"><i class="bx bx-arrow-back"></i></a>
                            </div>
                        </div>
                    </div>
            <div class="row">
                <div class="col-lg-12">
                    
                    <div class="card">
                        <div class="card-header bg-primary border-primary">
                            <h4 class="card-title text-white">Trax LDA Quality Audit Form</h4>

                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <input type="hidden" id="audit-by" name="audit-by">
                                    <input type="hidden" id="csrf-token" name="csrf-token" value="{{ csrf_token() }}">
                                    <div class="mb-3">
                                        <label for="choices-single-default" class="form-label">LDA Name <span class="text-danger">*</span></label>
                                        <select class="form-control choices-js" data-trigger name="lda-name" id="lda-name" placeholder="This is a search placeholder">
                                            <option value="">Select LDA Name</option>
                                            @foreach ($Users as $item)
                                            <option value="{{ $item->employeeid }}">
                                                {{ $item->first_name }} {{ $item->last_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Audit Ticket Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datetime-js flatpickr-input datepicker-humanfd" name="audit-date1" id="audit-date1" placeholder="Select Audit Date">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label for="choices-single-default" class="form-label">Audit Sup Name <span class="text-danger">*</span></label>
                                        <select class="form-control choices-js" data-trigger name="audit-sup-name" id="choices-single-default" placeholder="This is a search placeholder">
                                            <option value="">Select Audit Sup Name</option>
                                            @foreach ($Users as $item)
                                            <option value="{{ $item->employeeid }}">
                                                {{ $item->first_name }} {{ $item->last_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label for="choices-single-default" class="form-label">Auditor Name <span class="text-danger">*</span></label>
                                        <select class="form-control choices-js" data-trigger name="auditors-name" id="auditors-name" placeholder="This is a search placeholder">
                                            <option selected value="{{ $requestor->employeeid }}">{{ $requestor->last_name }} {{ $requestor->first_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Audit Coaching Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datetime-js flatpickr-input datepicker-humanfd" name="audit-date2" id="audit-date2" placeholder="Select Audit Date">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Invoice ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="" name="invoice-id" placeholder="Enter Invoice ID">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Carrier Name <span class="text-danger">*</span></label>
                                        <!-- <input type="text" class="form-control" id="" name="carrier-name" placeholder="Enter Carrier Name"> -->
                                        <select class="form-control choices-js" data-trigger name="carrier-name" id="carrier-name" placeholder="This is a search placeholder">
                                            <option value="">Select Carrier Name</option>
                                            @foreach($carrierCodeND as $item)
                                                <option value="{{ $item['carrier_code'] }}">
                                                    {{ $item['carrier_code'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Exception Status <span class="text-danger">*</span></label>
                                        <!-- <input type="text" class="form-control" id="" name="exception-status" placeholder="Enter Exception Status"> -->
                                        <select class="form-control choices-js" data-trigger name="exception-status" id="exception-status" placeholder="This is a search placeholder">
                                            <option value="">Select Carrier Name</option>
                                            @foreach($exceptionStatus as $item)
                                                <option value="{{ $item['audit_condition'] }}">
                                                    {{ $item['audit_condition'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Exception Owner <span class="text-danger">*</span></label>
                                        <!-- <input type="text" class="form-control" id="" name="exception-owner" placeholder="Enter Exception Owner"> -->

                                        <select class="form-control choices-js" data-trigger name="exception-owner" id="exception-owner" placeholder="This is a search placeholder">
                                            <option value="">Select Exception Owner</option>
                                            <option value="Carrier Review">Carrier Review</option>
                                            <option value="Client Review">Client Review</option>
                                            <option value="Trax Review">Trax Review</option>

                                            
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Verification and Identification Section -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header  bg-primary border-primary">
                            <h4 class="card-title text-white">Verification and Identification</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- LEFT CARD -->
                                <div class="col-lg-6 mb-5 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        LDA completed Verification checks before sending recon report or email communication
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-6">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i>
                                                                Client Name
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i>
                                                                Carrier (SCAC)
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i>
                                                                Region (APAC,EMEA,AMR,LA etc)
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium align-middle text-primary me-1"></i>
                                                                Carrier contact information (email address)
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="col-lg-12 mt-2">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-3">
                                                        <textarea name="ver-iden-1-comment" id="ver-iden-1-comment" class="form-control" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER (BOTTOM JUSTIFIED) -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">R&C Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="ver-iden-1-outcome" id="ver-iden-1-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="100">Pass</option>
                                                            <option value="0">Fail</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">100%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-vid-1">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- RIGHT CARD -->
                                <div class="col-lg-6 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Was there any Zero Tolerance violation in relation to this exception?
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-3">
                                                        <textarea class="form-control" name="ver-iden-2-comment" id="ver-iden-2-comment" rows="11" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">R&C Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="ver-iden-2-outcome" id="ver-iden-2-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="100">Pass</option>
                                                            <option value="0">Fail</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">100%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-vid-2">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
                <!-- PROCESS COMPLIANCE (ANALYSIS & EXCEPTION MANAGEMENT) Section -->
                <div class="col-lg-12">
                    <div class="card mb-2">
                        <div class="card-header bg-primary border-primary">
                            <h4 class="card-title text-white">PROCESS COMPLIANCE (ANALYSIS & EXCEPTION MANAGEMENT)</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- LEFT CARD -->
                                <div class="col-lg-6 mb-5 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Used all available tools and information on hand to help resolve all areas accurately
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Utilized tools, systems, or templates available for identifying, diagnosing, and resolving issues
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Use all information to understand the situation and avoid unnecessary back and forth communications
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Checked and investigated relevant context
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Accurate analysis completed to action the invoice/s in exception
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="col-lg-12 mt-2">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="pro-com-1-comment" id="pro-com-1-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER (BOTTOM JUSTIFIED) -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">PC Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="pro-com-1-outcome" id="pro-com-1-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="10">Met</option>
                                                            <option value="5">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">10%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-pc-1">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- RIGHT CARD -->
                                <div class="col-lg-6 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Took all necessary corrective and preventive actions to fully resolve the exception and mitigate the risk of recurrence
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">
                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Decision was made through thorough, objective, and factual evidence gathering, ensuring that the outcome or result was reliable and trustworthy
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Raised appropriate ticket for logic correction or enhancement
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Requested proper approvals based on process, as necessary
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Cleared the exception before the due date
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Provided the carrier with the correct process guidance to prevent future recurrence
                                                            </p>
                                                        </li>


                                                    </ul>
                                                </div>
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="pro-com-2-comment" id="pro-com-2-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">PC Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="pro-com-2-outcome" id="pro-com-2-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="15">Met</option>
                                                            <option value="8">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">15%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-pc-2">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- LEFT CARD 1 -->
                                <div class="col-lg-6 d-flex mt-4"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Correct remedial action/s identified, carried out and agreed with the carrier, customer or internal team
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Correctly escalated to the proper approver or owner
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Correctly followed the EMAIL FOLLOW UP PROCESS (SLA)
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Raised appropritate request to correct/enhance logic, correct mapping, and other Trax related issues to be resolved
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Invoices denied or released correctly
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Educated the carrier with remedial actions for carrier owned root cause/issues
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Educated the customer with remedial actions for customer owned root cause/issues
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="col-lg-12 mt-2">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="pro-com-3-comment" id="pro-com-3-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER (BOTTOM JUSTIFIED) -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">PC Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="pro-com-3-outcome" id="pro-com-3-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="15">Met</option>
                                                            <option value="8">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">15%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-pc-3">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- RIGHT CARD 2 -->
                                <div class="col-lg-6 d-flex mt-4"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Quality of Transfer/Hand off - Was the transfer necessary and correctly processed?
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">
                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Raised ticket to the correct team (GSS, AC, Dups, Post Audit)
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Jira ticket included sufficient information for investigation and internal team support
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                CN requested with all the neccesary details in the email
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Other transfers
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="pro-com-4-comment" id="pro-com-4-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">PC Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="pro-com-4-outcome" id="pro-com-4-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="10">Met</option>
                                                            <option value="5">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">10%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-pc-4">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
                <!-- ENGAGEMENT (RECON CALL & EMAIL COMMUNICATIONS)	 Section -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-primary border-primary">
                            <h4 class="card-title text-white">ENGAGEMENT (RECON CALL & EMAIL COMMUNICATIONS)</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- LEFT CARD -->
                                <div class="col-lg-6 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Effective and positive communication with the audience
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Took the lead in the the reconcililation call (applicable to CMS) or meeting
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Communication (call/email/chat) was in line with the needs of the audience (carrier or customer)
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Appropriate acknowledgement and statements made based on the current status of the discussion (provided timeline, next steps, etc)
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="col-lg-12 mt-2">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="engagement-1-comment" id="engagement-1-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER (BOTTOM JUSTIFIED) -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">CE Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="engagement-1-outcome" id="engagement-1-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="10">Met</option>
                                                            <option value="5">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">10%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-ce-1">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- RIGHT CARD -->
                                <div class="col-lg-6 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Appropriate questioning to arrive at correct root cause and resolution
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">
                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Probed /paraphrased appropriately to ensure understanding before providing information/answer
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Any follow up questions were relevant or aligned to identify correction action, when needed
                                                            </p>
                                                        </li>



                                                    </ul>
                                                </div>
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="engagement-2-comment" id="engagement-2-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">CE Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="engagement-2-outcome" id="engagement-2-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="10">Met</option>
                                                            <option value="5">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">10%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-ce-2">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- LEFT CARD 1 -->
                                <div class="col-lg-6 d-flex mt-4"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Set clear expectations with the audience relevant to the topics discussed
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Clearly articulated in the email the rationale for decision (clear, fair and not misleading)
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Explained the reason for any status or action being or to be made
                                                            </p>
                                                        </li>

                                                    </ul>
                                                </div>

                                                <div class="col-lg-12 mt-2">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="engagement-3-comment" id="engagement-3-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER (BOTTOM JUSTIFIED) -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">CE Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="engagement-3-outcome" id="engagement-3-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="15">Met</option>
                                                            <option value="8">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">15%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-ce-3">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- RIGHT CARD 2 -->
                                <div class="col-lg-6 d-flex mt-4"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Showed sense of ownership and urgency relevant to the topics discussed
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">
                                                <div class="col-lg-12">
                                                    <ul class="list-unstyled ps-0 mb-0 mt-3">
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Response showed urgency or provided updates when necessary
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Remedial action was promptly and accurately carried out by the LDA
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <p class="text-muted mb-1">
                                                                <i class="mdi mdi-circle-medium text-primary me-1"></i>
                                                                Response was provided within the required timeframe (24–48 hours, or within the agreed upon SLA)
                                                            </p>
                                                        </li>

                                                    </ul>
                                                </div>
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">Auditors Comments</h5>
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="engagement-4-comment" id="engagement-4-comment" rows="5" placeholder="Enter Comments..."></textarea>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- FOOTER -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5 class="font-size-14">CE Outcome</h5>
                                                    <div class="mb-3">
                                                        <select class="form-control choices-js" data-trigger name="engagement-4-outcome" id="engagement-4-outcome">
                                                            <option value="">Select Rating</option>
                                                            <option value="15">Met</option>
                                                            <option value="8">Coached</option>
                                                            <option value="0">Not Met</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Target</h5>
                                                    <div class="mb-3">15%</div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <h5 class="font-size-14">Score</h5>
                                                    <div class="mb-3" id="res-ce-4">NA</div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
                <!-- BUSINESS ANALYTICS Identify problems early and optimize processes Section -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-primary border-primary">
                            <h4 class="card-title text-white">Business Analytics</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- LEFT CARD -->
                                <div class="col-lg-8 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h5 class="font-size-14">
                                                        Identify problems early and optimize processes Section
                                                    </h5>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">

                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">Is there a sign that carrier may complain?</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="sign-carrier" id="sign-carrier" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="Yes">Yes</option>
                                                                <option value="No">No</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">Is this a follow up that remains open/not addressed?</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="follow-up" id="follow-up" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="Yes">Yes</option>
                                                                <option value="No">No</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">If so, how many days?</label>
                                                        <div class="col-md-4">
                                                            <input class="form-control" type="number" id="many-days" name="many-days">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">What caused the issue, and why weren’t the invoices cleared from exceptions?</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="cause-issue" id="cause-issue" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="Carrier Data Issue">Carrier Data Issue</option>
                                                                <option value="Rating Issue">Rating Issue</option>
                                                                <option value="Delayed in Response">Delayed in Response</option>
                                                                <option value="Incorrect Action Taken">Incorrect Action Taken</option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">Impact Areas</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="impact-area" id="impact-area" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="System">System</option>
                                                                <option value="Tools/Technology">Tools/Technology</option>
                                                                <option value="People">People</option>
                                                                <option value="Processes">Processes</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">Impact Factors</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="impact-factor" id="impact-factor" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="People - Employee actions">People - Employee actions</option>
                                                                <option value="People - Training Gap">People - Training Gap</option>
                                                                <option value="People - Behaviour">People - Behaviour</option>
                                                                <option value="People - Decision- Making">People - Decision- Making</option>
                                                                <option value="Processes - Workflow">Processes - Workflow</option>
                                                                <option value="Processes - Procedures">Processes - Procedures</option>
                                                                <option value="Processes - Protocols or Operational guidelines">Processes - Protocols or Operational guidelines</option>
                                                                <option value="Tools/Technology - Software">Tools/Technology - Software</option>
                                                                <option value="Tools/Technology - Systems">Tools/Technology - Systems</option>
                                                                <option value="Systems - Internal systems or processes">Systems - Internal systems or processes</option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">Who is accountable for impact factors?</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="accountable-factors" id="accountable-factors" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="Carrier">Carrier</option>
                                                                <option value="Client - Procurement">Client - Procurement</option>
                                                                <option value="Client">Client</option>
                                                                <option value="Trax LDA">Trax LDA</option>
                                                                <option value="Trax Internal Team">Trax Internal Team</option>


                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="example-text-input" class="col-md-8 col-form-label">Root Cause Analysis (RCA)</label>
                                                        <div class="col-md-4">
                                                            <select class="form-control choices-js" data-trigger name="root-cause" id="root-cause" placeholder="This is a search placeholder">
                                                                <option value="">Select Option</option>
                                                                <option value="Controllable">Controllable</option>
                                                                <option value="Uncontrollable">Uncontrollable</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>





                                            </div>
                                        </div>

                                        <!-- FOOTER (BOTTOM JUSTIFIED) -->
                                        <!-- <div class="card-footer bg-transparent border-top">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <h5 class="font-size-14">R&C Outcome</h5>
                                                        <div class="mb-3">
                                                            <select class="form-control" data-trigger name="choices-single-default" id="choices-single-default">
                                                                <option value="">Select Rating</option>
                                                                <option value="100">Pass</option>
                                                                <option value="0">Fail</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3">
                                                        <h5 class="font-size-14">Target</h5>
                                                        <div class="mb-3">100%</div>
                                                    </div>

                                                    <div class="col-lg-3">
                                                        <h5 class="font-size-14">Score</h5>
                                                        <div class="mb-3 text-success">100%</div>
                                                    </div>
                                                </div>
                                            </div> -->

                                    </div>
                                </div>

                                <!-- RIGHT CARD -->
                                <div class="col-lg-4 d-flex"><!-- ✅ added d-flex -->
                                    <div class="card h-100 w-100 d-flex flex-column"><!-- ✅ added h-100 + flex -->

                                        <!-- BODY -->
                                        <div class="card-body flex-grow-1"><!-- ✅ added flex-grow-1 -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h3 class="fw-medium mb-0">Summary OutCome</h3>
                                                </div>

                                                <hr class="mx-1 my-2">

                                                <div class="col-lg-12">
                                                    <div class="my-3">
                                                        <h4 class="card-title">Verification and Identification</h4>
                                                        <div class="progress">
                                                            <div class="progress-bar" id="total-verification" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h4 class="card-title">PROCESS COMPLIANCE</h4>
                                                        <div class="progress">
                                                            <div class="progress-bar" id="total-process-compliance" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h4 class="card-title">ENGAGEMENT</h4>
                                                        <div class="progress">
                                                            <div class="progress-bar" id="total-engagement" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>
                                                <div class="col-lg-12 text-center">
                                                    <h3 class="fw-medium mb-0">OverAll Score</h3>
                                                </div>
                                                <div class="col-lg-12 text-center mx-0 my-5">
                                                    <h1 class="fw-medium mb-0 display-1" id="overall-score">0%</h1>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- FOOTER -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="row">
                                                <button type="button" id="submit-qa-btn" class="btn btn-primary w-100">Submit Audit</button>
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