<?php

namespace App\Services\Devices\Normalizers;

use App\Models\AttendanceDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HikvisionNormalizer implements DeviceVendorNormalizerInterface
{
    public function normalize(Request $request, AttendanceDevice $device): array
    {
        $eventLog = $request->input('event_log');

        if (!$eventLog) {
            throw new \RuntimeException('event_log kosong');
        }

        $data = json_decode($eventLog, true);

        if (!is_array($data)) {
            throw new \RuntimeException('event_log tidak valid');
        }

        $ac = $data['AccessControllerEvent'] ?? [];

        $rawVerifyMode = (string) ($ac['currentVerifyMode'] ?? 'unknown');
        $subEventType  = (string) ($ac['subEventType'] ?? '');
        $employeeNo    = $ac['employeeNoString'] ?? null;
        $name          = $ac['name'] ?? null;

        $normalizedAuthMode = match (true) {
            str_contains(strtolower($rawVerifyMode), 'face') => 'face',
            str_contains(strtolower($rawVerifyMode), 'fp') => 'finger',
            str_contains(strtolower($rawVerifyMode), 'card') => 'card',
            default => 'unknown',
        };

        return [
            'event_time_device'    => isset($data['dateTime']) ? Carbon::parse($data['dateTime']) : null,
            'event_time_server'    => now(),
            'person_identifier'    => $employeeNo,
            'person_name'          => $name,
            'raw_verify_mode'      => $rawVerifyMode,
            'normalized_auth_mode' => $normalizedAuthMode,
            'raw_event_type'       => $data['eventType'] ?? 'AccessControllerEvent',
            'raw_sub_event_type'   => $subEventType,
            'purpose_hint'         => $device->usage_scope ?? 'unknown',
            'payload_json'         => $data,
            'has_picture'          => $request->hasFile('Picture'),
        ];
    }
}