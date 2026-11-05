<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SystemNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Sound/vibe the frontend should play for this notification.
     * Maps to a distinct chime in the layout JS:
     *   - approval_needed / approval (persetujuan) -> urgent double-beep
     *   - approved / success                 -> soft ascending
     *   - rejected / cancelled               -> low descending
     *   - warning / stock_alert              -> repeated pulse
     *   - info / null / default              -> standard 2-note chime
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info',
        public ?string $url = null,
        public ?string $sound_type = null
    ) {
    }

    /**
     * Get the notification's delivery channels.
     * Mengirim ke database sekaligus menyiarkan secara real-time via WebSocket (Reverb).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Aktifkan channel broadcast jika Reverb / Pusher aktif dan bukan saat unit testing
        if (config('broadcasting.default') !== 'null' && !app()->runningUnitTests()) {
            $channels[] = 'broadcast';
        }

        // Aktifkan Web Push jika user memiliki subscription
        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists() && !app()->runningUnitTests()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => $this->type,
            'url'        => $this->url,
            'sound_type' => $this->sound_type ?: $this->resolveSoundType(),
        ];
    }

    /**
     * Payload data khusus untuk siaran real-time WebSocket Reverb.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => $this->type,
            'url'        => $this->url,
            'sound_type' => $this->sound_type ?: $this->resolveSoundType(),
            'created_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Payload data untuk Web Push background notification.
     */
    public function toWebPush(object $notifiable, mixed $notification = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->message)
            ->icon('/assets/Logo-Dashboard.png')
            ->badge('/assets/Logo-Dashboard.png')
            ->data(['url' => $this->url ?: '/dashboard'])
            ->action('Buka Sistem', 'open');
    }

    /**
     * Derive a sound type from the notification type when one is not set explicitly.
     */
    protected function resolveSoundType(): string
    {
        return match (true) {
            in_array($this->type, ['approval_needed', 'approval', 'urgent']) => 'approval',
            in_array($this->type, ['approved', 'success'])                   => 'success',
            in_array($this->type, ['rejected', 'cancelled', 'deleted'])      => 'warning',
            in_array($this->type, ['warning', 'stock_alert', 'error'])       => 'warning',
            default                                                          => 'info',
        };
    }
}
