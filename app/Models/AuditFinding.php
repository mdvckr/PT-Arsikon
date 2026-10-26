<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'finding_code',
        'source_type',
        'auditable_type',
        'auditable_id',
        'warehouse_id',
        'purchase_order_id',
        'stock_opname_id',
        'title',
        'description',
        'financial_impact',
        'risk_level',
        'pic_id',
        'created_by',
        'due_date',
        'status',
        'corrective_action',
        'resolved_at',
        'last_reminded_at',
        'closed_at',
    ];

    protected $casts = [
        'due_date'         => 'date',
        'resolved_at'      => 'datetime',
        'last_reminded_at' => 'datetime',
        'closed_at'        => 'datetime',
        'financial_impact' => 'decimal:2',
    ];

    public static function generateCode(): string
    {
        $prefix = 'AUD-' . Carbon::now()->format('Ym') . '-';
        $last = self::where('finding_code', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();
        $number = $last ? ((int) substr($last->finding_code, -4)) + 1 : 1;
        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(AuditActionPlan::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'Closed' && $this->due_date && $this->due_date->isPast();
    }
}
