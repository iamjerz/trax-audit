<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- gridjs css -->
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">

<!-- flatpickr css -->
<link href="assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css">
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
                            <div class="card-body">

                                <div class="position-relative">
                                    <div class="modal-button mt-2">
                                        <div class="row align-items-start">
                                            
                                            <div class="col-sm">
                                                <div class="mt-3 mt-md-0 mb-3">
                                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFormModal"><i class="mdi mdi-plus me-1"></i> Add Form</button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end row -->
                                    </div>
                                </div>



                                <div id="table-invoices-list"></div>

                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                </div>

                <!-- Modal -->
                <form action="{{ route('viewforms.createForm') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="addFormModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">New Form</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="formrow-formname-input">Form Name</label>
                                        <input type="text" class="form-control" name="formname" placeholder="Enter Form Name" id="formrow-formname-input">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="formrow-formdescription-input">Form Description</label>
                                        <input type="text" class="form-control" name="formdescription" placeholder="Enter Form Description" id="formrow-formdescription-input">
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Create</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- end modal -->

            </div>
        </div>
    </div>
    @include('partials.script')
    <!-- gridjs js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>

    <!-- flatpickr js -->
    <script src="assets/libs/flatpickr/flatpickr.min.js"></script>
    <!-- invoice-list init -->
    <script src="assets/js/pages/invoice-list.init.js"></script>
</body>

</html>