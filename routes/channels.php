<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Saluran otorisasi WebSocket private untuk Laravel Reverb broadcasting.
|
*/

// 1. Channel Notifikasi Pribadi Pengguna
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// 2. Private Channel Khusus Admin Pusat (Menerima alur request & persetujuan)
Broadcast::channel('admin-pusat', function ($user) {
    return $user->hasRole('Admin Pusat');
});

// 3. Private Channel Khusus Owner (Menerima data pemantauan laporan real-time)
Broadcast::channel('owner-monitoring', function ($user) {
    return $user->hasRole('Owner');
});

// 4. Private Channel Gudang Spesifik
Broadcast::channel('warehouse.{warehouseId}', function ($user, $warehouseId) {
    if ($user->hasAnyRole(['Admin Pusat', 'Admin Gudang Pusat'])) {
        return true;
    }
    return $user->hasAccessToWarehouse(\App\Models\Warehouse::find($warehouseId));
});
