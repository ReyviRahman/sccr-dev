<?php

namespace App\Services\Devices;

use Carbon\Carbon;

class LiveEventPolicyService
{
    public function isRecognitionEvent(string $subEventType): bool
    {
        return in_array($subEventType, ['75'], true);
    }

    public function isLive(?Carbon $eventTimeDevice, int $maxSeconds = 120): array
    {
        if (!$eventTimeDevice) {
            return [
                'is_live' => false,
                'diff_seconds' => null,
                'reason' => 'missing_event_time',
            ];
        }

        $diff = abs($eventTimeDevice->diffInSeconds(now(), false));

        return [
            'is_live' => $diff <= $maxSeconds,
            'diff_seconds' => $diff,
            'reason' => $diff <= $maxSeconds ? 'ok' : 'old_event',
        ];
    }
}