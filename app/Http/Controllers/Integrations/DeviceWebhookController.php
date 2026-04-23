<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use App\Services\Attendance\AttendanceCaptureService;
use App\Services\Devices\DeviceEventIngestionService;
use App\Services\Devices\DeviceRegistryService;
use App\Services\Devices\LiveEventPolicyService;
use App\Services\Devices\Normalizers\HikvisionNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceWebhookController extends Controller
{
    public function __construct(
        protected DeviceRegistryService $registry,
        protected HikvisionNormalizer $hikvisionNormalizer,
        protected DeviceEventIngestionService $ingestion,
        protected LiveEventPolicyService $policy,
        protected AttendanceCaptureService $attendanceCaptureService,
    ) {}

    public function receive(Request $request, string $deviceCode): JsonResponse
    {
        try {
            /** @var AttendanceDevice $device */
            $device = $request->attributes->get('resolved_device')
                ?? $this->registry->resolveByCode($deviceCode);

            $this->registry->touchSeen($device);

            $normalized = $this->hikvisionNormalizer->normalize($request, $device);

            [$inbox, $created] = $this->ingestion->saveInbox($device, $normalized);

            if (!$created) {
                return response()->json([
                    'ok' => true,
                    'status' => 'duplicate',
                    'message' => 'Duplicate device event',
                ]);
            }

            if (empty($normalized['person_identifier'])) {
                $inbox->update([
                    'process_status' => 'ignored',
                    'process_note' => 'missing_person_identifier',
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'ok' => true,
                    'status' => 'ignored',
                    'message' => 'Missing person identifier',
                ]);
            }

            if (!$this->policy->isRecognitionEvent((string) $normalized['raw_sub_event_type'])) {
                $inbox->update([
                    'process_status' => 'ignored',
                    'process_note' => 'non_recognition_event',
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'ok' => true,
                    'status' => 'ignored',
                    'message' => 'Non recognition event',
                ]);
            }

            $liveCheck = $this->policy->isLive($normalized['event_time_device'], 120);

            if (!$liveCheck['is_live']) {
                $inbox->update([
                    'process_status' => 'ignored',
                    'process_note' => $liveCheck['reason'],
                    'updated_at' => now(),
                ]);

                Log::info('DEVICE_OLD_EVENT', [
                    'device_code' => $device->code,
                    'employee_no' => $normalized['person_identifier'],
                    'name' => $normalized['person_name'],
                    'event_time' => optional($normalized['event_time_device'])->format('Y-m-d H:i:s'),
                    'server_time' => now()->format('Y-m-d H:i:s'),
                    'diff_seconds' => $liveCheck['diff_seconds'],
                ]);

                return response()->json([
                    'ok' => true,
                    'status' => 'ignored_old_event',
                    'message' => 'Old event',
                ]);
            }

            $liveEvent = $this->ingestion->saveLiveEvent(
                $device,
                $inbox,
                (int) $liveCheck['diff_seconds']
            );

            $inbox->update([
                'process_status' => 'accepted',
                'process_note' => 'live_event',
                'updated_at' => now(),
            ]);

            Log::info('DEVICE_LIVE_EVENT', [
                'device_code' => $device->code,
                'employee_no' => $liveEvent->employee_nip,
                'name' => $liveEvent->employee_name,
                'event_time' => optional($liveEvent->event_time_device)->format('Y-m-d H:i:s'),
                'auth_mode' => $liveEvent->auth_mode,
                'usage_scope' => $liveEvent->usage_scope,
            ]);

            $attendanceResult = null;

            // AUTO POST ATTENDANCE
            if (in_array((string) $liveEvent->usage_scope, ['attendance', 'mixed'], true)) {
                try {
                    $attendanceResult = $this->attendanceCaptureService
                        ->postFromLiveEvent($liveEvent->fresh('inbox'));

                    Log::info('DEVICE_ATTENDANCE_AUTO_POSTED', [
                        'device_code' => $device->code,
                        'live_event_id' => $liveEvent->id,
                        'employee_nip' => $liveEvent->employee_nip,
                        'result' => $attendanceResult,
                    ]);
                } catch (\Throwable $attendanceError) {
                    Log::error('DEVICE_ATTENDANCE_AUTO_POST_ERROR', [
                        'device_code' => $device->code,
                        'live_event_id' => $liveEvent->id,
                        'employee_nip' => $liveEvent->employee_nip,
                        'message' => $attendanceError->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'ok' => true,
                'status' => 'accepted',
                'live_event_id' => $liveEvent->id,
                'employee_nip' => $liveEvent->employee_nip,
                'attendance_result' => $attendanceResult,
            ]);
        } catch (\Throwable $e) {
            Log::error('DEVICE_WEBHOOK_ERROR', [
                'device_code' => $deviceCode,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}