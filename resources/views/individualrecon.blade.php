<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Details</h5>
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
                                            {{ $data->recon_call_date }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">LDA Email</small>
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
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                
                                <div class="d-flex align-items-start border-bottom">
                                    <div class="flex-grow-1">
                                        <h5 class="font-size-14 mb-3 pb-3">Comments</h5>
                                    </div>
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
                               
                                @forelse($comment as $c)
                                    
                                    <div class="border-bottom mt-3 pb-3">
                                        <div class="mb-2">
                                            <p class="float-sm-end text-muted font-size-13">{{ $c->created_at }}</p>
                                            <h5 class="font-size-16 mb-0">{{ $c->employee_first_name }} {{ $c->employee_last_name }}</h5>  
                                        </div> 
                                        
                                        <p class="text-muted mb-4">{{ $c->comments }}</p>
                                    </div>
                                @empty
                                    <p>No comments found.</p>
                                @endforelse
                                
                                
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-2 text-muted small">Current Status</div>
                                    <span class="badge px-4 py-2 fs-6 
                                        bg-{{ 
                                            $data->status == 'To Do' ? 'secondary' : 
                                            ($data->status == 'Pending' ? 'warning' : 
                                            ($data->status == 'In Progress' ? 'info' : 
                                            ($data->status == 'Closed' ? 'success' : 'dark')))
                                        }}">
                                        {{ $data->status }}
                                    </span>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="card-header bg-white border-0">
                                    <h6 class="mb-0 fw-semibold">Action Item Summary</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-0">
                                        {{ $data->action_item_summary }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="card-header bg-white border-0">
                                    <h6 class="mb-0 fw-semibold">Action Item Details</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-0">
                                        {{ $data->action_item_details }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script id="7g9k3f">
    $('#saveCommentBtn').click(function () {

        let submission_id = $('#submissionid').val();
        let comments = $('#commentText').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '/recon-ticket-add-comment', // your API endpoint
            type: 'POST',
            data: {
                submission_id: submission_id,
                comments: comments
            },
            success: function (response) {

                // ✅ Close modal
                $('#addCommentModal').modal('hide');

                // ✅ Clear textarea
                $('#commentText').val('');

                // ✅ Optional: show success
                alert('Comment added!');

                // ✅ Optional: reload comments section
                location.reload(); // or use AJAX to refresh list
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Error saving comment');
            }
        });

    });
    </script>

</body>

</html>