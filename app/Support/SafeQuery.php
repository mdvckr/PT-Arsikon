<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

/**
 * Utility helper untuk eksekusi query Eloquent/Query Builder yang aman dari SQL Injection
 * dan Denial of Service (DoS) di level database.
 */
class SafeQuery
{
    /**
     * Menerapkan sorting dinamis secara aman dengan Whitelist ketat.
     * Mencegah "Order By SQL Injection" dan validasi arah sorting.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param Request|array|null $request
     * @param array<string, string> $allowedColumns Map alias => nama kolom database sesungguhnya
     * @param string $defaultKey Kunci kolom default
     * @param string $defaultDirection Arah default ('asc' atau 'desc')
     */
    public static function sort(
        EloquentBuilder|QueryBuilder $query,
        Request|array|null $request,
        array $allowedColumns,
        string $defaultKey = 'created_at',
        string $defaultDirection = 'desc'
    ): EloquentBuilder|QueryBuilder {
        $sortKey = is_array($request)
            ? ($request['sort_by'] ?? null)
            : ($request instanceof Request ? $request->input('sort_by') : null);

        $sortDirection = is_array($request)
            ? ($request['sort_direction'] ?? null)
            : ($request instanceof Request ? $request->input('sort_direction') : null);

        // 1. Validasi Kolom Menggunakan Strict Whitelisting
        if ($sortKey && array_key_exists($sortKey, $allowedColumns)) {
            $column = $allowedColumns[$sortKey];
        } else {
            $column = $allowedColumns[$defaultKey] ?? array_values($allowedColumns)[0] ?? 'id';
        }

        // 2. Sanitasi Arah Sort (Hanya izinkan 'asc' atau 'desc')
        $direction = strtolower((string) $sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction);
    }

    /**
     * Menerapkan pencarian teks aman dengan parameter binding dan pembatasan panjang input
     * guna menghindari ReDoS atau buffer flood.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param string|null $term Kata kunci pencarian
     * @param array<string> $columns Kolom-kolom yang dicari
     * @param int $maxLength Maksimal panjang string pencarian
     */
    public static function search(
        EloquentBuilder|QueryBuilder $query,
        ?string $term,
        array $columns,
        int $maxLength = 100
    ): EloquentBuilder|QueryBuilder {
        if ($term === null) {
            return $query;
        }

        // 1. Sanitasi dan Truncate input string
        $cleanedTerm = trim(strip_tags($term));
        if ($cleanedTerm === '') {
            return $query;
        }

        if (mb_strlen($cleanedTerm) > $maxLength) {
            $cleanedTerm = mb_substr($cleanedTerm, 0, $maxLength);
        }

        // 2. Escape karakter wildcard LIKE (% dan _) untuk mencegah wildcard expansion DoS
        $escapedTerm = self::escapeLike($cleanedTerm);
        $likePattern = '%' . $escapedTerm . '%';

        // 3. Kelompokkan klausa OR di dalam satu closure WHERE terisolasi
        return $query->where(function ($subQuery) use ($columns, $likePattern) {
            foreach ($columns as $index => $column) {
                // Mendukung relasi bertingkat (contoh: 'category.name')
                if (str_contains($column, '.')) {
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    if ($index === 0) {
                        $subQuery->whereHas($relation, function ($relQuery) use ($relationColumn, $likePattern) {
                            $relQuery->where($relationColumn, 'LIKE', $likePattern);
                        });
                    } else {
                        $subQuery->orWhereHas($relation, function ($relQuery) use ($relationColumn, $likePattern) {
                            $relQuery->where($relationColumn, 'LIKE', $likePattern);
                        });
                    }
                } else {
                    if ($index === 0) {
                        $subQuery->where($column, 'LIKE', $likePattern);
                    } else {
                        $subQuery->orWhere($column, 'LIKE', $likePattern);
                    }
                }
            }
        });
    }

    /**
     * Menghitung nilai 'per_page' yang aman dan terbatas untuk mencegah DoS alokasi memori.
     */
    public static function perPage(Request|array|null $request, int $default = 15, int $min = 5, int $max = 100): int
    {
        $value = is_array($request)
            ? ($request['per_page'] ?? null)
            : ($request instanceof Request ? $request->input('per_page') : null);

        if ($value === null || !is_numeric($value)) {
            return $default;
        }

        $intVal = (int) $value;

        return max($min, min($max, $intVal));
    }

    /**
     * Escape wildcard karakter LIKE SQL (% dan _) agar dicari secara literal.
     */
    public static function escapeLike(string $value, string $char = '\\'): string
    {
        return str_replace(
            [$char, '%', '_'],
            [$char . $char, $char . '%', $char . '_'],
            $value
        );
    }
}
