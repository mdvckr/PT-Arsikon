<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyAuditReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_period',
        'total_findings',
        'resolved_findings',
        'pending_findings',
        'overdue_findings',
        'total_financial_impact',
        'summary_payload',
        'generated_at',
    ];

    protected $casts = [
        'summary_payload'        => 'array',
        'generated_at'           => 'datetime',
        'total_financial_impact' => 'decimal:2',
    ];
}
