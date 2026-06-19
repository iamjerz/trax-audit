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

    .quick-link-card {
        transition: transform .15s ease, box-shadow .15s ease;
        height: 100%;
    }
    .quick-link-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.12);
    }
    .welcome-banner {
        background: linear-gradient(135deg, #1f58c7 0%, #2b8be0 100%);
        color: #fff;
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

                @php
                    $user = auth()->user();
                    $isAdmin = $access->contains('access_type', 'admin');
                    $can = fn($type) => $isAdmin || $access->contains('access_type', $type);

                    $canManage    = $can('web_managers');
                    $canApprove   = $can('web_score_approval');
                    $canDashboard = $can('web_dashboard') || $can('web_managers');
                    $canForms     = $can('web_forms') || $can('web_managers');
                    $canMonitor   = $can('web_report_monitoring');
                    $canRecon     = $can('web_report_action_register');
                    $canCoaching  = $can('web_report_coaching');
                    $canTriad     = $can('web_report_triad');
                @endphp

                {{-- ===== Welcome banner ===== --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card welcome-banner">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div>
                                        <h4 class="text-white mb-1">Welcome back, {{ $user->first_name }} {{ $user->last_name }}!</h4>
                                        <p class="mb-0 text-white-50">
                                            {{ $user->position ?? 'Team Member' }}
                                            @if($user->department) &middot; {{ $user->department }} @endif
                                        </p>
                                        <p class="mb-0 mt-2 text-white-50" style="max-width: 640px;">
                                            This is the Trax Audit Ops workspace — create QA monitoring evaluations,
                                            track reconciliation action items, run Triad and Coaching sessions, and
                                            review performance across the team.
                                        </p>
                                    </div>
                                    <div class="text-end d-none d-md-block">
                                        <i class="bx bx-bar-chart-alt-2 display-3 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Action Center ===== --}}
                <h5 class="font-size-15 mb-3 mt-2">Needs Your Attention</h5>
                <div class="row">
                    <div class="col-md-6 col-xl-4 mt-4">
                        <a href="/my-evaluations" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-3">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 mb-1">Evaluations to acknowledge</h6>
                                        <small class="text-muted">Review &amp; sign off</small>
                                    </div>
                                    <span class="badge bg-warning rounded-pill font-size-14" id="ac-pending">0</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    @if($canManage || $isAdmin)
                    <div class="col-md-6 col-xl-4 mt-4">
                        <a href="/reports/disputes" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-3">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 mb-1">Disputes to review</h6>
                                        <small class="text-muted">Open appeals</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill font-size-14" id="ac-disputes">0</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($canRecon || $canDashboard || $isAdmin)
                    <div class="col-md-6 col-xl-4 mt-4">
                        <a href="/recon-overdue" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-3">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 mb-1">My overdue action items</h6>
                                        <small class="text-muted">Open 7+ days</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill font-size-14" id="ac-overdue">0</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($isAdmin || $canApprove)
                    <div class="col-md-6 col-xl-4 mt-4">
                        <a href="/reports/corrections" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-3">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 mb-1">Corrections to approve</h6>
                                        <small class="text-muted">Awaiting your approval</small>
                                    </div>
                                    <span class="badge bg-warning rounded-pill font-size-14" id="ac-corrections">0</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>

                {{-- ===== At-a-glance stats ===== --}}
                <h5 class="font-size-15 mb-3 mt-4 pt-2">At a Glance</h5>
                <div class="row">

                    @if($canDashboard || $canMonitor)
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 text-muted">Total Evaluations</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="hp-total-eval">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-cylinder font-size-24 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isAdmin)
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 text-muted">Total LDAs</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="hp-total-lda">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-info-subtle">
                                            <i class="bx bx-group font-size-24 text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($canDashboard || $canRecon)
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 text-muted">Open Action Items</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="hp-recon-open">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-warning-subtle">
                                            <i class="bx bx-list-check font-size-24 text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($canDashboard || $canTriad)
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="font-size-14 text-muted">Total Triads</h6>
                                        <h4 class="mt-3 pt-1 mb-0 font-size-22" id="hp-total-triad">0</h4>
                                    </div>
                                    <div class="avatar">
                                        <div class="avatar-title rounded bg-success-subtle">
                                            <i class="bx bx-list-ul font-size-24 text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ===== Quick access ===== --}}
                <h5 class="font-size-15 mb-3 mt-4 pt-2">Quick Access</h5>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">

                    @if($canDashboard)
                    <div class="col">
                        <a href="/dashboard-qa" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bxs-dashboard font-size-22 text-primary"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">QA Monitoring Dashboard</h6>
                                        <small class="text-muted">Evaluation scores &amp; trends</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/dashboard-recon" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-warning-subtle">
                                            <i class="bx bx-receipt font-size-22 text-warning"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Action Register Dashboard</h6>
                                        <small class="text-muted">Reconciliation tickets</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/dashboard-triad" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-success-subtle">
                                            <i class="bx bx-bar-chart-alt-2 font-size-22 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Triad Dashboard</h6>
                                        <small class="text-muted">Pass / fail analytics</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($canForms)
                    <div class="col">
                        <a href="/monitoringform" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-info-subtle">
                                            <i class="bx bx-food-menu font-size-22 text-info"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">QA Monitoring Form</h6>
                                        <small class="text-muted">Create a new evaluation</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($canMonitor)
                    <div class="col">
                        <a href="/eval-individual" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-secondary-subtle">
                                            <i class="bx bxs-report font-size-22 text-secondary"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Individual Evaluations</h6>
                                        <small class="text-muted">Per-LDA performance</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($canRecon)
                    <div class="col">
                        <a href="/recon-ticket" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-warning-subtle">
                                            <i class="bx bx-receipt font-size-22 text-warning"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Action Register Tickets</h6>
                                        <small class="text-muted">Manage recon items</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($canCoaching)
                    <div class="col">
                        <a href="/coaching-ticket" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-primary-subtle">
                                            <i class="bx bx-conversation font-size-22 text-primary"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Coaching Tickets</h6>
                                        <small class="text-muted">SMART &amp; GROW sessions</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($canTriad)
                    <div class="col">
                        <a href="/triad-ticket" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-success-subtle">
                                            <i class="bx bx-list-ul font-size-22 text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Triad Tickets</h6>
                                        <small class="text-muted">Review triad evaluations</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    @if($isAdmin)
                    <div class="col">
                        <a href="/users" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-dark-subtle">
                                            <i class="bx bxs-group font-size-22 text-dark"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">User Management</h6>
                                        <small class="text-muted">Manage users &amp; access</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/audit-trail" class="text-reset text-decoration-none">
                            <div class="card quick-link-card mb-0">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="avatar-title rounded bg-dark-subtle">
                                            <i class="bx bx-history font-size-22 text-dark"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Audit Trail</h6>
                                        <small class="text-muted">System activity log</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                </div>

                {{-- ===== Getting started ===== --}}
                <h5 class="font-size-15 mb-3 mt-4">Getting Started</h5>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="accordion accordion-flush" id="gettingStarted">

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#gs1" aria-expanded="true">
                                                1. Run a QA Monitoring evaluation
                                            </button>
                                        </h2>
                                        <div id="gs1" class="accordion-collapse collapse show" data-bs-parent="#gettingStarted">
                                            <div class="accordion-body text-muted">
                                                Open the <strong>QA Monitoring Form</strong> to score an LDA across Verification,
                                                Process Compliance, Engagement, and Business Analytics. Verification acts as a gate —
                                                if it isn't fully met the overall score is 0%. Submitted evaluations appear on the
                                                QA Monitoring dashboard and in Individual Evaluations.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs2" aria-expanded="false">
                                                2. Track reconciliation action items
                                            </button>
                                        </h2>
                                        <div id="gs2" class="accordion-collapse collapse" data-bs-parent="#gettingStarted">
                                            <div class="accordion-body text-muted">
                                                The <strong>Action Register</strong> lists recon tickets with status (To Do, In Progress,
                                                Pending, Closed), assignments, and comments. Use the Action Register dashboard for
                                                top client/carrier breakdowns.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs3" aria-expanded="false">
                                                3. Coaching &amp; Triad follow-ups
                                            </button>
                                        </h2>
                                        <div id="gs3" class="accordion-collapse collapse" data-bs-parent="#gettingStarted">
                                            <div class="accordion-body text-muted">
                                                <strong>Triad</strong> records score 10 call-handling criteria as Pass/Fail, while
                                                <strong>Coaching</strong> captures SMART goals and GROW plans. Both link back to an
                                                evaluation and feed the Triad dashboard.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs4" aria-expanded="false">
                                                4. Need access to a section?
                                            </button>
                                        </h2>
                                        <div id="gs4" class="accordion-collapse collapse" data-bs-parent="#gettingStarted">
                                            <div class="accordion-body text-muted">
                                                The menu and the cards above only show what your account has permission for. If you
                                                need access to additional modules, contact an administrator to update your access settings.
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>

    <script>
        // Populate the at-a-glance stat cards from the existing dashboard endpoints.
        function setStat(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value ?? 0;
        }

        // Action Center counts
        fetch('/home/action-center', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                setStat('ac-pending', d.pending_ack);
                setStat('ac-disputes', d.open_disputes);
                setStat('ac-overdue', d.my_overdue);
                setStat('ac-corrections', d.pending_corrections);
            })
            .catch(() => {});

        // QA cards: total evaluations + total LDAs
        if (document.getElementById('hp-total-eval') || document.getElementById('hp-total-lda')) {
            fetch('/dashboard/cards', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(d => {
                    setStat('hp-total-eval', d.total);
                    setStat('hp-total-lda', d.total_lda);
                })
                .catch(() => {});
        }

        // Recon: open = to do + pending + in progress
        if (document.getElementById('hp-recon-open')) {
            fetch('/dashboard-recon-cards', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(d => {
                    const open = (d.todo || 0) + (d.pending || 0) + (d.in_progress || 0);
                    setStat('hp-recon-open', open);
                })
                .catch(() => {});
        }

        // Triad total
        if (document.getElementById('hp-total-triad')) {
            fetch('/dashboard-triad-cards', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(d => setStat('hp-total-triad', d.total))
                .catch(() => {});
        }
    </script>
</body>

</html>
