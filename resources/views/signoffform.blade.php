<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
@include('partials.header')
<style>
    /* Sign Off details card: keep labels on one line, let long values wrap
       instead of overflowing the narrow column and overlapping the next card. */
    #signoff-details-table th {
        white-space: nowrap;
        padding-right: 10px;
        vertical-align: top;
    }
    #signoff-details-table td {
        word-break: break-word;
        overflow-wrap: anywhere;
        vertical-align: top;
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
                
                <div class="row">
                    <div class="col-3">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mt-3 border-bottom pb-3">
                                            <h5 class="mb-1">Hypercare Sign Off</h5>
                                            
                                        </div>
                                        <div class="pt-3">
                                            <table id="signoff-details-table" class="table align-middle table-sm table-borderless table-centered mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Carrier Name :</th>
                                                        <td class="text-muted">New Your City</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Client Name :</th>
                                                        <td class="text-muted">New Your City</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Target Date :</th>
                                                        <td class="text-muted">New Your City</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Completion Date :</th>
                                                        <td class="text-muted">New Your City</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Created By :</th>
                                                        <td class="text-muted">New Your City</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Created At :</th>
                                                        <td class="text-muted">New Your City</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Status :</th>
                                                        <td class="text-muted">In Progress</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Last Update :</th>
                                                        <td class="text-muted">Last Update</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">
                                                            Last Update By :</th>
                                                        <td class="text-muted">Last Update Last Update Last Update</td>
                                                    </tr>
                                                    <!-- end tr -->
                                                    
                                                </tbody><!-- end tbody -->
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        Change Status
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="card">
                            <div class="card-body">
                                <div class="accordion accordion-flush" id="accordionFlushExample">
                                    <!-- Section 1 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-headingOne">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">

                                                <span class="flex-grow-1">
                                                    Section 1:
                                                    <span class="fw-bold">Carrier Data Integrations</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <label class="form-label text-uppercase mb-0">
                                                            All deployed carriers send invoices after go live
                                                        </label>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="formrow-firstname-input">QA</label>
                                                                <input type="text" class="form-control" placeholder="Feedback">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="formrow-firstname-input">QA</label>
                                                                <input type="text" class="form-control" placeholder="Feedback">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                


                                            </div>
                                        </div>
                                    </div>
                                    <!-- Section 1 End -->
                                    <!-- Section 2 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="section-2">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                                <span class="flex-grow-1">
                                                    Section 2:
                                                    <span class="fw-bold">Audit</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                            </button>

                                        </h2>
                                        <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="section-2" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck
                                                quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid
                                                single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer raw denim
                                                aesthetic synth nesciunt you probably haven't heard of them accusamus labore.</div>
                                        </div>
                                    </div>
                                    <!-- Section 2 End -->
                                    <!-- Section 3 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="section-3">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                                <span class="flex-grow-1">
                                                    Section 3:
                                                    <span class="fw-bold">Carrier Enablement to Audit Hand Off</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                            </button>

                                        </h2>
                                        <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="section-3" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck
                                                quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid
                                                single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer raw denim
                                                aesthetic synth nesciunt you probably haven't heard of them accusamus labore.</div>
                                        </div>
                                    </div>
                                    <!-- Section 3 End -->
                                    <!-- Section 4 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="section-4">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse4" aria-expanded="false" aria-controls="flush-collapse4">
                                                <span class="flex-grow-1">
                                                    Section 4:
                                                    <span class="fw-bold">Cost Allocation</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                            </button>

                                        </h2>
                                        <div id="flush-collapse4" class="accordion-collapse collapse" aria-labelledby="section-3" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck
                                                quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid
                                                single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer raw denim
                                                aesthetic synth nesciunt you probably haven't heard of them accusamus labore.</div>
                                        </div>
                                    </div>
                                    <!-- Section 4 End -->
                                    <!-- Section 5 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="section-5">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse5" aria-expanded="false" aria-controls="flush-collapse5">
                                                <span class="flex-grow-1">
                                                    Section 5:
                                                    <span class="fw-bold">Settlement</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                                
                                            </button>

                                        </h2>
                                        <div id="flush-collapse5" class="accordion-collapse collapse" aria-labelledby="section-3" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck
                                                quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid
                                                single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer raw denim
                                                aesthetic synth nesciunt you probably haven't heard of them accusamus labore.</div>
                                        </div>
                                    </div>
                                    <!-- Section 5 End -->
                                    <!-- Section 6 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="section-6">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse6" aria-expanded="false" aria-controls="flush-collapse6">
                                                <span class="flex-grow-1">
                                                    Section 6:
                                                    <span class="fw-bold">Reporting</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                                
                                            </button>

                                        </h2>
                                        <div id="flush-collapse6" class="accordion-collapse collapse" aria-labelledby="section-3" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck
                                                quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid
                                                single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer raw denim
                                                aesthetic synth nesciunt you probably haven't heard of them accusamus labore.</div>
                                        </div>
                                    </div>
                                    <!-- Section 6 End -->
                                    <!-- Section 7 Start -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="section-7">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse7" aria-expanded="false" aria-controls="flush-collapse7">
                                                <span class="flex-grow-1">
                                                    Section 7:
                                                    <span class="fw-bold">Delivery</span>
                                                </span>

                                                <span class="badge bg-primary me-2">
                                                    In Progress
                                                </span>
                                                
                                            </button>

                                        </h2>
                                        <div id="flush-collapse7" class="accordion-collapse collapse" aria-labelledby="section-3" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck
                                                quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid
                                                single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer raw denim
                                                aesthetic synth nesciunt you probably haven't heard of them accusamus labore.</div>
                                        </div>
                                    </div>
                                    <!-- Section 7 End -->
                                </div><!-- end accordion -->
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
