<?php

namespace App\Mail;

use App\Models\AuditFinding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditDeadlineReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AuditFinding $finding,
        public string $urgencyType // 'UPCOMING' atau 'OVERDUE'
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->urgencyType === 'OVERDUE' ? '[OVERDUE / TERLAMBAT]' : '[REMINDER H-3]';
        return new Envelope(
            subject: "{$prefix} Tindak Lanjut Temuan Audit #{$this->finding->finding_code} - PT Arsikon",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.audit_reminder',
        );
    }
}
