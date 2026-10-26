<?php

namespace App\Mail;

use App\Models\MonthlyAuditReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class MonthlyAuditSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MonthlyAuditReport $report,
        public Collection $unresolvedFindings
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Laporan Bulanan Otomatis] Evaluasi Audit Logistik & Pengadaan Periode {$this->report->report_period}",
        );
    }

    public function content(): Content
    {
        $summaryData = [
            'total_findings'      => $this->report->total_findings,
            'closed_findings'     => $this->report->resolved_findings,
            'resolution_rate'     => $this->report->total_findings > 0 ? round(($this->report->resolved_findings / $this->report->total_findings) * 100, 1) : 100,
            'open_exposure'       => $this->report->total_financial_impact,
            'by_source'           => collect($this->report->summary_payload ?? [])->map(function ($val, $key) {
                return (object) [
                    'source_type' => $key,
                    'total'       => is_array($val) ? ($val['count'] ?? 0) : $val,
                    'impact'      => is_array($val) ? ($val['financial_impact'] ?? 0) : 0,
                ];
            }),
            'critical_unresolved' => $this->unresolvedFindings->whereIn('risk_level', ['High', 'Critical'])->take(10),
        ];

        return new Content(
            view: 'emails.monthly_audit_summary',
            with: [
                'summaryData' => $summaryData,
                'periodLabel' => $this->report->report_period,
            ]
        );
    }
}
