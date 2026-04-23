<?php

use App\Http\Controllers\Attendance\DeviceAttendanceController;
use App\Http\Controllers\Integrations\DeviceWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('integrations/devices')
    ->middleware([
        'device.ip.whitelist',
        // 'device.webhook.auth',
    ])
    ->group(function () {
        Route::post('/{deviceCode}/event', [DeviceWebhookController::class, 'receive'])
            ->name('api.devices.event');
    });

/*
|--------------------------------------------------------------------------
| INTERNAL DEVICE LIVE EVENT ACTIONS
|--------------------------------------------------------------------------
| Untuk sementara bisa dipakai manual test.
| Nanti bisa diamankan dengan auth:sanctum + api.token jika dipakai gateway/internal app.
*/
Route::post('/device-live/{liveEventId}/attendance', [DeviceAttendanceController::class, 'postAttendance'])
    ->name('api.device-live.attendance.post');

require __DIR__.'/api_v1.php';