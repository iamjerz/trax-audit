Hi {{ $ldaName }},

A new QA evaluation has been logged for you.

@if($invoiceId)
Invoice #: {{ $invoiceId }}
@endif
Audit ID: {{ $auditId }}

Please review it and acknowledge once you've gone through the results:
{{ $viewUrl }}

This is an automated notification from Audit Ops. Please don't reply to this email.
