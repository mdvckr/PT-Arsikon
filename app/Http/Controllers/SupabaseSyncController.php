<?php

namespace App\Http\Controllers;

use App\Services\SupabaseSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SupabaseSyncController extends Controller
{
    public function __construct(protected SupabaseSyncService $syncService)
    {
    }

    /**
     * Tampilkan halaman status dan manajemen Supabase Cloud Sync.
     */
    public function index(): View
    {
        $stats = $this->syncService->getStats();
        $config = $this->syncService->getConfigSummary();

        return view('settings.supabase', compact('stats', 'config'));
    }

    /**
     * Endpoint AJAX untuk menguji koneksi ke Supabase.
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->syncService->testConnection();

        return response()->json($result);
    }

    /**
     * Endpoint AJAX untuk mengeksekusi PUSH (Lokal -> Supabase).
     */
    public function push(Request $request): JsonResponse
    {
        try {
            $result = $this->syncService->pushToSupabase();

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencadangkan data ke Supabase: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX untuk mengeksekusi PULL (Supabase -> Lokal).
     */
    public function pull(Request $request): JsonResponse
    {
        try {
            $result = $this->syncService->pullFromSupabase();

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menarik data dari Supabase: ' . $e->getMessage(),
            ], 500);
        }
    }
}
