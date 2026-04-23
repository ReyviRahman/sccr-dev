<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\DeviceLiveEvent;
use App\Services\Attendance\AttendanceCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeviceAttendanceController extends Controller
{
    public function __construct(
        protected AttendanceCaptureService $attendanceCaptureService
    ) {}

    public function postAttendance(int $liveEventId): JsonResponse
    {
        try {
            $event = DeviceLiveEvent::with('inbox')->findOrFail($liveEventId);

            $result = $this->attendanceCaptureService->postFromLiveEvent($event);

            Log::info('DEVICE_ATTENDANCE_POSTED', [
                'live_event_id' => $liveEventId,
                'employee_nip' => $event->employee_nip,
                'result' => $result,
            ]);

            return response()->json([
                'ok' => true,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('DEVICE_ATTENDANCE_POST_ERROR', [
                'live_event_id' => $liveEventId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}