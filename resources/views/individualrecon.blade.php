<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.header')
<style>
    .dd-pointer {
    cursor: pointer;important!
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
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h3 class="card-title">Details</h3>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3  text-end">
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#change-button">
                                <i class="bx bx-grid-small font-size-16 align-middle me-2"></i> Options
                            </button>

                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <small class="text-muted">Submission ID</small>
                                        <div class="fw-semibold">
                                            {{ $data->submission_id }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Recon Call Date</small>
                                        <div class="fw-semibold">

                                            {{ \Carbon\Carbon::parse($data->recon_call_date)->format('F d, Y') }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">LDA Email (Primary Owner)</small>
                                        <div class="fw-semibold">
                                            {{ $data->lda_email }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Supervisor</small>
                                        <div class="fw-semibold">
                                            {{ $data->audit_sup_email }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Client Code</small>
                                        <div class="fw-semibold">
                                            {{ $data->client_code }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Carrier Code</small>
                                        <div class="fw-semibold">
                                            {{ $data->carrier_code }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Region</small>
                                        <div class="fw-semibold">
                                            {{ $data->region }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Jira Ticket</small>
                                        <div class="fw-semibold">
                                            {{ $data->jira_ticket }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Secondary Owner</small>
                                        <div class="fw-semibold">
                                            {{ ($assignTo->FirstName ?? 'N/A') . ' ' . ($assignTo->LastName ?? '') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">

                                <div class="d-flex align-items-center justify-content-between border-bottom mb-3 pb-2">
                                    <ul class="nav nav-tabs card-header-tabs border-0" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="comment-tab-btn" data-bs-toggle="tab" data-bs-target="#comment-tab-pane" type="button" role="tab" aria-controls="comment-tab-pane" aria-selected="true">Comment</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="history-tab-btn" data-bs-toggle="tab" data-bs-target="#history-tab-pane" type="button" role="tab" aria-controls="history-tab-pane" aria-selected="false">History</button>
                                        </li>
                                    </ul>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addCommentModal">
                                                <i class="bx bx-comment-dots font-size-16 align-middle me-2"></i> Add a comment
                                            </button>
                                            <div class="modal fade" id="addCommentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="staticBackdropLabel">Add a comment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="text" class="submissionid" id="submissionid" value="{{ $data->submission_id }}" hidden>
                                                            <textarea class="form-control" id="commentText" rows="12"></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" id="saveCommentBtn">Update</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="comment-tab-pane" role="tabpanel" aria-labelledby="comment-tab-btn" tabindex="0">
                                        <div class="comment" id="comment"></div>
                                    </div>
                                    <div class="tab-pane fade" id="history-tab-pane" role="tabpanel" aria-labelledby="history-tab-btn" tabindex="0">
                                        <div class="history" id="history"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-2 text-muted small">Current Status</div>
                                

                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn btn-{{$data->status == 'To Do' ? 'secondary' : 
                                    ($data->status == 'Pending' ? 'warning' : 
                                    ($data->status == 'In Progress' ? 'primary' : 
                                    ($data->status == 'Closed' ? 'success' : 'dark')))
                                    }} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="status" id="status-changed">{{ $data->status }}</span> <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                        <li><a class="dropdown-item dd-pointer">Closed</a></li>
                                        <li><a class="dropdown-item dd-pointer">In Progress</a></li>
                                        <li><a class="dropdown-item dd-pointer">To Do</a></li>
                                        <li><a class="dropdown-item dd-pointer">Pending</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-semibold">Action Item Summary</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSummaryModal">
                                        <i class="bx bx-edit-alt"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-0" id="action-item-summary-text">
                                        {{ $data->action_item_summary }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editSummaryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="editSummaryModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editSummaryModalLabel">Edit Action Item Summary</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <textarea class="form-control" id="editSummaryText" rows="8">{{ $data->action_item_summary }}</textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveSummaryBtn">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-semibold">Action Item Details</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDetailsModal">
                                        <i class="bx bx-edit-alt"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-0" id="action-item-details-text">
                                        {{ $data->action_item_details }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editDetailsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="editDetailsModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editDetailsModalLabel">Edit Action Item Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <textarea class="form-control" id="editDetailsText" rows="8">{{ $data->action_item_details }}</textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveDetailsBtn">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <div class="modal fade" id="change-button" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="choices-single-default" class="form-label">Name <span class="text-danger">*</span></label>
                    <select class="form-control" data-choice name="assign-to" id="assign-to" placeholder="This is a search placeholder">
                        <option value="">Select LDA Name</option>
                        @foreach ($usersData['allusers'] as $alluser)
                        <option value="{{ $alluser->employeeid }}">
                            {{ $alluser->first_name }} {{ $alluser->last_name }}
                        </option>
                        @endforeach

                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-assigned-to">Update</button>
                </div>
            </div>
        </div>
    </div>


    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script id="7g9k3f">
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('[data-choice]');

            elements.forEach(el => {
                new Choices(el);
            });
        });

        $('#saveCommentBtn').click(function() {

            let submission_id = $('#submissionid').val();
            let comments = $('#commentText').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '/recon-ticket-add-comment',
                type: 'POST',
                data: {
                    submission_id: submission_id,
                    comments: comments
                },

                // ✅ FIXED
                beforeSend: function() {
                    $('#addCommentModal').modal('hide');
                    $('#saveCommentBtn').prop('disabled', true).text('Saving...');
                },

                success: function(response) {
                    $('#commentText').val('');

                    // reload comments
                    loadComments();

                    // optional toast
                    console.log('Comment added');
                },

                error: function(xhr) {
                    console.log(xhr.responseText);
                    notifyError('Error saving comment');
                },

                // 👉 always runs
                complete: function() {
                    $('#saveCommentBtn').prop('disabled', false).text('Save');
                }
            });

        });

        $('#saveSummaryBtn').click(function() {

            let id = window.location.pathname.split('/').filter(Boolean).pop();
            let summary = $('#editSummaryText').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '/recon/update-summary/' + id,
                type: 'POST',
                data: {
                    action_item_summary: summary
                },

                beforeSend: function() {
                    $('#saveSummaryBtn').prop('disabled', true).text('Saving...');
                },

                success: function(response) {
                    $('#action-item-summary-text').text(summary);
                    $('#editSummaryModal').modal('hide');
                    loadHistory();
                    notifySuccess('Action Item Summary updated.');
                },

                error: function(xhr) {
                    console.log(xhr.responseText);
                    notifyError('Error saving Action Item Summary');
                },

                complete: function() {
                    $('#saveSummaryBtn').prop('disabled', false).text('Save');
                }
            });

        });

        $('#saveDetailsBtn').click(function() {

            let id = window.location.pathname.split('/').filter(Boolean).pop();
            let details = $('#editDetailsText').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '/recon/update-details/' + id,
                type: 'POST',
                data: {
                    action_item_details: details
                },

                beforeSend: function() {
                    $('#saveDetailsBtn').prop('disabled', true).text('Saving...');
                },

                success: function(response) {
                    $('#action-item-details-text').text(details);
                    $('#editDetailsModal').modal('hide');
                    loadHistory();
                    notifySuccess('Action Item Details updated.');
                },

                error: function(xhr) {
                    console.log(xhr.responseText);
                    notifyError('Error saving Action Item Details');
                },

                complete: function() {
                    $('#saveDetailsBtn').prop('disabled', false).text('Save');
                }
            });

        });

        function loadComments() {
            let id = window.location.pathname.split('/').filter(Boolean).pop();

            // 🔥 show loader before request
            $('#comment').html(`
            <div class="text-center p-3">
                <div class="spinner-border text-primary"></div>
                <div>Loading comments...</div>
            </div>
            `);

            $.ajax({
                url: '/recon-view-comment/' + id,
                type: 'GET',
                success: function(response) {
                    $('#comment').html(response);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        // History tab: assign/status-change events, sourced from audit_trails
        // (see historyList() in ReconTiketController) instead of the comment
        // list, so real comments and system history stay visually separate.
        function loadHistory() {
            let id = window.location.pathname.split('/').filter(Boolean).pop();

            $('#history').html(`
            <div class="text-center p-3">
                <div class="spinner-border text-primary"></div>
                <div>Loading history...</div>
            </div>
            `);

            $.ajax({
                url: '/recon-view-history/' + id,
                type: 'GET',
                success: function(response) {
                    $('#history').html(response);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        $(document).ready(function() {
            loadComments();
            loadHistory();
        });


        $(document).ready(function() {
            $(document).on('click', '#update-assigned-to', async function() {
                try {


                    const pathParts = window.location.pathname.split('/');
                    const id = pathParts[pathParts.length - 1]; // gets 1776678420704

                    const employeeid = document.getElementById('assign-to').value;
                    console.log("EmployeeID::" , employeeid)
                    console.log("Ticket ID: ",id )
                    const res = await fetch(`/recon/assignto/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            assigned_to: employeeid
                        })
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP error! Status: ${res.status}`);
                    }

                    const data = await res.json();
                    if (data.status === 200) {
                        window.location.reload();
                    }

                } catch (err) {
                    console.error("Request failed:", err);
                }
            });
        });

        document.querySelectorAll('.dd-pointer').forEach(function(el) {
            el.addEventListener('click', async function() {

                let ticket_status = this.textContent.trim();

                const statusSpan = document.getElementById('status-changed');
                const button = document.getElementById('btnGroupDrop1');

                // ✅ Update text
                statusSpan.textContent = ticket_status;

                // ✅ SAME mapping as Blade
                const statusColors = {
                    'To Do': 'btn-secondary',
                    'Pending': 'btn-warning',
                    'In Progress': 'btn-primary',
                    'Closed': 'btn-success'
                };

                // ✅ Remove all possible classes
                button.classList.remove(
                    'btn-secondary',
                    'btn-warning',
                    'btn-primary',
                    'btn-success',
                    'btn-dark'
                );

                // ✅ Apply new class
                button.classList.add(statusColors[ticket_status] || 'btn-dark');

                try {
                    const id = window.location.pathname.split('/').filter(Boolean).pop();

                    const res = await fetch(`/recon/status-change/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            status: ticket_status
                        })
                    });

                    if (!res.ok) throw new Error(`HTTP error! ${res.status}`);

                    const data = await res.json();
                    console.log(data);
                    // Status changes are logged to audit_trails now, not as a
                    // comment, so refresh History rather than Comments.
                    loadHistory();
                } catch (err) {
                    console.error("Request failed:", err);
                }

            });
        });
    </script>

</body>

</html>