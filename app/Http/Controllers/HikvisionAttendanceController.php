<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HikvisionAttendanceController extends Controller
{
    public function receive(Request $request)
    {
        $eventLog = $request->input('event_log');
        if (!$eventLog) {
            return response('IGNORED', 200);
        }

        $data = json_decode($eventLog, true);
        if (!is_array($data)) {
            return response('IGNORED', 200);
        }

        $ac = $data['AccessControllerEvent'] ?? [];

        $nip          = $ac['employeeNoString'] ?? null;
        $name         = $ac['name'] ?? null;
        $subEventType = (int) ($ac['subEventType'] ?? 0);
        $eventTimeRaw = $data['dateTime'] ?? null;

        if (!$nip || !$name) {
            return response('IGNORED', 200);
        }

        // fokus hanya recognition sukses
        if ($subEventType !== 75) {
            return response('IGNORED', 200);
        }

        try {
            $eventTime = Carbon::parse($eventTimeRaw);
        } catch (\Throwable $e) {
            return response('IGNORED', 200);
        }

        // toleransi realtime, misalnya 2 menit
        $diffInSeconds = abs($eventTime->diffInSeconds(now(), false));
        if ($diffInSeconds > 120) {
            Log::info('HIKVISION_OLD_EVENT', [
                'employee_no' => $nip,
                'name' => $name,
                'event_time' => $eventTime->format('Y-m-d H:i:s'),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'diff_seconds' => $diffInSeconds,
            ]);

            return response('IGNORED_OLD_EVENT', 200);
        }

        // dedupe supaya scan cepat berulang tidak dobel proses
        $duplicate = DB::table('emp_attendance_hikvision_live')
            ->where('employee_nip', $nip)
            ->where('event_time', $eventTime->format('Y-m-d H:i:s'))
            ->exists();

        if ($duplicate) {
            return response('IGNORED_DUPLICATE', 200);
        }

        DB::table('emp_attendance_hikvision_live')->insert([
            'employee_nip' => $nip,
            'employee_name' => $name,
            'event_time' => $eventTime->format('Y-m-d H:i:s'),
            'sub_event_type' => $subEventType,
            'raw_payload' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('HIKVISION_LIVE_EVENT', [
            'employee_no' => $nip,
            'name' => $name,
            'event_time' => $eventTime->format('Y-m-d H:i:s'),
            'sub_event_type' => $subEventType,
        ]);

        return response()->json([
            'ok' => true,
            'nip' => $nip,
            'name' => $name,
            'event_time' => $eventTime->format('Y-m-d H:i:s'),
        ]);
    }
}