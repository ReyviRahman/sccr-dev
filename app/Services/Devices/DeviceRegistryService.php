<?php

namespace App\Services\Devices;

use App\Models\AttendanceDevice;

class DeviceRegistryService
{
    public function resolveByCode(string $deviceCode): AttendanceDevice
    {
        $device = AttendanceDevice::query()
            ->where('code', $deviceCode)
            ->where('is_active', 1)
            ->first();

        if (!$device) {
            throw new \RuntimeException("Device not found: {$deviceCode}");
        }

        return $device;
    }

    public function touchSeen(AttendanceDevice $device): void
    {
        $device->forceFill([
            'last_seen_at' => now(),
        ])->save();
    }
}