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
                            <div class="card-body">
                                <h3 class="card-title">Form Builder - {{ $form->form_name }}</h3>
                            </div>
                        </div>
                    </div>

                   
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                    {!! $form->form_html !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                    {!! $form->form_html !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                    {!! $form->form_html !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')


</body>

</html>