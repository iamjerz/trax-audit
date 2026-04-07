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
                            <h5 class="card-title">Users List <span class="text-muted fw-normal ms-2">(834)</span></h5>
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

    <!-- gridjs js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>

    <script src="assets/js/pages/gridjs.init.js"></script>


</body>

</html>