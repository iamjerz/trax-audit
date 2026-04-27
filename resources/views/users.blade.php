<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <div class="modal fade" id="add-user" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
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

    <script src="assets/js/pages/gridjs.init.js"></script>


</body>

</html>