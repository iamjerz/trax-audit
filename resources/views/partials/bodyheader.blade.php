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
                @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_dashboard'))
                <li class="menu-title" data-key="t-menu">Dashboard</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="bx bxs-dashboard icon nav-icon"></i>
                        <span class="menu-item" data-key="t-dashboard">Dashboard</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/dashboard-qa" data-key="t-ecommerce">QA Monitoring</a></li>
                        <li><a href="/dashboard-recon" data-key="t-sales">Action Register</a></li>
                        <li><a href="/dashboard-triad" data-key="t-sales">Triad</a></li>
                    </ul>
                </li>
                @endif
                @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_forms'))
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
                 @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_reports'))
                <li class="menu-title" data-key="t-applications">Reports </li>
                    @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_report_monitoring'))
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-report icon nav-icon"></i>
                            <span class="menu-item" data-key="t-ecommerce">Evaluations</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="ecommerce-products.html" data-key="t-products">Team(In Progress)</a></li>
                            <li><a href="/eval-individual" data-key="t-product-detail">Individual</a></li>
                        </ul>
                    </li>
                    @endif
                    @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_report_action_register'))
                    <li>
                        <a href="/recon-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Action Register Ticket</span>
                        </a>
                        
                    </li>
                    @endif
                    @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_report_coaching'))
                    <li>
                        <a href="/coaching-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Coaching Ticket</span>
                        </a>
                        
                    </li>
                    @endif
                    @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'web_report_triad'))
                    <li>
                        <a href="/triad-ticket">
                            <i class="bx bx-receipt icon nav-icon"></i>
                            <span class="menu-item" data-key="t-calendar">Triad Ticket</span>
                        </a>
                        
                    </li>
                    @endif
                    
                
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
                    </ul>
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