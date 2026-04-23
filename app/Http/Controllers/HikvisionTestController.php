<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

// class HikvisionTestController extends Controller
// {
//     public function receive(Request $request)
//     {
//         $raw = $request->getContent();

//         Log::info('HIKVISION HIT', [
//             'ip' => $request->ip(),
//             'headers' => $request->headers->all(),
//             'raw' => $raw,
//         ]);

//         return response('OK', 200);
//     }
// }

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HikvisionTestController extends Controller
{
    public function receive(Request $request)
    {
        $eventLog = $request->input('event_log');

        if (!$eventLog) {
            return response('IGNORED_NO_EVENT_LOG', 200);
        }

        $data = json_decode($eventLog, true);

        if (!is_array($data)) {
            return response('IGNORED_INVALID_JSON', 200);
        }

        $ac = $data['AccessControllerEvent'] ?? [];

        $employeeNo = $ac['employeeNoString'] ?? null;
        $name = $ac['name'] ?? null;
        $subEventType = (int) ($ac['subEventType'] ?? 0);
        $verifyMode = $ac['currentVerifyMode'] ?? null;
        $eventTimeRaw = $data['dateTime'] ?? null;

        if (!$employeeNo && !$name) {
            return response('IGNORED_NO_IDENTITY', 200);
        }

        // hanya event recognition yang relevan
        if (!in_array($subEventType, [75, 38], true)) {
            return response('IGNORED_SUB_EVENT', 200);
        }

        if (!$eventTimeRaw) {
            return response('IGNORED_NO_EVENT_TIME', 200);
        }

        try {
            $eventTime = Carbon::parse($eventTimeRaw);
        } catch (\Throwable $e) {
            return response('IGNORED_BAD_EVENT_TIME', 200);
        }

        $now = now();
        $diffInSeconds = abs($eventTime->diffInSeconds($now, false));

        // hanya event live, toleransi 5 menit
        if ($diffInSeconds > 300) {
            return response('IGNORED_OLD_EVENT', 200);
        }

        Log::info('HIKVISION_LIVE_ACCEPTED', [
            'device_ip' => $request->ip(),
            'event_time' => $eventTime->toDateTimeString(),
            'employee_no' => $employeeNo,
            'name' => $name,
            'sub_event_type' => $subEventType,
            'verify_mode' => $verifyMode,
        ]);

        return response()->json([
            'ok' => true,
            'employee_no' => $employeeNo,
            'name' => $name,
            'event_time' => $eventTime->toDateTimeString(),
        ]);
    }
}