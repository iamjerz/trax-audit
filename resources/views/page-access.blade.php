<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.header')

<style>
    .page-access-wrap {
        max-height: 72vh;
        overflow: auto;
        border: 1px solid #eff2f7;
    }

    .page-access-wrap table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .page-access-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #fff;
        white-space: normal;
        min-width: 110px;
        max-width: 130px;
        vertical-align: middle;
        font-size: 11.5px;
        text-align: center;
        border-bottom: 2px solid #eff2f7 !important;
    }

    .page-access-wrap tbody th {
        position: sticky;
        left: 0;
        z-index: 2;
        background: #fff;
        white-space: nowrap;
        text-align: left;
        vertical-align: middle;
    }

    .page-access-wrap thead th:first-child {
        left: 0;
        z-index: 4;
        min-width: 160px;
    }

    .page-access-wrap td {
        text-align: center;
        vertical-align: middle;
    }

    .page-access-cell.cell-dirty {
        background-color: #fff3cd !important;
        border-radius: 4px;
    }

    .page-access-checkbox {
        width: 1.15em;
        height: 1.15em;
        cursor: pointer;
    }

    /* Bootstrap's default unchecked border is a very light gray that all but
       disappears on a white cell background — darken it. Checked styling is
       left to Bootstrap as-is. */
    .page-access-checkbox:not(:checked) {
        border: 2px solid #74788d;
    }

    /* Row/column hover crosshair — classes are toggled by page-access.js.
       IMPORTANT: these must be OPAQUE colors, not rgba()/alpha. The first
       column and header row are position:sticky, which relies on their
       background fully covering whatever scrolls underneath them. A
       translucent hover color let the scrolled-under cells show through
       ("ghosting") on the sticky column while scrolling. Specificity is
       also bumped above the sticky-header rules above (which set an
       explicit white background) so the highlight shows through at all. */
    .page-access-wrap tbody td.pa-hover-row,
    .page-access-wrap tbody th.pa-hover-row,
    .page-access-wrap thead th.pa-hover-row,
    .page-access-wrap tbody td.pa-hover-col,
    .page-access-wrap tbody th.pa-hover-col,
    .page-access-wrap thead th.pa-hover-col {
        background-color: #f2f4fd;
    }

    .page-access-wrap tbody td.pa-hover-row.pa-hover-col,
    .page-access-wrap tbody th.pa-hover-row.pa-hover-col,
    .page-access-wrap thead th.pa-hover-row.pa-hover-col {
        background-color: #dfe4fa;
    }
</style>

<body>
    <div id="layout-wrapper">
        @include('partials.bodyheader')
    </div>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title mb-1">Page Access</h5>
                            <p class="text-muted mb-0 font-size-13">
                                Check a box to give every user with that Position access to that page. Uncheck to remove it.
                                Nothing changes until you click <strong>Save Changes</strong>. Admin-only tools and the
                                Chrome extension aren't part of this — those are unaffected.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 text-end">
                            <button type="button" class="btn btn-light waves-effect" id="discard-changes-btn" disabled>
                                Discard
                            </button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="save-changes-btn" disabled>
                                <i class="bx bx-save font-size-16 align-middle me-1"></i>
                                <span id="save-btn-label">Save Changes</span>
                                <span id="pending-count" class="badge bg-light text-dark ms-1 d-none">0</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Position &times; Page</h4>
                            </div>
                            <div class="card-body">
                                <div class="page-access-wrap">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Position</th>
                                                @foreach ($pages as $pageKey => $label)
                                                    <th>{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($positions as $position)
                                                <tr>
                                                    <th>
                                                        {{ $position }}
                                                        <div class="text-muted font-size-11 fw-normal">
                                                            {{ $totals[$position] ?? 0 }} user(s)
                                                        </div>
                                                    </th>
                                                    @foreach ($pages as $pageKey => $label)
                                                        <td class="page-access-cell" data-position="{{ $position }}" data-page="{{ $pageKey }}">
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input page-access-checkbox"
                                                                data-position="{{ $position }}"
                                                                data-page="{{ $pageKey }}"
                                                                data-initial="{{ $matrix[$position][$pageKey] ? '1' : '0' }}"
                                                                {{ $matrix[$position][$pageKey] ? 'checked' : '' }}
                                                            >
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($pages) + 1 }}" class="text-center text-muted py-4">
                                                        No positions found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="{{ asset('assets/js/page-access.js') }}"></script>
</body>

</html>
