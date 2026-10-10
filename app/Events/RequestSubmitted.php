<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event Real-Time WebSocket (Laravel Reverb) saat Karyawan atau Admin Gudang
 * mengajukan permohonan baru (Material Request, Tool Loan, Procurement Request, dll).
 */
class RequestSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $title,
        public string $message,
        public string $requestType,
        public ?string $requestNumber = null,
        public ?string $submittedBy = null,
        public ?string $url = null,
        public string $soundType = 'approval',
        public ?int $warehouseId = null
    ) {
    }

    /**
     * Tentukan private channel tempat event akan disiarkan.
     * Admin Pusat menerima untuk persetujuan, Owner menerima untuk pemantauan.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin-pusat'),
            new PrivateChannel('owner-monitoring'),
        ];

        if ($this->warehouseId) {
            $channels[] = new PrivateChannel("warehouse.{$this->warehouseId}");
        }

        return $channels;
    }

    /**
     * Nama event yang didengarkan oleh Laravel Echo di frontend.
     */
    public function broadcastAs(): string
    {
        return 'RequestSubmitted';
    }

    /**
     * Data payload yang dikirimkan ke WebSocket client.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'title'          => $this->title,
            'message'        => $this->message,
            'request_type'   => $this->requestType,
            'request_number' => $this->requestNumber,
            'submitted_by'   => $this->submittedBy,
            'url'            => $this->url,
            'sound_type'     => $this->soundType,
            'type'           => 'approval_needed',
            'created_at'     => now()->toIso8601String(),
        ];
    }
}
