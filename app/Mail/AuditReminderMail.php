<?php

namespace App\Mail;

use App\Models\AuditFinding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AuditFinding $finding,
        public string $notificationType = 'REMINDER_H3' // NEW_FINDING, REMINDER_H3, OVERDUE_ALERT
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->notificationType) {
            'NEW_FINDING'   => '[Temuan Audit Baru]',
            'OVERDUE_ALERT' => '[URGENT - OVERDUE]',
            default         => '[Reminder H-3]',
        };

        return new Envelope(
            subject: "{$prefix} Tindak Lanjut Audit #{$this->finding->finding_code} - PT Arsikon",
        );
    }

    public function content(): Content
    {
        // Re-uses the polished audit_reminder view or audit_assigned view
        $view = ($this->notificationType === 'NEW_FINDING') ? 'emails.audit_assigned' : 'emails.audit_reminder';

        return new Content(
            view: $view,
            with: [
                'urgencyType' => ($this->notificationType === 'OVERDUE_ALERT') ? 'OVERDUE' : 'UPCOMING',
            ]
        );
    }
}
