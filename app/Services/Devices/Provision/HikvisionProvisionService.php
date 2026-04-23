<?php

namespace App\Services\Devices\Provision;

use App\Models\AttendanceDevice;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HikvisionProvisionService
{
    public function upsertPerson(AttendanceDevice $device, string $nip, string $name): array
    {
        $search = $this->searchPerson($device, $nip);

        if (($search['exists'] ?? false) === true) {
            return $this->modifyPerson($device, $nip, $name, true);
        }

        return $this->recordPerson($device, $nip, $name, true);
    }

    public function disablePerson(AttendanceDevice $device, string $nip, string $name): array
    {
        return $this->modifyPerson($device, $nip, $name, false);
    }

    public function deletePerson(AttendanceDevice $device, string $nip): array
    {
        $payload = [
            'UserInfoDetailDeleteCond' => [
                'EmployeeNoList' => [
                    ['employeeNo' => $nip],
                ],
            ],
        ];

        $url = $this->buildUrl($device, 'delete');
        $resp = $this->sendJson($device, 'PUT', $url, $payload);

        return [
            'request' => $payload,
            'response' => $resp,
        ];
    }

    public function searchPerson(AttendanceDevice $device, string $nip): array
    {
        $payload = [
            'UserInfoSearchCond' => [
                'searchID' => (string) now()->timestamp,
                'searchResultPosition' => 0,
                'maxResults' => 1,
                'EmployeeNoList' => [
                    ['employeeNo' => $nip],
                ],
            ],
        ];

        $url = $this->buildUrl($device, 'search');
        $resp = $this->sendJson($device, 'POST', $url, $payload);

        $exists = false;
        $ok = false;
        $body = $resp['body'] ?? null;

        if (is_array($body)) {
            $searchNode = $body['UserInfoSearch'] ?? null;

            if (is_array($searchNode)) {
                $responseStatus = strtoupper((string) ($searchNode['responseStatusStrg'] ?? ''));

                if ($responseStatus === 'OK') {
                    $ok = true;
                }

                $numOfMatches = (int) ($searchNode['numOfMatches'] ?? 0);
                if ($numOfMatches > 0) {
                    $exists = true;
                }

                if (isset($searchNode['UserInfo']) && is_array($searchNode['UserInfo']) && count($searchNode['UserInfo']) > 0) {
                    $exists = true;
                }
            }
        }

        $resp['ok'] = $ok;
        $resp['exists'] = $exists;

        return $resp;
    }

    protected function recordPerson(AttendanceDevice $device, string $nip, string $name, bool $enabled): array
    {
        $payload = $this->buildUserInfoPayload($nip, $name, $enabled);
        $url = $this->buildUrl($device, 'record');
        $resp = $this->sendJson($device, 'POST', $url, $payload);

        return [
            'request' => $payload,
            'response' => $resp,
        ];
    }

    protected function modifyPerson(AttendanceDevice $device, string $nip, string $name, bool $enabled): array
    {
        $payload = $this->buildUserInfoPayload($nip, $name, $enabled);
        $url = $this->buildUrl($device, 'modify');
        $resp = $this->sendJson($device, 'PUT', $url, $payload);

        return [
            'request' => $payload,
            'response' => $resp,
        ];
    }

    protected function buildUserInfoPayload(string $nip, string $name, bool $enabled): array
    {
        return [
            'UserInfo' => [
                'employeeNo' => $nip,
                'name' => $name,
                'userType' => 'normal',
                'gender' => 'male',
                'localUIRight' => false,
                'maxOpenDoorTime' => 0,
                'Valid' => [
                    'enable' => $enabled,
                    'beginTime' => now()->startOfDay()->format('Y-m-d\T00:00:00'),
                    'endTime' => now()->addYears(10)->format('Y-m-d\T23:59:59'),
                    'timeType' => 'local',
                ],
                'doorRight' => '1',
                'RightPlan' => [
                    [
                        'doorNo' => 1,
                        'planTemplateNo' => '1',
                    ],
                ],
                'userVerifyMode' => '',
            ],
        ];
    }

    protected function buildUrl(AttendanceDevice $device, string $action): string
    {
        $cfg = $this->resolveProvisionConfig($device);

        $path = match ($action) {
            'record' => $cfg['record_path'],
            'modify' => $cfg['modify_path'],
            'search' => $cfg['search_path'],
            'delete' => $cfg['delete_path'],
            default => throw new \RuntimeException("Unknown Hikvision action {$action}"),
        };

        if (empty($device->identifier) || empty($path)) {
            throw new \RuntimeException("Device host/path belum lengkap untuk {$device->code}");
        }

        return "{$cfg['scheme']}://{$device->identifier}{$path}";
    }

    protected function resolveProvisionConfig(AttendanceDevice $device): array
    {
        return [
            'scheme' => $device->provision_scheme ?: config('devices.hikvision.scheme', 'http'),
            'http_auth' => $device->provision_http_auth ?: config('devices.hikvision.http_auth', 'digest'),
            'username' => $device->provision_username ?: config('devices.hikvision.username', 'admin'),
            'password' => $device->provision_password_decrypted ?: config('devices.hikvision.password', ''),
            'record_path' => $device->provision_record_path ?: config('devices.hikvision.paths.record_person'),
            'modify_path' => $device->provision_modify_path ?: config('devices.hikvision.paths.modify_person'),
            'search_path' => $device->provision_search_path ?: config('devices.hikvision.paths.search_person'),
            'delete_path' => $device->provision_delete_path ?: config('devices.hikvision.paths.delete_person'),
        ];
    }

    protected function clientForDevice(AttendanceDevice $device): PendingRequest
    {
        $cfg = $this->resolveProvisionConfig($device);

        $client = Http::timeout(20)
            ->acceptJson()
            ->asJson();

        if (!empty($cfg['username'])) {
            if ($cfg['http_auth'] === 'basic') {
                $client = $client->withBasicAuth($cfg['username'], $cfg['password']);
            } else {
                $client = $client->withOptions([
                    'auth' => [$cfg['username'], $cfg['password'], 'digest'],
                ]);
            }
        }

        return $client;
    }

    protected function sendJson(AttendanceDevice $device, string $method, string $url, array $payload): array
    {
        $cfg = $this->resolveProvisionConfig($device);

        Log::info('HIKVISION_PROVISION_REQUEST', [
            'device_code' => $device->code,
            'method' => $method,
            'url' => $url,
            'auth_mode' => $cfg['http_auth'],
            'payload' => $payload,
        ]);

        $resp = $this->clientForDevice($device)->send($method, $url, [
            'json' => $payload,
        ]);

        try {
            $body = $resp->json();
        } catch (\Throwable $e) {
            $body = $resp->body();
        }

        Log::info('HIKVISION_PROVISION_RESPONSE', [
            'device_code' => $device->code,
            'method' => $method,
            'url' => $url,
            'http_status' => $resp->status(),
            'body' => $body,
        ]);

        if ($resp->successful()) {
            $device->forceFill([
                'provision_last_success_at' => now(),
                'provision_last_error_at' => null,
                'provision_last_error_message' => null,
            ])->save();
        } else {
            $device->forceFill([
                'provision_last_error_at' => now(),
                'provision_last_error_message' => is_array($body) ? json_encode($body) : (string) $body,
            ])->save();
        }

        return [
            'ok' => $this->isSuccessResponseData($resp->status(), $body),
            'status' => $resp->status(),
            'body' => $body,
        ];
    }

    protected function isSuccessResponseData(int $httpStatus, mixed $body): bool
    {
        if ($httpStatus < 200 || $httpStatus >= 300) {
            return false;
        }

        if (is_array($body)) {
            $statusCode = $body['statusCode'] ?? null;
            $statusString = strtolower((string) ($body['statusString'] ?? ''));
            $subStatusCode = strtolower((string) ($body['subStatusCode'] ?? ''));

            if ((string) $statusCode === '1' && $statusString === 'ok') {
                return true;
            }

            if ($subStatusCode === 'ok') {
                return true;
            }
        }

        return false;
    }
}