<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Procurement & Logistics Configurations
    |--------------------------------------------------------------------------
    */

    // Batas hari pencatatan kehilangan barang sejak GR dikonfirmasi/dibuat
    'loss_grace_days' => 3,

    // Ambang batas persentase kehilangan per vendor dalam 30 hari untuk trigger notifikasi peringatan
    'loss_alert_percent' => 3,
];
