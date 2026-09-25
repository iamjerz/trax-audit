<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- gridjs css -->
<link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- flatpickr css -->
<!-- quill css -->
<link href="assets/libs/quill/quill.core.css" rel="stylesheet" type="text/css" />
<link href="assets/libs/quill/quill.bubble.css" rel="stylesheet" type="text/css" />
<link href="assets/libs/quill/quill.snow.css" rel="stylesheet" type="text/css" />

<link href="assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css">
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
                <div class="page-title-box">
                    <h4 class="mb-2 font-size-18">Feedback/Feature Request</h4>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3" hidden>
                            <label for="formrow-firstname-input" class="form-label">User Jira ID</label>
                            <input type="text" class="form-control" placeholder="User Jira ID" id="user-jira-id" value="{{ $users[0]['accountId'] }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="formrow-firstname-input" class="form-label">Summary</label>
                            <input type="text" class="form-control" placeholder="Summary" id="summary">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Application</label>
                            <select class="form-control dropdown-choices" data-trigger id="application" placeholder="This is a search placeholder">
                                <option value="Chrome-Extension">Chrome Extension</option>
                                <option value="Web-App">Web App</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Category</label>
                            <select class="form-control dropdown-choices" data-trigger id="category" placeholder="This is a search placeholder">
                                <option value="QA-Monitoring">QA Monitoring</option>
                                <option value="Coaching">Coaching</option>
                                <option value="Triad">Triad</option>
                                <option value="Recon-Call-Register">Recon Call Register</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="formrow-firstname-input" class="form-label">Description</label>
                            <div id="snow-editor" style="height: 500px;">

                            </div>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="send-req">
                                <i class="bx bx-send font-size-16 align-middle me-2"></i> Send
                            </button>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('partials.script')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- gridjs js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- flatpickr js -->
    <script src="assets/libs/flatpickr/flatpickr.min.js"></script>
    <!-- quill js -->
    <script src="assets/libs/quill/quill.js"></script>
</body>

<script>
    console.log('JIRA JS LOADED');

    var snowQuill = new Quill("#snow-editor", {
        theme: "snow",
        modules: {
            toolbar: [
                [{
                    font: []
                }, {
                    size: []
                }],
                ["bold", "italic", "underline", "strike"],
                [{
                    color: []
                }, {
                    background: []
                }],
                [{
                    script: "super"
                }, {
                    script: "sub"
                }],
                [{
                        header: [false, 1, 2, 3, 4, 5, 6]
                    },
                    "blockquote",
                    "code-block"
                ],
                [{
                        list: "ordered"
                    },
                    {
                        list: "bullet"
                    },
                    {
                        indent: "-1"
                    },
                    {
                        indent: "+1"
                    }
                ],
                ["direction", {
                    align: []
                }],
                ["link", "image", "video"],
                ["clean"]
            ]
        }
    });


    document.addEventListener('DOMContentLoaded', function() {

        const sendButton = document.getElementById('send-req');

        console.log('Button:', sendButton);

        if (!sendButton) {
            console.error('SEND BUTTON NOT FOUND!');
            return;
        }

        sendButton.addEventListener('click', function() {

            const summaryValue = document.getElementById('summary').value;
            const userIdValue = document.getElementById('user-jira-id').value;
            const applicationValue = document.getElementById('application').value;
            const categoryValue = document.getElementById('category').value;
            const descriptionValue = snowQuill.root.innerHTML;

            $.ajax({
                url: '/api/jira/issues',
                type: 'POST',

                data: {
                    summary: summaryValue,
                    description: descriptionValue,
                    reporter: userIdValue,
                    priority: 10003,
                    application: applicationValue,
                    category: categoryValue
                },

                beforeSend: function() {
                    console.log('Creating Jira ticket...');
                },

                success: function(response) {
                    notifySuccess('Request Send to Audit Ops Team.', () => location.reload());
                },

                error: function(xhr) {
                    console.error('Jira error:', xhr.responseText);
                }
            });

        });

    });

    document.addEventListener("DOMContentLoaded", function () {

        var e = document.querySelectorAll("[data-trigger]");

        for (var i = 0; i < e.length; ++i) {

            var a = e[i];

            new Choices(a, {
                placeholderValue: "Select",
                searchPlaceholderValue: "This is a search placeholder",
                shouldSort: false
            });

        }

    });
</script>

</html>