<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-4" id="extension-title-page"></h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="col-xl-3 col-lg-4 col-sm-6">
                                <a href="#" id="back-button"><i class="bx bx-arrow-back"></i></a>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="mb-3">
                                <label for="choices-single-default" class="form-label">Employee Name <span class="text-danger">*</span></label>
                                <select class="form-control choices-js" data-trigger name="lda-name" id="lda-name" placeholder="This is a search placeholder">
                                    <option value="">Select Employee Name</option>

                                    @foreach ($allusers as $alluser)
                                    <option value="{{ $alluser->employeeid }}">
                                        {{ $alluser->first_name }} {{ $alluser->last_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="mb-3">
                                <label for="choices-single-default" class="form-label">Reference <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="coaching-reference" id="coaching-reference" placeholder="Reference">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6" id="coaching-type-field">
                            <div class="mb-3">
                                <label for="choices-single-default" class="form-label">Coaching Type <span class="text-danger">*</span></label>
                                <select class="form-control choices-js" data-trigger name="coaching-type" id="coaching-type" placeholder="This is a search placeholder">
                                    <option value="">Select Coaching Type</option>
                                    <option value="Spot Check">Spot Check</option>
                                    <option value="Calibration Follow up">Calibration Follow up</option>
                                    <option value="Performance">Performance</option>
                                    
                                </select>
                            </div>
                        </div>
                        
                    </div>




                </div>
            </div>
            <div class="forms"></div>
        </div>

    </div>
</div>
                