<style>
    .is-invalid {
      border: 1px solid red !important;
    }

    .choices.is-invalid .choices__inner {
      border-color: red !important;
    }
</style>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-4">Recon Call Action Register</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="col-xl-3 col-lg-4 col-sm-6">
                                <a href="#" id="back-button"><i class="bx bx-arrow-back"></i></a>
                            </div>
                        </div>
                    </div>
                    <!-- <h4 class="mb-0">Recon Call Action Register</h4> -->
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Recon Call Date</label>
                        <input type="text" name="reconCallDate" class="form-control datetime-js" id="recon-call-date">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="formrow-firstname-input">LDA Email</label>
                        <input type="text" class="form-control" name="ldaEmail" placeholder="Enter LDA Email" id="lda-email" required disabled>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="choices-single-default" class="form-label font-size-13">Audit Sup Email</label>
                            <select class="form-control choices-js" data-trigger name="auditSupEmail" id="audit-sup-email" placeholder="This is a search placeholder" required>
                                <option value="" disabled selected></option>
                                @foreach($users as $item)
                                    <option value="{{ $item->email }}">{{ $item->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="choices-single-default" class="form-label font-size-13">Client Code</label>
                            <select class="form-control choices-js" data-trigger name="clientCode" id="client-code" placeholder="This is a search placeholder" required>
                                <option value="" disabled selected></option>
                                @foreach($carrier_code_dr as $item)
                                    <option value="{{ $item['client_code'] }}">{{ $item['client_code'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="choices-single-default" class="form-label font-size-13">Carrier Code</label>
                            <select class="form-control choices-js" data-trigger name="carrier-code" id="carrier-code" placeholder="This is a search placeholder" required>
                                <option value="" disabled selected></option>
                                @foreach($carrierCodes as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="choices-single-default" class="form-label font-size-13">Region</label>
                            <select class="form-control choices-js" data-trigger name="region" id="region" placeholder="This is a search placeholder" required>
                                <option value="" disabled selected></option>
                                <option value="AMR">AMR</option>
                                <option value="EMEA">EMEA</option>
                                <option value="APAC">APAC</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="formrow-firstname-input">Action Item Summary</label>
                        <input type="text" class="form-control" name="actionItemSummary" placeholder="Enter Action Item Summary" id="action-item-summary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="formrow-firstname-input">Action Item Details</label>
                        <textarea class="form-control" name="actionItemDetails" id="action-item-detail" placeholder="Enter Action Item Details" rows="2" style="height: 138px;" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="formrow-firstname-input">Jira Link</label>
                        <input type="text" class="form-control" name="jiraLink" placeholder="Enter Jira Link" id="jira-link" required>
                    </div>
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="choices-single-default" class="form-label font-size-13">Status</label>
                            <select class="form-control choices-js" data-trigger name="status" id="status" placeholder="This is a search placeholder" required>
                                <option value="" disabled selected></option>
                                <option value="To Do">To Do</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Pending">Pending</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>

                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" id="submitBtn" class="btn btn-primary  waves-effect waves-light">Submit</button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

