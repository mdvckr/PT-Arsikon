<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyAuditExecutiveSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $summaryData,
        public string $periodLabel
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Laporan Otomatis Bulanan: Evaluasi Logistik, PO & Audit - Periode {$this->periodLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly_audit_summary',
        );
    }
}
