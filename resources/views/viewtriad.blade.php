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