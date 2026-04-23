<?php

namespace App\Services\Attendance;

use App\Models\DeviceLiveEvent;
use Illuminate\Support\Facades\DB;

class AttendanceCaptureService
{
    public function postFromLiveEvent(DeviceLiveEvent $event): array
    {
        if ((string) $event->usage_scope !== 'attendance' && (string) $event->usage_scope !== 'mixed') {
            throw new \RuntimeException('Live event ini bukan untuk attendance');
        }

        if (!$event->employee_nip) {
            throw new \RuntimeException('Employee NIP kosong pada live event');
        }

        $employee = DB::table('employees')
            ->where('nip', $event->employee_nip)
            ->where('is_deleted', 0)
            ->first();

        if (!$employee) {
            throw new \RuntimeException("Employee {$event->employee_nip} tidak ditemukan atau nonaktif");
        }

        $workDate = $event->event_time_device->toDateString();

        // dedupe by live event id
        $existingByLiveEvent = DB::table('emp_attendance_logs')
            ->where('device_event_live_id', $event->id)
            ->first();

        if ($existingByLiveEvent) {
            return [
                'status' => 'duplicate_live_event',
                'attendance_log_id' => $existingByLiveEvent->id,
                'attendance_type' => $existingByLiveEvent->attendance_type,
                'work_date' => $existingByLiveEvent->work_date,
            ];
        }

        // tentukan IN / OUT dengan rule sederhana:
        // scan pertama hari itu = IN, berikutnya = OUT
        $hasIn = DB::table('emp_attendance_logs')
            ->where('employee_nip', $event->employee_nip)
            ->where('work_date', $workDate)
            ->where('attendance_type', 'IN')
            ->exists();

        $attendanceType = $hasIn ? 'OUT' : 'IN';

        // dedupe tambahan by emp + timestamp
        $duplicate = DB::table('emp_attendance_logs')
            ->where('employee_nip', $event->employee_nip)
            ->where('attendance_at', $event->event_time_device->format('Y-m-d H:i:s'))
            ->where('attendance_type', $attendanceType)
            ->exists();

        if ($duplicate) {
            return [
                'status' => 'duplicate_timestamp',
                'attendance_type' => $attendanceType,
                'work_date' => $workDate,
            ];
        }

        $rawPayload = $event->inbox?->payload_json;

        DB::table('emp_attendance_logs')->insert([
            'employee_nip' => $event->employee_nip,
            'holding_id' => $employee->holding_id,
            'device_id' => $event->device_id,
            'attendance_policy_id' => null,
            'shift_type_id' => null,
            'work_date' => $workDate,
            'attendance_at' => $event->event_time_device->format('Y-m-d H:i:s'),
            'attendance_type' => $attendanceType,
            'source_type' => match ($event->auth_mode) {
                'face' => 'face_scan',
                'finger' => 'finger_scan',
                'card' => 'card_scan',
                default => 'device_scan',
            },
            'auth_mode' => $event->auth_mode,
            'device_event_live_id' => $event->id,
            'raw_payload' => $rawPayload,
            'notes' => 'Captured from biometric live event',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attendanceLogId = (int) DB::getPdo()->lastInsertId();

        DB::statement('CALL sp_emp_rebuild_attendance_daily(?, ?, ?)', [
            $workDate,
            $workDate,
            $event->employee_nip,
        ]);

        $event->update([
            'business_status' => 'attendance_posted',
            'attendance_log_id' => $attendanceLogId,
            'updated_at' => now(),
        ]);

        return [
            'status' => 'posted',
            'attendance_log_id' => $attendanceLogId,
            'attendance_type' => $attendanceType,
            'work_date' => $workDate,
            'employee_nip' => $event->employee_nip,
        ];
    }
}