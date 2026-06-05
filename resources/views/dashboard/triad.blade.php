<div class="row mb-2">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Triad Dashboard</h4>
        </div>
    </div>
</div>

{{-- Summary cards --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-5 g-3">

    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="font-size-14">Total Triads</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="triad-total">0</h4>
                    </div>
                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-dark-subtle">
                                <i class="bx bx-list-ul font-size-24 text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="font-size-14">Overall Pass Rate</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22"><span id="triad-pass-rate">0</span>%</h4>
                    </div>
                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-info-subtle">
                                <i class="bx bx-bar-chart-alt-2 font-size-24 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="font-size-14">Total Pass</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="triad-pass">0</h4>
                    </div>
                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-success-subtle">
                                <i class="bx bx-badge-check font-size-24 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="font-size-14">Total Fail</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="triad-fail">0</h4>
                    </div>
                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-danger-subtle">
                                <i class="bx bx-x-circle font-size-24 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="font-size-14">This Month</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="triad-month">0</h4>
                    </div>
                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-primary-subtle">
                                <i class="bx bx-calendar font-size-24 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Per-criterion chart --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Pass / Fail by Criterion</h5>
                <div id="criteriaChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Criterion breakdown + evaluator breakdown --}}
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Criterion Breakdown</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-centered align-middle table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Criterion</th>
                                <th>Total</th>
                                <th>Pass</th>
                                <th>Fail</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody id="criteria-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">By Evaluator</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-centered align-middle table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Evaluator</th>
                                <th>Triads</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody id="evaluator-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
