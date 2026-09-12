<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PrintTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'width_px',
        'height_px',
        'used_for_pr',
        'used_for_po',
        'padding_top_mm',
        'padding_left_mm',
        'padding_right_mm',
        'padding_bottom_mm',
        'created_by',
    ];

    protected $casts = [
        'used_for_pr'       => 'boolean',
        'used_for_po'       => 'boolean',
        'padding_top_mm'    => 'float',
        'padding_left_mm'   => 'float',
        'padding_right_mm'  => 'float',
        'padding_bottom_mm' => 'float',
        'file_size'         => 'integer',
        'width_px'          => 'integer',
        'height_px'         => 'integer',
    ];

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    /**
     * Get the public URL of the template image.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Quality label based on width resolution.
     */
    public function getQualityLabelAttribute(): string
    {
        if ($this->width_px >= 2000) return 'Tinggi (300 DPI)';
        if ($this->width_px >= 794)  return 'Sedang (96 DPI)';
        return 'Rendah';
    }

    public function getQualityColorAttribute(): string
    {
        if ($this->width_px >= 2000) return 'success';
        if ($this->width_px >= 794)  return 'warning';
        return 'danger';
    }

    // ──────────────────────────────────────────
    // Static Helpers
    // ──────────────────────────────────────────

    /**
     * Get the currently active template for Purchase Requests.
     */
    public static function activeForPR(): ?self
    {
        return static::where('used_for_pr', true)->latest()->first();
    }

    /**
     * Get the currently active template for Purchase Orders.
     */
    public static function activeForPO(): ?self
    {
        return static::where('used_for_po', true)->latest()->first();
    }
}
