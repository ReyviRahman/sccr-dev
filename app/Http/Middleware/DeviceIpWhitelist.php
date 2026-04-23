<?php

namespace App\Http\Middleware;

use App\Models\AttendanceDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $deviceCode = (string) $request->route('deviceCode');

        if ($deviceCode === '') {
            abort(404, 'Device code not found');
        }

        $device = AttendanceDevice::query()
            ->where('code', $deviceCode)
            ->where('is_active', 1)
            ->first();

        if (!$device) {
            abort(403, 'Device not registered or inactive');
        }

        $requestIp = $request->ip();

        // identifier saat ini bisa dipakai sebagai single IP / hostname
        // contoh: 192.168.11.196
        $allowed = [];

        if (!empty($device->identifier)) {
            $allowed[] = trim((string) $device->identifier);
        }

        // optional: kalau nanti kamu isi auth_modes_json / notes / kolom baru untuk multi IP
        // bisa dikembangkan di sini

        if (!empty($allowed) && !in_array($requestIp, $allowed, true)) {
            abort(403, "Unauthorized device IP: {$requestIp}");
        }

        // simpan object device supaya controller tidak query ulang
        $request->attributes->set('resolved_device', $device);

        return $next($request);
    }
}