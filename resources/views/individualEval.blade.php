<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- gridjs css -->
<link rel="stylesheet" href="{{ asset('assets/libs/gridjs/theme/mermaid.min.css') }}">
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
                                <div class="row">
                                    <div class="col-lg-4 col-md-6">
                                        <input type="hidden" id="audit-by" name="audit-by" value="{{ auth()->user()->employeeid }}">
                                        <div class="mb-3">
                                            <label for="choices-single-default" class="form-label">LDA Name <span class="text-danger">*</span></label>
                                            <select class="form-control" data-trigger name="lda-name" id="lda-name" placeholder="This is a search placeholder">
                                                <option value="">Select LDA Name</option>
                                                @foreach ($logisticsUsers as $logisticsUser)
                                                    <option value="{{ $logisticsUser->employeeid }}">
                                                        {{ $logisticsUser->first_name }} {{ $logisticsUser->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Audit Date <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control flatpickr-input datepicker-humanfd" name="audit-date1" id="audit-date1" placeholder="Select Audit Date">
                                        </div>
                                    </div>
                                </div>
                                

                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>


                    <div id="container-body-body">
                       <!-- body here -->
                        
                    </div>
                </div>

                
            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/libs/gridjs/gridjs.umd.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>

        document.addEventListener("DOMContentLoaded", function () {

        let state = {
            user: null,
            date_from: null,
            date_to: null
        };

        /* ========== Choices.js ========== */
        const elements = document.querySelectorAll("[data-trigger]");

            elements.forEach(el => {
                const choiceInstance = new Choices(el, {
                    searchEnabled: true,
                    searchFields: ['label', 'value'],
                    shouldSort: false,
                    placeholder: true
                });

                el.addEventListener("change", function () {
                    state.user = el.value;

                    checkUserInputs(); // unified logger
                });
            });

            
            /* ========== Flatpickr ========== */
            document.querySelectorAll(".datepicker-humanfd").forEach(el => {
                flatpickr(el, {
                    mode: "range",
                    altInput: true,
                    altFormat: "F j, Y",
                    dateFormat: "Y-m-d",
                    allowInput: true,
                    conjunction: " to ",

                    onChange: function(selectedDates) {
                        if (selectedDates.length === 2) {
                            const formatLocal = (d) => {
                                const y = d.getFullYear();
                                const m = String(d.getMonth() + 1).padStart(2, '0');
                                const day = String(d.getDate()).padStart(2, '0');
                                return `${y}-${m}-${day}`;
                            };

                            state.date_from = formatLocal(selectedDates[0]);
                            state.date_to   = formatLocal(selectedDates[1]);
                        } else {
                            state.date_from = null;
                            state.date_to   = null;
                        }

                        checkUserInputs();
                    }
                });
            });

            function checkUserInputs() {
                if (state.user && state.date_from && state.date_to) {
                    logState();   // all fields present
                } else if (state.user) {
                    logState();   // only user present
                }
            }

            /* ========== Unified Logger ========== */
            function logState() {
                console.log("🔍 Current Filter State:");
                console.table(state);

                // Example for API usage:
                // fetch(`/api/tickets?user=${state.user}&from=${state.date_from}&to=${state.date_to}`)
                $.ajax({
                    url: '/load-blade',
                    type: 'GET',
                    data: {
                        id: state.user,
                        date_from: state.date_from,
                        date_to: state.date_to
                    },
                    beforeSend: function () {
                        $('#container-body-body').html('<div class="text-center"><div class="spinner-grow text-primary m-1" role="status"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-primary m-1" role="status"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-primary m-1" role="status"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-primary m-1" role="status"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-primary m-1" role="status"><span class="sr-only">Loading...</span></div></div>');
                    },
                    success: function(html){
                        $('#container-body-body').html(html);
                    },
                    error: function (xhr) {
                        console.error("❌ AJAX Error:", xhr.responseText);
                    }
                });
            }
        });

    </script>
</body>

</html>