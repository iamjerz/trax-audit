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
                            <h5 class="card-title">Coaching Form</h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="card">
                            <div class="card-header bg-primary border-primary">
                                <h4 class="card-title text-white">Coaching</h4>
                
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 col-md-6">
                                        <input type="hidden" id="audit-by" name="audit-by" value="{{ auth()->user()->employeeid }}">
                                        <div class="mb-3">
                                            <label for="choices-single-default" class="form-label">LDA Name <span class="text-danger">*</span></label>
                                            <select class="form-control" data-trigger name="lda-name" id="lda-name" placeholder="This is a search placeholder">
                                                <option value="">Select LDA Name</option>
                                                @foreach ($allusers as $alluser)
                                                    <option value="{{ $alluser->employeeid }}">
                                                        {{ $alluser->first_name }} {{ $alluser->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-6">
                                        <input type="hidden" id="audit-by" name="audit-by" value="{{ auth()->user()->employeeid }}">
                                        <div class="mb-3">
                                            <label for="choices-single-default" class="form-label">Coaching Reference <span class="text-danger">*</span></label>
                                            <select class="form-control" data-trigger name="coaching-reference" id="coaching-reference" placeholder="This is a search placeholder">
                                                <option value="">Select Coaching Reference</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>

                            </div>
                            
                        </div>
                        <div class="" id="ticket-body"></div>
                    </div>

                </div>
            

            </div>
        </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
const elements = document.querySelectorAll("[data-trigger]");
const choicesMap = {}; // store Choices instances by select ID

// Init all selects
elements.forEach(el => {

    const instance = new Choices(el, {
        searchEnabled: true,
        shouldSort: false,
        placeholder: true,
        itemSelectText: '',
    });

    // store instance
    choicesMap[el.id] = instance;

    el.addEventListener("change", function () {

        const data = {
            value: this.value,
            label: this.options[this.selectedIndex]?.text || "",
            name: this.name,
            id: this.id
        };

        console.log("CHANGE:", data);

        // When LDA changes → update coaching reference
        if (data.name === "lda-name") {

            const coachingChoices = choicesMap['coaching-reference'];

            // 🔹 Clear selected value
            coachingChoices.removeActiveItems();

            // 🔹 Clear options
            coachingChoices.clearChoices();

            // 🔹 Disable while loading
            coachingChoices.disable();

            // 🔹 Loading placeholder
            coachingChoices.setChoices([
                { value: '', label: 'Loading coaching references...', disabled: true }
            ], 'value', 'label', true);

            // 🔹 Stop if empty
            if (!data.value) {
                coachingChoices.clearChoices();
                coachingChoices.setChoices([
                    { value: '', label: 'Select Coaching Reference', disabled: true }
                ], 'value', 'label', true);
                coachingChoices.enable();
                return;
            }

            appendCoachingReference(data.value);
        }else if (data.name === "coaching-reference") {
            console.log("DATA DATA", data)
            appendTicketInformation(data.value)
        }

    });

});


// ================= API LOADER =================

function appendCoachingReference(ldaId) {

    fetch(`/api/coaching-triad?id=${ldaId}`)
        .then(res => res.json())
        .then(response => {

            console.log("RAW API:", response);

            // normalize API response
            const list = response.list || response.data || response.results || [];

            const items = list.map(u => ({
                value: u.audit_id,   // from your JSON
                label: u.audit_id
            }));

            console.log("NORMALIZED:", items);

            const coachingChoices = choicesMap['coaching-reference'];

            coachingChoices.clearChoices();

            if (items.length === 0) {
                coachingChoices.setChoices([
                    { value: '', label: 'No coaching references found', disabled: true }
                ], 'value', 'label', true);
            } else {
                coachingChoices.setChoices(items, 'value', 'label', true);

                // ✅ optional auto-select first item
                // coachingChoices.setChoiceByValue(items[0].value);
            }

            coachingChoices.enable();
        })
        .catch(err => {
            console.error('API error:', err);

            const coachingChoices = choicesMap['coaching-reference'];
            coachingChoices.clearChoices();
            coachingChoices.setChoices([
                { value: '', label: 'Failed to load data', disabled: true }
            ], 'value', 'label', true);
            coachingChoices.enable();
        });
}


function appendTicketInformation(ticketid) {
    $.ajax({
        url: '/api/coaching-ticket',
        method: 'GET',
        data: { id: ticketid },
        beforeSend: function() {
            $('#ticket-body').html('Loading...');
        },
        success: function(response) {
            $('#ticket-body').html(response);
            choicesInit(".append-ticket")
        },
        error: function(xhr) {
            console.error('Error:', xhr.status, xhr.responseText);
        }
    });
}

function choicesInit(className) {
    const elements = document.querySelectorAll(className);

    elements.forEach(function(element) {
        new Choices(element, {
            searchPlaceholderValue: "This is a search placeholder"
        });
    });
}

function SmartCoaching(){
    const specific = document.getElementById('specific').value;
    const measurable = document.getElementById('measurable').value;
    const achievable = document.getElementById('achievable').value;
    const relevant = document.getElementById('relevant').value;
    const time_bound = document.getElementById('time-bound').value;

    const Smart = {
        specific: specific,
        measurable: measurable,
        achievable: achievable,
        relevant: relevant,
        time_bound: time_bound
    };

    return Smart
}

function GrowCoaching(){
    const grow_input = document.getElementById('grow-input')?.value || '';
    const grow_comments = document.getElementById('grow-comments')?.value || '';

    const reality_input = document.getElementById('reality-input')?.value || '';
    const reality_comments = document.getElementById('reality-comments')?.value || '';
    
    const option_input = document.getElementById('option-input')?.value || '';
    const option_comments = document.getElementById('option-comments')?.value || '';

    const wayforward_input = document.getElementById('wayforward-input')?.value || '';
    const wayforward_comments = document.getElementById('wayforward-comments')?.value || '';

    const SmartPlan = {
        grow: {
            input: grow_input,
            comments: grow_comments
        },
        reality: {
            input: reality_input,
            comments: reality_comments
        },
        option: {
            input: option_input,
            comments: option_comments
        },
        wayforward: {
            input: wayforward_input,
            comments: wayforward_comments
        }
    };
    return SmartPlan
}


$(document).ready(function() {
    $(document).on('click', '#submit-coaching', async function() {
        
        const coaching_reference = document.getElementById('coaching-reference')?.value || '';
        const CoachingForm = {
            Reference: coaching_reference,
            Smart: SmartCoaching(),
            Grow: GrowCoaching(),
            Origin: "Web"
        }


        fetch('/coaching', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(CoachingForm)
        })
        .then(res => res.json())
        .then(data => console.log(data))
        .catch(err => console.error(err));
    });
});

</script>


</body>

</html>