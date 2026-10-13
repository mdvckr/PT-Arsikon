<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'id' => $id]);
        }

        return back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    public function unreadCount()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['count' => 0, 'latest' => null]);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $latest = $user->unreadNotifications()->latest()->first();

        return response()->json([
            'count' => $unreadCount,
            'latest' => $latest ? [
                'id' => $latest->id,
                'title' => $latest->data['title'] ?? 'Notifikasi Baru',
                'message' => $latest->data['message'] ?? '',
                'type' => $latest->data['type'] ?? 'info',
                'tint' => $this->resolveTintType($latest->data['type'] ?? 'info'),
                'url' => $latest->data['url'] ?? $latest->data['link'] ?? route('notifications.index'),
                'sound_type' => $latest->data['sound_type'] ?? null,
                'created_at_human' => $latest->created_at ? $latest->created_at->diffForHumans() : 'Baru saja',
            ] : null,
        ]);
    }

    public function unreadList()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['count' => 0, 'unread' => []]);
        }

        $notifications = $user->unreadNotifications()->latest()->limit(20)->get();

        $mapped = $notifications->map(function ($notif) {
            $data = $notif->data ?? [];
            $rawType = $data['type'] ?? 'info';
            $tint = $this->resolveTintType($rawType);

            return [
                'id' => $notif->id,
                'title' => $data['title'] ?? 'Pemberitahuan Sistem',
                'message' => $data['message'] ?? '',
                'type' => $tint,
                'raw_type' => $rawType,
                'url' => $data['url'] ?? $data['link'] ?? route('notifications.index'),
                'sound_type' => $data['sound_type'] ?? null,
                'created_at' => $notif->created_at?->toIso8601String(),
                'created_at_human' => $notif->created_at ? $notif->created_at->diffForHumans() : 'Baru saja',
                'is_today' => $notif->created_at ? $notif->created_at->isToday() : true,
            ];
        });

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'unread' => $mapped,
        ]);
    }

    protected function resolveTintType(?string $rawType): string
    {
        return match (true) {
            in_array($rawType, ['approved', 'success']) => 'success',
            in_array($rawType, ['approval_needed', 'approval', 'warning', 'stock_alert']) => 'warning',
            in_array($rawType, ['rejected', 'cancelled', 'error', 'danger']) => 'danger',
            default => 'info',
        };
    }
}

