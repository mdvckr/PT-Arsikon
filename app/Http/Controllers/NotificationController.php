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

    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    public function unreadCount()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $latest = $user->unreadNotifications()->latest()->first();

        return response()->json([
            'count' => $unreadCount,
            'latest' => $latest ? [
                'id' => $latest->id,
                'title' => $latest->data['title'] ?? 'Notifikasi Baru',
                'message' => $latest->data['message'] ?? '',
                'url' => $latest->data['url'] ?? $latest->data['link'] ?? route('notifications.index'),
            ] : null,
        ]);
    }
}

