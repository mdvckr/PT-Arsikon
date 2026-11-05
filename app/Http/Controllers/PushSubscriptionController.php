<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Store or update Web Push subscription for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint'    => 'required|string',
            'keys.auth'   => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        $endpoint = $validated['endpoint'];
        $key      = $validated['keys']['p256dh'];
        $token    = $validated['keys']['auth'];
        $encoding = $request->input('contentEncoding', 'aesgcm');

        $request->user()->updatePushSubscription($endpoint, $key, $token, $encoding);

        return response()->json([
            'success' => true,
            'message' => 'Langganan Web Push berhasil disimpan.',
        ]);
    }

    /**
     * Delete Web Push subscription for the authenticated user.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $request->user()->deletePushSubscription($validated['endpoint']);

        return response()->json([
            'success' => true,
            'message' => 'Langganan Web Push dinonaktifkan.',
        ]);
    }
}

