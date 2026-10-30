<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\DocumentCounter;
use App\Models\GoodsReceipt;
use App\Models\MaterialRequest;
use App\Models\ProcurementRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate next safe document number using document_counters with pessimistic locking (lockForUpdate).
     */
    public static function generate(string $type, ?int $year = null): string
    {
        $year = $year ?? (int) date('Y');

        return DB::transaction(function () use ($type, $year) {
            $counter = DocumentCounter::where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $initialMax = static::resolveInitialMaxNumber($type, $year);
                $counter = DocumentCounter::create([
                    'type'        => $type,
                    'year'        => $year,
                    'last_number' => $initialMax + 1,
                ]);
            } else {
                $counter->last_number += 1;
                $counter->save();
            }

            return static::formatNumber($type, $year, $counter->last_number);
        });
    }

    /**
     * Resolves the maximum legacy sequence number if the counter row does not exist yet.
     */
    protected static function resolveInitialMaxNumber(string $type, int $year): int
    {
        $max = 0;

        switch ($type) {
            case 'PO':
                $numbers = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")
                    ->pluck('po_number');
                foreach ($numbers as $num) {
                    if (preg_match("/PO-{$year}-(\\d+)/", $num, $matches)) {
                        $val = (int) $matches[1];
                        if ($val > $max) {
                            $max = $val;
                        }
                    }
                }
                break;

            case 'PR':
                $numbers = ProcurementRequest::where('pr_number', 'like', "PR-{$year}-%")
                    ->pluck('pr_number');
                foreach ($numbers as $num) {
                    if (preg_match("/PR-{$year}-(\\d+)/", $num, $matches)) {
                        $val = (int) $matches[1];
                        if ($val > $max) {
                            $max = $val;
                        }
                    }
                }
                break;

            case 'MR':
                $numbers = MaterialRequest::where('request_number', 'like', "REQ-{$year}%")
                    ->pluck('request_number');
                foreach ($numbers as $num) {
                    if (preg_match('/REQ-\\d+-(\\d+)/', $num, $matches)) {
                        $val = (int) $matches[1];
                        if ($val > $max) {
                            $max = $val;
                        }
                    }
                }
                break;

            case 'GR':
                $numbers = GoodsReceipt::where('receipt_number', 'like', "GR-{$year}%")
                    ->pluck('receipt_number');
                foreach ($numbers as $num) {
                    if (preg_match('/GR-\\d+-(\\d+)/', $num, $matches)) {
                        $val = (int) $matches[1];
                        if ($val > $max) {
                            $max = $val;
                        }
                    }
                }
                break;

            case 'DST':
                $numbers = Distribution::where('distribution_number', 'like', "DST-{$year}%")
                    ->pluck('distribution_number');
                foreach ($numbers as $num) {
                    if (preg_match('/DST-\\d+-(\\d+)/', $num, $matches)) {
                        $val = (int) $matches[1];
                        if ($val > $max) {
                            $max = $val;
                        }
                    }
                }
                break;

            case 'PKL':
                $numbers = \App\Models\ProjectBalanceRequest::where('request_number', 'like', "PKL-{$year}%")
                    ->pluck('request_number');
                foreach ($numbers as $num) {
                    if (preg_match('/PKL-\\d+-(\\d+)/', $num, $matches)) {
                        $val = (int) $matches[1];
                        if ($val > $max) {
                            $max = $val;
                        }
                    }
                }
                break;
        }

        return $max;
    }

    /**
     * Format number according to document type.
     */
    protected static function formatNumber(string $type, int $year, int $seq): string
    {
        $padded = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        return match ($type) {
            'PO'  => "PO-{$year}-{$padded}",
            'PR'  => "PR-{$year}-{$padded}",
            'MR'  => 'REQ-' . date('Ymd') . "-{$padded}",
            'GR'  => 'GR-' . date('Ymd') . "-{$padded}",
            'DST' => 'DST-' . date('Ymd') . "-{$padded}",
            'PKL' => 'PKL-' . date('Ymd') . "-{$padded}",
            default => "{$type}-{$year}-{$padded}",
        };
    }
}
