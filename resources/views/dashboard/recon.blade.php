<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-5 g-3">

    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    
                    <div>
                        <h6 class="font-size-14">Total Tickets</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="total-evaluations">0</h4>
                    </div>

                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-dark-subtle">
                                <i class="bx bx-cylinder font-size-24 text-dark"></i>
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
                        <h6 class="font-size-14">Total To Do</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="todo-count">0</h4>
                    </div>

                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-secondary-subtle">
                                <i class="bx bx-list-check font-size-24 text-secondary"></i>
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
                        <h6 class="font-size-14">Total Pending</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="pending-count">0</h4>
                    </div>

                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-warning-subtle">
                                <i class="bx bx-timer font-size-24 text-warning"></i>
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
                        <h6 class="font-size-14">Total In Progress</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="inprogress-count">0</h4>
                    </div>

                    <div>
                        <div class="avatar">
                            <div class="avatar-title rounded bg-primary-subtle">
                                <i class="bx bxs-time font-size-24 text-primary"></i>
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
                        <h6 class="font-size-14">Total Closed</h6>
                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="closed-count">0</h4>
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

    <!-- Repeat this .col block for your other cards -->

</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-4">
                <select id="chartFilter" data-trigger class="form-select form-select-sm">
                    <option value="all">All Tickets</option>
                    <option value="team">My Team</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Open item aging / SLA --}}
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">Open Item Aging <small class="text-muted">(SLA: 7 days)</small></h5>
        <div class="row text-center">
            <div class="col">
                <div class="p-2 border rounded">
                    <h6 class="text-muted font-size-13 mb-1">Overdue (7+ days)</h6>
                    <h4 class="mb-0 text-danger" id="aging-overdue">0</h4>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded">
                    <h6 class="text-muted font-size-13 mb-1">0–3 days</h6>
                    <h4 class="mb-0 text-success" id="aging-0-3">0</h4>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded">
                    <h6 class="text-muted font-size-13 mb-1">4–7 days</h6>
                    <h4 class="mb-0 text-warning" id="aging-4-7">0</h4>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded">
                    <h6 class="text-muted font-size-13 mb-1">8–14 days</h6>
                    <h4 class="mb-0 text-warning" id="aging-8-14">0</h4>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded">
                    <h6 class="text-muted font-size-13 mb-1">15+ days</h6>
                    <h4 class="mb-0 text-danger" id="aging-15">0</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div id="clientChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div id="carrierChart"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table">
                    <table class="table table-striped table-centered align-middle table-nowrap mb-0 table-check">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Carrier</th>
                                <th>Total</th>
                                <th>To Do</th>
                                <th>Pending</th>
                                <th>In Progress</th>
                                <th>Closed</th>
                            </tr>
                        </thead>
                        <tbody id="top10-body"></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>