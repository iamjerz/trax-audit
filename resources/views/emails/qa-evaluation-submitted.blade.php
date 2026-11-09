<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family: Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:480px; background-color:#ffffff; border-radius:6px; overflow:hidden;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background-color:#556ee6; padding:20px 28px;">
                            <span style="color:#ffffff; font-size:16px; font-weight:600;">Audit Ops</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px; font-size:15px; color:#2a2e3e;">Hi {{ $ldaName }},</p>

                            <p style="margin:0 0 16px; font-size:15px; color:#2a2e3e; line-height:1.5;">
                                A new QA evaluation has been logged for you.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; font-size:14px; color:#555;">
                                @if($invoiceId)
                                <tr>
                                    <td style="padding:4px 0; width:120px;"><strong>Invoice #</strong></td>
                                    <td style="padding:4px 0;">{{ $invoiceId }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:4px 0;"><strong>Audit ID</strong></td>
                                    <td style="padding:4px 0;">{{ $auditId }}</td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; font-size:15px; color:#2a2e3e; line-height:1.5;">
                                Please review it and acknowledge once you've gone through the results.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:4px; background-color:#556ee6;">
                                        <a href="{{ $viewUrl }}"
                                           style="display:inline-block; padding:10px 20px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">
                                            View Evaluation
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px; background-color:#f4f5f7;">
                            <p style="margin:0; font-size:12px; color:#999;">
                                This is an automated notification from Audit Ops. Please don't reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
