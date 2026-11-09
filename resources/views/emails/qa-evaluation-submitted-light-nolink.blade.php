<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222;">
    <p>Hi {{ $ldaName }},</p>

    <p>A new QA evaluation has been logged for you.</p>

    <p>
        @if($invoiceId)
            <strong>Invoice #:</strong> {{ $invoiceId }}<br>
        @endif
        <strong>Audit ID:</strong> {{ $auditId }}
    </p>

    <p>
        Please log into the Audit Ops system to review it and acknowledge once you've gone through the results.
    </p>

    <p style="color: #777; font-size: 12px;">
        This is an automated notification from Audit Ops. Please don't reply to this email.
    </p>
</body>
</html>
