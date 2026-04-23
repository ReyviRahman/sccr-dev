<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceWebhookAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $device = $request->attributes->get('resolved_device');

        if (!$device) {
            abort(403, 'Device context missing');
        }

        $expected = $device->webhook_secret ?? null;

        if (!$expected) {
            // kalau secret belum diisi, biarkan lewat
            return $next($request);
        }

        $token = (string) ($request->query('token') ?? $request->header('X-Device-Token', ''));

        if ($token === '' || !hash_equals($expected, $token)) {
            abort(403, 'Invalid device webhook token');
        }

        return $next($request);
    }
}