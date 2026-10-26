<?php

namespace App\Mail;

use App\Models\AuditFinding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditFindingAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AuditFinding $finding
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[AUDIT ASSIGNED] Penugasan Temuan Audit #{$this->finding->finding_code} - PT Arsikon",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.audit_assigned',
        );
    }
}
