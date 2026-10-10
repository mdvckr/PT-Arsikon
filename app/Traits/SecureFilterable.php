<?php

namespace App\Traits;

use App\Support\SafeQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SecureFilterable
{
    /**
     * Scope query untuk menerapkan search, filtering, dan dynamic sorting secara aman.
     *
     * @param Builder $query
     * @param Request|array|null $request
     * @param array<string> $searchColumns Kolom-kolom yang dicari dengan kata kunci 'search'
     * @param array<string, string> $allowedSortColumns Kolom-kolom yang diizinkan untuk di-sort
     * @param string $defaultSortKey
     * @param string $defaultSortDirection
     */
    public function scopeSafeSearchAndSort(
        Builder $query,
        Request|array|null $request,
        array $searchColumns = [],
        array $allowedSortColumns = ['created_at' => 'created_at'],
        string $defaultSortKey = 'created_at',
        string $defaultSortDirection = 'desc'
    ): Builder {
        $searchTerm = is_array($request)
            ? ($request['search'] ?? null)
            : ($request instanceof Request ? $request->input('search') : null);

        // 1. Terapkan pencarian aman jika kata kunci tersedia
        if (!empty($searchColumns) && !empty($searchTerm)) {
            SafeQuery::search($query, $searchTerm, $searchColumns);
        }

        // 2. Terapkan sorting dinamis dengan whitelist ketat
        if (!empty($allowedSortColumns)) {
            SafeQuery::sort($query, $request, $allowedSortColumns, $defaultSortKey, $defaultSortDirection);
        }

        return $query;
    }
}
