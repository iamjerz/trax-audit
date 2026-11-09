<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the audited LDA once a QA Monitoring form submission
 * (POST /api/qa-form) has been committed successfully. Queued (not sent
 * inline) so a slow/unreachable mail provider never delays or fails the
 * Chrome extension's form-submit response -- see AuditController::store().
 */
class QaEvaluationSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ldaName,
        public string $auditId,
        public ?string $invoiceId,
        public string $viewUrl,
    ) {}

    public function envelope(): Envelope
    {
        $subjectId = $this->invoiceId ?: $this->auditId;

        return new Envelope(
            subject: "New QA Evaluation Logged - Invoice {$subjectId}",
        );
    }

    public function content(): Content
    {
        // Deliverability history (sending via Gmail SMTP, an unaffiliated
        // personal account, to a corporate M365 recipient domain):
        //   - Fully-styled HTML (colored banner + button CTA): filtered.
        //   - Light HTML (paragraphs/bold only, no banner/button) + a
        //     plain-text multipart alternative: ALSO filtered -- same
        //     plain-text part as the version below, only difference was an
        //     HTML part being present at all. So the trigger here isn't
        //     "how styled," it's the presence of a text/html part at all.
        //   - Plain text only (no HTML part, no multipart), including with
        //     the link present as a literal URL: delivered, twice.
        //
        // CONFIRMED (4 rounds of testing, sending via Gmail SMTP from an
        // unaffiliated personal account to a corporate M365 recipient):
        //   - Fully-styled HTML w/ a button link: filtered.
        //   - Plain text, no link: delivered.
        //   - Plain text, URL present as literal text (not clickable):
        //     delivered.
        //   - Light HTML w/ a clickable <a href> link (+ plain-text
        //     multipart alt that itself had the link as literal text):
        //     filtered.
        //   - Light HTML w/ NO link anywhere, in either part: delivered.
        //
        // So the trigger was never "HTML" or "a URL string in the
        // message" -- it's specifically a clickable <a href> anchor (Safe
        // Links-style scanners crawl/reputation-check actual hyperlinks,
        // not plain text). This is the settled, working config: light
        // design, no clickable link, LDA logs into Audit Ops directly
        // rather than clicking through. If sending ever moves to a
        // domain-authenticated source (verified traxtech.com on Resend, or
        // Graph as a real internal mailbox), a clickable link should be
        // safe to reintroduce -- swap `view`/`text` back to
        // emails.qa-evaluation-submitted-light / -plain (has the link) or
        // emails.qa-evaluation-submitted (full original design).
        return new Content(
            view: 'emails.qa-evaluation-submitted-light-nolink',
            text: 'emails.qa-evaluation-submitted-plain-nolink',
        );
    }
}
