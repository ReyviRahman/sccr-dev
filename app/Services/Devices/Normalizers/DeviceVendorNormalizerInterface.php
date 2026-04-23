<?php

namespace App\Services\Devices\Normalizers;

use App\Models\AttendanceDevice;
use Illuminate\Http\Request;

interface DeviceVendorNormalizerInterface
{
    public function normalize(Request $request, AttendanceDevice $device): array;
}