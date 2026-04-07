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
                            <h5 class="card-title">Triad Form</h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="card">
                            <div class="card-header bg-primary border-primary">
                                <h4 class="card-title text-white">TRIAD</h4>
                
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
        url: '/api/triad-ticket',
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


function TriadCoach(){
    const body_language_input = document.getElementById('body-language-input')?.value || '';
    const body_language_score = document.getElementById('body-language-score')?.value || '';

    const clear_mind_input = document.getElementById('clear-mind-input')?.value || '';
    const clear_mind_score = document.getElementById('clear-mind-score')?.value || '';

    const permission_notes_input = document.getElementById('permission-notes-input')?.value || '';
    const permission_notes_score = document.getElementById('permission-notes-score')?.value || '';

    const choices_question_input = document.getElementById('choices-question-input')?.value || '';
    const choices_question_score = document.getElementById('choices-question-score')?.value || '';

    const was_sme_input = document.getElementById('was-sme-input')?.value || '';
    const was_sme_score = document.getElementById('was-sme-score')?.value || '';

    const recap_summary_input = document.getElementById('recap-summary-input')?.value || '';
    const recap_summary_score = document.getElementById('recap-summary-score')?.value || '';

    const sme_adhere_input = document.getElementById('sme-adhere-input')?.value || '';
    const sme_adhere_score = document.getElementById('sme-adhere-score')?.value || '';

    const clearly_defined_input = document.getElementById('clearly-defined-input')?.value || '';
    const clearly_defined_score = document.getElementById('clearly-defined-score')?.value || '';

    const rca_input = document.getElementById('rca-input')?.value || '';
    const rca_score = document.getElementById('rca-score')?.value || '';

    const line_situation_input = document.getElementById('line-situation-input')?.value || '';
    const line_situation_score = document.getElementById('line-situation-score')?.value || '';

    const Triad = {
        body_language: {
            input: body_language_input,
            score: body_language_score
        },
        clear_mind: {
            input: clear_mind_input,
            score: clear_mind_score
        },
        permission_notes: {
            input: permission_notes_input,
            score: permission_notes_score
        },
        choices_question: {
            input: choices_question_input,
            score: choices_question_score
        },
        was_sme: {
            input: was_sme_input,
            score: was_sme_score
        },
        recap_summary: {
            input: recap_summary_input,
            score: recap_summary_score
        },
        sme_adhere: {
            input: sme_adhere_input,
            score: sme_adhere_score
        },
        clearly_defined: {
            input: clearly_defined_input,
            score: clearly_defined_score
        },
        rca: {
            input: rca_input,
            score: rca_score
        },
        line_situation: {
            input: line_situation_input,
            score: line_situation_score
        }
    };
    return Triad
}

$(document).ready(function() {
    $(document).on('click', '#submit', async function() {
        
        const coaching_reference = document.getElementById('coaching-reference')?.value;
        const TriadForm = {
            Reference: coaching_reference,
            Triad: TriadCoach(),
            Origin: "Web"
        }


        fetch('/triad', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(TriadForm)
        })
        .then(res => res.json())
        .then(data => console.log(data))
        .catch(err => console.error(err));
    });
});
</script>


</body>

</html>