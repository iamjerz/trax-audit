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
                    <h4 class="mb-0">Audit Ops Forms</h4>
                    <hr>
                    <ul class="list-group list-group-flush">
                        @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'action_register'))
                            <button type="button" id="recon-form" class="list-group-item list-group-item-action"><i class="bx bx-caret-right"></i> Recon Call Action Register</button>
                        @endif
                        
                        @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'monitoring'))
                            <button type="button" id="qa-form"class="list-group-item list-group-item-action"><i class="bx bx-caret-right"></i> QA Monitoring</button>
                        @endif
                        
                        @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'coaching'))
                            <button type="button" id="coaching-form" class="list-group-item list-group-item-action"><i class="bx bx-caret-right"></i> Coaching</button>
                        @endif

                        @if($access->contains('access_type', 'admin') || $access->contains('access_type', 'triad'))
                            <button type="button" id="triad-form" class="list-group-item list-group-item-action"><i class="bx bx-caret-right"></i> Triad</button>
                        @endif
                    </ul>
                </div>

            </div>
            
        </div>
    </div>
</div>

