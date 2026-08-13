<header id="page-topbar" class="isvertical-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="/homepage" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" height="26">
                    </span>
                    <span class="logo-lg">
                        <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" height="26">
                    </span>
                </a>

                <a href="/homepage" class="logo logo-light">
                    <span class="logo-lg">
                        <img src="assets/images/logo-light.png" alt="" height="30">
                    </span>
                    <span class="logo-sm">
                        <img src="assets/images/logo-light-sm.png" alt="" height="26">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect vertical-menu-btn">
                <i class="bx bx-menu align-middle"></i>
            </button>

            <!-- start page title -->
            <div class="page-title-box align-self-center d-none d-md-block">
                <h4 class="page-title mb-0">Hi, Welcome Back!</h4>
            </div>
            <!-- end page title  -->

        </div>

        <div class="d-flex">
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item user text-start d-flex align-items-center" id="page-header-user-dropdown-v" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-2 fw-medium font-size-15">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h6>
                        <p class="mb-0 font-size-11 text-muted">
                            {{ auth()->user()->email ?? 'juan.delacruz@traxtech.com' }}
                        </p>
                    </div>



                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="mdi mdi-logout text-muted font-size-16 align-middle me-2"></i>
                            <span class="align-middle">Logout</span>
                        </button>
                    </form>
                </div>



            </div>
        </div>
    </div>
</header>
<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="/homepage" class="logo logo-dark">
            <span class="logo-sm">
                <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" height="26">
            </span>
            <span class="logo-lg">
                <img src="https://www.traxtech.com/hubfs/build_assets/trax-core/251/js_client_assets/assets/logo-hwTUqwwd.svg" alt="" height="28">
            </span>
        </a>

        <a href="/homepage" class="logo logo-light">
            <span class="logo-lg">
                <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" height="30">
            </span>
            <span class="logo-sm">
                <img src="https://www.traxtech.com/hubfs/build_assets/trax-core/251/js_client_assets/assets/logo-hwTUqwwd.svg" alt="" height="26">
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect vertical-menu-btn">
        <i class="bx bx-menu align-middle"></i>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li>
                    <a href="/homepage">
                        <i class="bx bx-home-alt icon nav-icon"></i>
                        <span class="menu-item" data-key="t-calendar">Main</span>
                    </a>

                </li>
                <li>
                    <a href="/my-evaluations">
                        <i class="bx bx-user-check icon nav-icon"></i>
                        <span class="menu-item" data-key="t-my-evaluations">My Evaluations</span>
                    </a>
                </li>

                <!-- Hypercare Tool — admin only for now; more roles/access to come -->
                @if($access->contains('access_type', 'admin'))
                <li class="menu-title" data-key="t-menu">Hypercare Tool</li>
                <li>
                    <a href="/hypercare-dashboard">
                        <i class="bx bx-user-check icon nav-icon"></i>
                        <span class="menu-item" data-key="t-my-evaluations">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="/sign-off">
                        <i class="bx bx-user-check icon nav-icon"></i>
                        <span class="menu-item" data-key="t-my-evaluations">Sign Off</span>
                    </a>
                </li>
                @endif

                <li class="menu-title" data-key="t-menu">Dashboard</li>
                @if($pageAccess->contains('dashboard-qa'))
                    <li>
                        <a href="/dashboard-qa">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">QA Monitoring</span>
                        </a>

                    </li>
                @endif
                @if($pageAccess->contains('dashboard-recon'))
                    <li>
                        <a href="/dashboard-recon">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Action Register</span>
                        </a>
                    </li>
                @endif
                @if($pageAccess->contains('dashboard-triad'))
                    <li>
                        <a href="/dashboard-triad">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Triad</span>
                        </a>

                    </li>
                @endif
                <!-- Dashboard End -->
                @if($pageAccess->contains('monitoring-form'))
                <li class="menu-title" data-key="t-menu">Forms</li>
                <li>
                    <a href="/monitoringform">
                        <i class="bx bx-food-menu icon nav-icon"></i>
                        <span class="menu-item" data-key="t-calendar">QA Monitoring Form</span>
                    </a>

                </li>
                @endif
                <!-- <li>
                    <a href="/viewcoaching">
                        <i class="bx bx-food-menu icon nav-icon"></i>
                        <span class="menu-item" data-key="t-calendar">Coaching</span>
                    </a>
                    
                </li>
                <li>
                    <a href="/viewtriad">
                        <i class="bx bx-food-menu icon nav-icon"></i>
                        <span class="menu-item" data-key="t-calendar">Triad</span>
                    </a>
                    
                </li> -->

                <!-- Name Divider -->
                <!-- <li class="menu-title" data-key="t-applications">Tools </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="bx bx-store icon nav-icon"></i>
                        <span class="menu-item" data-key="t-ecommerce">Forms</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/viewforms" data-key="t-forms">List of Forms</a></li>
                    </ul>
                </li> -->
                <!-- Name Divider -->
                 @if($pageAccess->contains('eval-individual')
                    || $pageAccess->contains('monitoring-ticket')
                    || $pageAccess->contains('coaching-ticket')
                    || $pageAccess->contains('triad-ticket')
                    || $pageAccess->contains('recon-ticket')
                    || $pageAccess->contains('recon-overdue')
                    || $pageAccess->contains('client-carrier-health'))
                <li class="menu-title" data-key="t-applications">Reports </li>
                    @if($pageAccess->contains('eval-individual'))
                    <li>
                        <a href="/eval-individual">
                            <i class="bx bxs-report icon nav-icon"></i>
                            <span class="menu-item" data-key="t-recon-overdue">Evaluations</span>
                        </a>
                    </li>
                    @endif
                    @if($pageAccess->contains('monitoring-ticket'))
                    <li>
                        <a href="/monitoring-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">QA Monitoring List</span>
                        </a>
                    </li>
                    @endif
                    @if($pageAccess->contains('coaching-ticket'))
                    <li>
                        <a href="/coaching-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Coaching Ticket</span>
                        </a>

                    </li>
                    @endif
                    @if($pageAccess->contains('triad-ticket'))
                    <li>
                        <a href="/triad-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Triad Ticket</span>
                        </a>

                    </li>
                    @endif
                    @if($pageAccess->contains('recon-ticket'))
                    <li>
                        <a href="/recon-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Action Register Ticket</span>
                        </a>

                    </li>
                    @endif
                    @if($pageAccess->contains('recon-overdue'))
                    <li>
                        <a href="/recon-overdue">
                            <i class="bx bx-time-five icon nav-icon"></i>
                            <span class="menu-item" data-key="t-recon-overdue">Overdue Items</span>
                        </a>
                    </li>
                    @endif
                    @if($pageAccess->contains('client-carrier-health'))
                    <li>
                        <a href="/analytics/client-carrier-health">
                            <i class="bx bx-pulse icon nav-icon"></i>
                            <span class="menu-item" data-key="t-client-health">Client/Carrier Health</span>
                        </a>
                    </li>
                    @endif
                @endif
                <!-- Name Divider -->
                @if($pageAccess->contains('lda-scorecard')
                    || $pageAccess->contains('pending-acknowledgements')
                    || $pageAccess->contains('auditor-productivity')
                    || $pageAccess->contains('root-cause')
                    || $pageAccess->contains('audit-coverage'))
                <li class="menu-title" data-key="t-management">Management Reports</li>
                @if($pageAccess->contains('lda-scorecard'))
                <li>
                    <a href="/lda-scorecard">
                        <i class="bx bxs-user-detail icon nav-icon"></i>
                        <span class="menu-item" data-key="t-lda-scorecard">LDA Scorecard</span>
                    </a>
                </li>
                @endif
                @if($pageAccess->contains('pending-acknowledgements'))
                <li>
                    <a href="/reports/pending-acknowledgements">
                        <i class="bx bx-time icon nav-icon"></i>
                        <span class="menu-item" data-key="t-pending-ack">Pending Acknowledgements</span>
                    </a>
                </li>
                @endif
                @if($pageAccess->contains('auditor-productivity'))
                <li>
                    <a href="/analytics/auditor-productivity">
                        <i class="bx bx-bar-chart icon nav-icon"></i>
                        <span class="menu-item" data-key="t-auditor-prod">Auditor Productivity</span>
                    </a>
                </li>
                @endif
                @if($pageAccess->contains('root-cause'))
                <li>
                    <a href="/analytics/root-cause">
                        <i class="bx bx-pie-chart-alt-2 icon nav-icon"></i>
                        <span class="menu-item" data-key="t-root-cause">Root Cause Analytics</span>
                    </a>
                </li>
                @endif
                @if($pageAccess->contains('audit-coverage'))
                <li>
                    <a href="/analytics/audit-coverage">
                        <i class="bx bx-list-check icon nav-icon"></i>
                        <span class="menu-item" data-key="t-coverage">Audit Coverage</span>
                    </a>
                </li>
                @endif
                @endif
                <!-- Disputes -->
                @if($pageAccess->contains('disputes'))
                <li>
                    <a href="/reports/disputes">
                        <i class="bx bx-error-circle icon nav-icon"></i>
                        <span class="menu-item" data-key="t-disputes">Disputes</span>
                    </a>
                </li>
                @endif
                <!-- Name Divider -->
                @if($pageAccess->contains('score-approvals'))
                <li class="menu-title" data-key="t-manager-tools">Manager Tools</li>
                <li>
                    <a href="/reports/corrections">
                        <i class="bx bx-check-shield icon nav-icon"></i>
                        <span class="menu-item" data-key="t-corrections">Score Approvals</span>
                    </a>
                </li>
                @endif
                <!-- Name Divider -->
                 @if($access->contains('access_type', 'admin'))
                <li class="menu-title" data-key="t-applications">Administrator </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="bx bxs-group icon nav-icon"></i>
                        <span class="menu-item" data-key="t-user">User</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <!-- <li><a href="ecommerce-products.html" data-key="t-users-add">Add New User</a></li> -->
                        <li><a href="/users" data-key="t-users">List of Users</a></li>
                        <li><a href="/page-access" data-key="t-page-access">Page Access</a></li>
                        <li><a href="/positions" data-key="t-positions">Positions</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/audit-trail">
                        <i class="bx bx-history icon nav-icon"></i>
                        <span class="menu-item" data-key="t-audit-trail">Audit Trail</span>
                    </a>
                </li>
                <li>
                    <a href="/extension-details">
                        <i class="bx bx-extension icon nav-icon"></i>
                        <span class="menu-item" data-key="t-extension-details">Extension Details</span>
                    </a>
                </li>
                @endif

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<header class="ishorizontal-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="/homepage" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" height="26">
                    </span>
                    <span class="logo-lg">
                        <img src="https://www.traxtech.com/hubfs/Artboard%201.png" alt="" height="28">
                    </span>
                </a>

                <a href="/homepage" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-light-sm.png" alt="" height="26">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-light.png" alt="" height="30">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 d-lg-none header-item" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="bx bx-menu align-middle"></i>
            </button>

            <!-- start page title -->
            <div class="page-title-box align-self-center d-none d-md-block">
                <h4 class="page-title mb-0">Hi, Welcome Back!</h4>
            </div>
            <!-- end page title -->

        </div>

        <div class="d-flex">


        </div>
    </div>

    <div class="topnav">
        <div class="container-fluid">
            <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

                <div class="collapse navbar-collapse" id="topnav-menu-content">

                </div>
            </nav>
        </div>
    </div>
</header>