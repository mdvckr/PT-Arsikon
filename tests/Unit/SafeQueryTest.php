<?php

namespace Tests\Unit;

use App\Support\SafeQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SafeQueryTest extends TestCase
{
    public function test_sort_whitelists_allowed_columns_and_rejects_sql_injection(): void
    {
        $query = DB::table('materials');

        $allowedColumns = [
            'name'       => 'materials.name',
            'created_at' => 'materials.created_at',
            'stock'      => 'materials.current_stock',
        ];

        // 1. Uji input sah
        SafeQuery::sort($query, ['sort_by' => 'name', 'sort_direction' => 'asc'], $allowedColumns);
        $orders = $query->orders ?? [];
        $this->assertEquals('materials.name', $orders[0]['column']);
        $this->assertEquals('asc', $orders[0]['direction']);

        // 2. Uji payload SQL Injection pada kolom (harus fallback ke default created_at)
        $queryInjected = DB::table('materials');
        $injectionPayload = 'name; DROP TABLE materials; --';
        SafeQuery::sort($queryInjected, ['sort_by' => $injectionPayload, 'sort_direction' => 'asc'], $allowedColumns);
        $ordersInjected = $queryInjected->orders ?? [];
        $this->assertEquals('materials.created_at', $ordersInjected[0]['column']);

        // 3. Uji payload SQL Injection pada arah sort (harus fallback ke 'desc')
        $queryInjectedDir = DB::table('materials');
        SafeQuery::sort($queryInjectedDir, ['sort_by' => 'name', 'sort_direction' => 'asc; SLEEP(5);'], $allowedColumns);
        $ordersInjectedDir = $queryInjectedDir->orders ?? [];
        $this->assertEquals('desc', $ordersInjectedDir[0]['direction']);
    }

    public function test_per_page_clamps_excessive_values(): void
    {
        // Melebihi max 100 -> harus dipatok 100
        $this->assertEquals(100, SafeQuery::perPage(['per_page' => 9999999]));

        // Di bawah min 5 -> harus dipatok 5
        $this->assertEquals(5, SafeQuery::perPage(['per_page' => 1]));

        // Nilai tidak valid -> fallback ke default 15
        $this->assertEquals(15, SafeQuery::perPage(['per_page' => 'invalid-abc']));

        // Nilai valid
        $this->assertEquals(50, SafeQuery::perPage(['per_page' => 50]));
    }

    public function test_escape_like_escapes_wildcards(): void
    {
        $raw = '50%_discount\\';
        $escaped = SafeQuery::escapeLike($raw);

        $this->assertEquals('50\\%\\_discount\\\\', $escaped);
    }
}
