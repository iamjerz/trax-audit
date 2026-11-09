    <head>

        <meta charset="utf-8" />
        <title>Trax | Audit Ops Forms Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta name="auth-token" content="{{session('api_token')}}">
        <meta content="Themesdesign" name="author" />

        <!-- Global loading indicator: a thin top progress bar that shows
             on every full-page navigation (link clicks, form submits, back/
             forward -- via `beforeunload`, through to the new page's `load`
             event) and on every fetch()/XHR call the app makes (dashboard
             cards, chart data, Grid.js tables, filter reloads, etc). Pure
             CSS pseudo-element driven by classes on <html>, so it needs no
             DOM element and works from the very first line of <head>, on
             every page (included here rather than in bodyheader/script
             partials since this is the one partial even login.blade.php and
             auth/change-password.blade.php include). Inlined (not a
             separate asset file) so it's active before any other script on
             the page runs, with zero extra network request. -->
        <style>
            body::before {
                content: "";
                position: fixed;
                top: 0;
                left: 0;
                height: 3px;
                width: 0%;
                background: #556ee6;
                z-index: 99999;
                opacity: 0;
                pointer-events: none;
                transition: width 0.4s ease, opacity 0.2s ease;
            }
            html.app-loading body::before {
                opacity: 1;
                width: 85%;
            }
            html.app-loading.app-loading-done body::before {
                width: 100%;
                transition: width 0.2s ease, opacity 0.2s ease 0.2s;
            }

            /* Full-page navigation overlay: blurs + lightly dims the
               content area (frosted-glass look via backdrop-filter) and
               shows a spinner while a full page navigation is in flight
               (link click, form submit, back/forward -- through to the new
               page's `load` event). Unlike the thin bar above, this does
               NOT trigger on every background fetch -- navigation only.
               Sits at z-index 1000/1001, below the sidebar and top bar
               (both z-index 1005, both have opaque backgrounds), so they
               stay fully visible and clickable the whole time without
               needing to know their exact width/position. */
            html::before {
                content: "";
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, 0.35);
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
                z-index: 1000;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.25s ease;
            }
            html::after {
                content: "";
                position: fixed;
                top: 50%;
                left: 50%;
                width: 48px;
                height: 48px;
                margin: -24px 0 0 -24px;
                border: 4px solid rgba(85, 110, 230, 0.2);
                border-top-color: #556ee6;
                border-radius: 50%;
                z-index: 1001;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            }
            html.app-navigating::before {
                opacity: 1;
                pointer-events: auto;
            }
            html.app-navigating::after {
                opacity: 1;
                animation: app-spin 0.7s linear infinite;
            }
            @keyframes app-spin {
                to { transform: rotate(360deg); }
            }
        </style>
        <script>
            (function () {
                if (window.__appLoaderInstalled) return;
                window.__appLoaderInstalled = true;

                // Counts everything currently "in flight": starts at 1 for
                // the page's own load, +1 per fetch/XHR call. The bar only
                // hides once every one of them has finished.
                var pending = 1;
                var safetyTimer = null;

                function show() {
                    document.documentElement.classList.remove('app-loading-done');
                    document.documentElement.classList.add('app-loading');
                    if (!safetyTimer) {
                        // Dead-man's switch: never leave the bar stuck
                        // forever if something never resolves (dropped
                        // connection, stalled stream, a rogue fetch with no
                        // matching done()).
                        safetyTimer = setTimeout(function () {
                            pending = 0;
                            finish();
                        }, 15000);
                    }
                }

                function finish() {
                    clearTimeout(safetyTimer);
                    safetyTimer = null;
                    document.documentElement.classList.add('app-loading-done');
                    setTimeout(function () {
                        document.documentElement.classList.remove('app-loading', 'app-loading-done');
                    }, 200);
                }

                function start() {
                    pending++;
                    show();
                }

                function done() {
                    pending = Math.max(0, pending - 1);
                    if (pending === 0) finish();
                }

                // Exposed so any page-specific script can show the bar for
                // a client-side-only operation that isn't a fetch/XHR call.
                window.AppLoader = { start: start, done: done };

                show();
                window.addEventListener('beforeunload', start);
                window.addEventListener('load', done);

                var nativeFetch = window.fetch;
                if (nativeFetch) {
                    window.fetch = function () {
                        start();
                        return nativeFetch.apply(this, arguments).finally(done);
                    };
                }

                // Covers $.ajax() and raw XMLHttpRequest too (jQuery's ajax
                // uses XHR under the hood) -- patched at the prototype
                // level so no per-call-site changes are needed anywhere.
                var nativeOpen = XMLHttpRequest.prototype.open;
                var nativeSend = XMLHttpRequest.prototype.send;
                XMLHttpRequest.prototype.open = function () {
                    this.__trackedByAppLoader = true;
                    return nativeOpen.apply(this, arguments);
                };
                XMLHttpRequest.prototype.send = function () {
                    if (this.__trackedByAppLoader) {
                        start();
                        this.addEventListener('loadend', done);
                    }
                    return nativeSend.apply(this, arguments);
                };
            })();

            (function () {
                // Full-page navigation overlay -- deliberately separate
                // state from the fetch/XHR-driven bar above: this one
                // shows only for full navigations, not for every
                // dashboard/background refresh.
                if (window.__appNavOverlayInstalled) return;
                window.__appNavOverlayInstalled = true;

                var safetyTimer = null;

                function show() {
                    document.documentElement.classList.add('app-navigating');
                    clearTimeout(safetyTimer);
                    // Dead-man's switch -- never leave the page stuck
                    // un-clickable if `load` never fires for some reason.
                    safetyTimer = setTimeout(hide, 15000);
                }

                function hide() {
                    clearTimeout(safetyTimer);
                    safetyTimer = null;
                    document.documentElement.classList.remove('app-navigating');
                }

                show(); // this page itself is "loading" until `load` fires
                window.addEventListener('beforeunload', show);
                window.addEventListener('load', hide);
            })();
        </script>

        <!-- App favicon -->
        <link rel="shortcut icon" href="https://www.traxtech.com/hubfs/Artboard%201.png">
        <!-- gridjs css -->
        <link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- flatpickr css -->
        <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">

        <!-- datepicker css -->
        <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

        <!-- plugin css -->
        <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
        <!-- Sweet Alert-->
        <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    </head>