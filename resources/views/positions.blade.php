<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.header')

<style>
    .positions-wrap {
        max-height: 72vh;
        overflow: auto;
        border: 1px solid #eff2f7;
    }

    .positions-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #fff;
        border-bottom: 2px solid #eff2f7 !important;
    }

    .position-row.row-dirty > th,
    .position-row.row-dirty > td {
        background-color: #fff3cd !important;
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
                            <h5 class="card-title mb-1">Positions</h5>
                            <p class="text-muted mb-0 font-size-13">
                                The canonical list of Positions, and how much of each ticket list they see:
                                <strong>Own</strong> (just their own records), <strong>Team</strong> (their own plus
                                anyone whose supervisor is them), or <strong>All</strong> (unrestricted). This list also
                                feeds the Position dropdown on Edit User and Add New User, so it's the one place to
                                add a new position going forward.
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
                            <button type="button" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                                <i class="bx bx-plus font-size-16 align-middle me-1"></i> Add Position
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Position &times; Level</h4>
                            </div>
                            <div class="card-body">
                                <div class="positions-wrap">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th style="min-width:240px;">Position</th>
                                                <th style="min-width:160px;">Level</th>
                                                <th style="min-width:80px;">Users</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($positions as $position)
                                                <tr class="position-row" data-id="{{ $position->id }}">
                                                    <th class="fw-normal">{{ $position->name }}</th>
                                                    <td>
                                                        <select class="form-select form-select-sm position-scope-select"
                                                            data-id="{{ $position->id }}"
                                                            data-initial="{{ $position->scope }}">
                                                            <option value="own"  {{ $position->scope === 'own'  ? 'selected' : '' }}>Own</option>
                                                            <option value="team" {{ $position->scope === 'team' ? 'selected' : '' }}>Team</option>
                                                            <option value="all"  {{ $position->scope === 'all'  ? 'selected' : '' }}>All</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-muted">
                                                        {{ $totals[$position->name] ?? 0 }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">No positions found.</td>
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

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Position name</label>
                        <input type="text" class="form-control" id="new-position-name" placeholder="e.g. Team Lead">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select class="form-select" id="new-position-scope">
                            <option value="own">Own — only their own records</option>
                            <option value="team">Team — their own plus their direct reports'</option>
                            <option value="all" selected>All — unrestricted</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="add-position-btn">Add Position</button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.script')
    <script src="{{ asset('assets/js/positions.js') }}"></script>
</body>

</html>
