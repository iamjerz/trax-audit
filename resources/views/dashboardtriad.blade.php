<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
@include('partials.header')
<style>
    .counter {
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .counter.show {
        opacity: 1;
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
               <div class="triad-dashboard">
                    @include('dashboard.triad')
               </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- apexcharts -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <script src="{{ asset('assets/js/dashboard-triad.js') }}"></script>
</body>

</html>
