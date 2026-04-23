<?php

namespace App\Services\Devices\Provision;

use App\Models\DevicePersonRegistry;
use App\Models\DeviceProvisionJob;
use App\Models\DeviceProvisionResult;

class DeviceProvisionJobRunnerService
{
    public function __construct(
        protected DeviceProvisionPlannerService $planner,
        protected HikvisionProvisionService $hikvision,
    ) {}

    public function runOne(DeviceProvisionJob $job): void
    {
        $job->update([
            'queue_status' => 'processing',
            'attempt_count' => $job->attempt_count + 1,
            'last_attempt_at' => now(),
            'updated_at' => now(),
        ]);

        $devices = $this->planner->resolveTargetDevices($job);

        if ($devices->isEmpty()) {
            $job->update([
                'queue_status' => 'failed',
                'processed_at' => now(),
                'last_error_message' => 'No target devices resolved',
                'updated_at' => now(),
            ]);
            return;
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($devices as $device) {
            try {
                $result = match ($job->action_type) {
                    'upsert_person' => $this->hikvision->upsertPerson(
                        $device,
                        (string) $job->employee_nip,
                        (string) $job->employee_name
                    ),
                    'disable_person' => $this->hikvision->disablePerson(
                        $device,
                        (string) $job->employee_nip,
                        (string) $job->employee_name
                    ),
                    'delete_person' => $this->hikvision->deletePerson(
                        $device,
                        (string) $job->employee_nip
                    ),
                    default => throw new \RuntimeException("Unsupported action_type: {$job->action_type}"),
                };

                $ok = (bool) ($result['response']['ok'] ?? false);

                DeviceProvisionResult::updateOrCreate(
                    [
                        'provision_job_id' => $job->id,
                        'device_code' => $device->code,
                    ],
                    [
                        'device_id' => $device->id,
                        'employee_nip' => $job->employee_nip,
                        'employee_name' => $job->employee_name,
                        'action_type' => $job->action_type,
                        'request_payload_json' => $result['request'],
                        'response_payload_json' => $result['response'],
                        'result_status' => $ok ? 'success' : 'failed',
                        'result_message' => $ok ? 'OK' : 'Device API rejected request',
                        'http_status_code' => $result['response']['status'] ?? null,
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->syncRegistry($job, $device, $ok, $result);

                if ($ok) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                DeviceProvisionResult::updateOrCreate(
                    [
                        'provision_job_id' => $job->id,
                        'device_code' => $device->code,
                    ],
                    [
                        'device_id' => $device->id,
                        'employee_nip' => $job->employee_nip,
                        'employee_name' => $job->employee_name,
                        'action_type' => $job->action_type,
                        'request_payload_json' => $job->payload_json,
                        'response_payload_json' => ['error' => $e->getMessage()],
                        'result_status' => 'failed',
                        'result_message' => $e->getMessage(),
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                DevicePersonRegistry::updateOrCreate(
                    [
                        'employee_nip' => $job->employee_nip,
                        'device_code' => $device->code,
                    ],
                    [
                        'person_uuid' => $job->person_uuid,
                        'device_id' => $device->id,
                        'company_db' => $device->company_db,
                        'sync_status' => 'failed',
                        'last_action' => $job->action_type,
                        'last_synced_name' => $job->employee_name,
                        'last_payload_hash' => hash('sha256', json_encode($job->payload_json)),
                        'remote_exists' => 0,
                        'last_error_at' => now(),
                        'last_error_message' => $e->getMessage(),
                        'updated_at' => now(),
                    ]
                );

                $failedCount++;
            }
        }

        $finalStatus = match (true) {
            $successCount > 0 && $failedCount === 0 => 'success',
            $successCount > 0 && $failedCount > 0 => 'partial',
            default => 'failed',
        };

        $job->update([
            'queue_status' => $finalStatus,
            'processed_at' => now(),
            'last_error_message' => $failedCount > 0 ? 'Some or all device calls failed' : null,
            'updated_at' => now(),
        ]);
    }

    protected function syncRegistry(DeviceProvisionJob $job, $device, bool $ok, array $result): void
    {
        $status = match ($job->action_type) {
            'upsert_person' => $ok ? 'synced' : 'failed',
            'disable_person' => $ok ? 'disabled' : 'failed',
            'delete_person' => $ok ? 'deleted' : 'failed',
            default => 'failed',
        };

        $remoteExists = match ($job->action_type) {
            'delete_person' => 0,
            'disable_person' => 1,
            'upsert_person' => $ok ? 1 : 0,
            default => 0,
        };

        DevicePersonRegistry::updateOrCreate(
            [
                'employee_nip' => $job->employee_nip,
                'device_code' => $device->code,
            ],
            [
                'person_uuid' => $job->person_uuid,
                'device_id' => $device->id,
                'company_db' => $device->company_db,
                'sync_status' => $status,
                'last_action' => $job->action_type,
                'last_synced_name' => $job->employee_name,
                'last_payload_hash' => hash('sha256', json_encode($result['request'] ?? [])),
                'remote_exists' => $remoteExists,
                'last_synced_at' => $ok ? now() : null,
                'last_error_at' => $ok ? null : now(),
                'last_error_message' => $ok ? null : json_encode($result['response'] ?? []),
                'updated_at' => now(),
            ]
        );
    }
}