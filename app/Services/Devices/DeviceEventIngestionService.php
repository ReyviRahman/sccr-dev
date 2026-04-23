<?php

namespace App\Services\Devices;

use App\Models\AttendanceDevice;
use App\Models\DeviceEventInbox;
use App\Models\DeviceLiveEvent;

class DeviceEventIngestionService
{
    public function buildDedupKey(AttendanceDevice $device, array $normalized): string
    {
        return sha1(implode('|', [
            $device->code,
            $normalized['person_identifier'] ?? '',
            optional($normalized['event_time_device'])->format('Y-m-d H:i:s'),
            $normalized['raw_sub_event_type'] ?? '',
        ]));
    }

    public function saveInbox(AttendanceDevice $device, array $normalized): array
    {
        $dedupKey = $this->buildDedupKey($device, $normalized);

        $inbox = DeviceEventInbox::firstOrCreate(
            ['dedup_key' => $dedupKey],
            [
                'device_id' => $device->id,
                'device_code' => $device->code,
                'device_identifier' => $device->identifier,
                'holding_id' => $device->holding_id,
                'company_db' => $device->company_db,
                'branch_code' => $device->branch_code,
                'outlet_code' => $device->outlet_code,
                'vendor' => $device->vendor ?? 'hikvision',
                'event_source' => 'webhook',
                'content_type' => request()->header('Content-Type'),
                'event_time_device' => $normalized['event_time_device'],
                'event_time_server' => $normalized['event_time_server'],
                'person_identifier' => $normalized['person_identifier'],
                'person_name' => $normalized['person_name'],
                'raw_verify_mode' => $normalized['raw_verify_mode'],
                'normalized_auth_mode' => $normalized['normalized_auth_mode'],
                'raw_event_type' => $normalized['raw_event_type'],
                'raw_sub_event_type' => $normalized['raw_sub_event_type'],
                'purpose_hint' => $normalized['purpose_hint'],
                'payload_json' => json_encode($normalized['payload_json'], JSON_UNESCAPED_UNICODE),
                'process_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return [$inbox, $inbox->wasRecentlyCreated];
    }

    public function saveLiveEvent(
        AttendanceDevice $device,
        DeviceEventInbox $inbox,
        int $diffSeconds
    ): DeviceLiveEvent {
        return DeviceLiveEvent::create([
            'inbox_id' => $inbox->id,
            'device_id' => $device->id,
            'device_code' => $device->code,
            'device_holding_id' => $device->holding_id,
            'device_company_db' => $device->company_db,
            'usage_scope' => $device->usage_scope,
            'employee_nip' => $inbox->person_identifier,
            'employee_name' => $inbox->person_name,
            'employee_holding_id' => null,
            'event_time_device' => $inbox->event_time_device,
            'event_time_server' => $inbox->event_time_server,
            'live_diff_seconds' => $diffSeconds,
            'auth_mode' => $inbox->normalized_auth_mode,
            'event_code' => $inbox->raw_event_type,
            'event_sub_code' => $inbox->raw_sub_event_type,
            'is_live' => 1,
            'business_status' => 'new',
            'response_json' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}