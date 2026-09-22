<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
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
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
