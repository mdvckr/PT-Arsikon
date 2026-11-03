<?php

namespace App\Imports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PurchaseOrderItemImport implements ToArray, WithHeadingRow
{
    protected array $parsedItems = [];

    public function array(array $array): void
    {
        $this->parsedItems = $this->parseRows($array);
    }

    public function getParsedItems(): array
    {
        return $this->parsedItems;
    }

    /**
     * Parse raw rows from Excel into structured PO items
     */
    public function parseRows(array $rows): array
    {
        $results = [];

        foreach ($rows as $row) {
            $name = $this->extractField($row, [
                'nama_barang_sku', 'nama_barang', 'nama_material', 'item_name', 'nama', 'barang', 'material'
            ]);

            if (empty($name) || trim($name) === '') {
                continue;
            }

            $rawQty = $this->extractField($row, [
                'jumlah_qty', 'jumlah', 'qty', 'quantity', 'kuantitas'
            ]);
            $qty = $this->cleanNumber($rawQty);

            if ($qty <= 0) {
                continue;
            }

            $unit = $this->extractField($row, [
                'satuan', 'unit', 'sat'
            ]);

            $rawPrice = $this->extractField($row, [
                'harga_satuan_rp', 'harga_satuan', 'harga', 'unit_price', 'price'
            ]);
            $price = $this->cleanNumber($rawPrice);

            $notes = $this->extractField($row, [
                'catatan_keterangan', 'catatan', 'keterangan', 'notes', 'spesifikasi', 'deskripsi'
            ]);

            // Attempt to match in database materials catalog
            $trimmedName = trim($name);
            $material = Material::where('sku', $trimmedName)
                ->orWhere('name', $trimmedName)
                ->first();

            if (!$material) {
                // Secondary fallback search case-insensitive substring
                $material = Material::where('name', 'like', $trimmedName)->first();
            }

            if ($material) {
                $matUnit = $material->unit?->code ?? $material->unit?->name ?? 'unit';
                $finalUnit = !empty($unit) ? trim($unit) : $matUnit;
                $finalPrice = ($price > 0) ? $price : (float) ($material->unit_price ?? 0);

                $results[] = [
                    'material_id' => $material->id,
                    'name'        => $material->name,
                    'quantity'    => $qty,
                    'unit'        => strtolower($finalUnit),
                    'unit_price'  => $finalPrice,
                    'subtotal'    => $qty * $finalPrice,
                    'notes'       => !empty($notes) ? trim($notes) : null,
                    'is_custom'   => false,
                ];
            } else {
                $finalUnit = !empty($unit) ? trim($unit) : 'unit';
                $results[] = [
                    'material_id' => null,
                    'name'        => $trimmedName,
                    'quantity'    => $qty,
                    'unit'        => strtolower($finalUnit),
                    'unit_price'  => $price,
                    'subtotal'    => $qty * $price,
                    'notes'       => !empty($notes) ? trim($notes) : null,
                    'is_custom'   => true,
                ];
            }
        }

        return $results;
    }

    /**
     * Extract field by flexible header variations
     */
    protected function extractField(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return (string) $row[$key];
            }
        }

        // Check if any normalized key matches
        $rowNormalized = [];
        foreach ($row as $k => $v) {
            $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower((string)$k));
            $rowNormalized[$cleanKey] = $v;
        }

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
            if (isset($rowNormalized[$cleanKey]) && $rowNormalized[$cleanKey] !== '') {
                return (string) $rowNormalized[$cleanKey];
            }
        }

        return null;
    }

    /**
     * Clean and parse numeric strings (handling currency, commas, decimals)
     */
    protected function cleanNumber(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }

        if (empty($val)) {
            return 0.0;
        }

        $str = (string) $val;
        // Strip Rp, spaces, and currency symbols
        $str = preg_replace('/[^\d.,]/', '', $str);

        // Handle Indonesian thousand separator (dots) vs comma decimals
        if (str_contains($str, '.') && str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, '.') && !str_contains($str, ',')) {
            // Check if it's thousands separator like "50.000" (3 digits after dot)
            if (preg_match('/\.\d{3}$/', $str)) {
                $str = str_replace('.', '', $str);
            }
        } elseif (str_contains($str, ',')) {
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }
}

