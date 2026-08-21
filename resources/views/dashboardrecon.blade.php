<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css">
@include('partials.header')
<style>
    .counter {
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .counter.show {
        opacity: 1;
    }

    /* Date Range Picker (daterangepicker.com) theming — matches this app's
       primary accent (#556ee6, same shade used on the QA dashboard's date
       filter and SweetAlert2 buttons) instead of the library's own default
       colors (#08c / #357ebd). Plain selectors, not CSS custom properties —
       this library doesn't expose theming vars — with !important to beat
       the stylesheet loaded via <link> above. */
    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background-color: #556ee6 !important;
    }

    .daterangepicker td.in-range {
        background-color: rgba(85, 110, 230, 0.15) !important;
    }

    .daterangepicker .ranges li.active {
        background-color: #556ee6 !important;
        color: #fff !important;
    }

    .daterangepicker .applyBtn {
        background-color: #556ee6 !important;
        border-color: #556ee6 !important;
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
               <div class="recon-dashboard">
                    @include('dashboard.recon')
               </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Date Range Picker (daterangepicker.com) — needs jQuery (above) + Moment.js loaded first -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
    <!-- apexcharts -->
    <!-- Sweet Alerts js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <script src="{{ asset('assets/js/dashboard-recon.js') }}"></script>
</body>

</html>