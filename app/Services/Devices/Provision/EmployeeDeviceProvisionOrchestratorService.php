<?php

namespace App\Services\Devices\Provision;

use App\Models\DeviceProvisionJob;

class EmployeeDeviceProvisionOrchestratorService
{
    public function __construct(
        protected DeviceProvisionJobRunnerService $runner
    ) {}

    public function processEmployeeNow(string $employeeNip, int $limit = 20): array
    {
        $jobs = DeviceProvisionJob::query()
            ->where('employee_nip', $employeeNip)
            ->where('queue_status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $processed = 0;
        $success = 0;
        $failed = 0;

        foreach ($jobs as $job) {
            $this->runner->runOne($job->fresh());

            $processed++;

            $job->refresh();

            if ($job->queue_status === 'success') {
                $success++;
            } elseif ($job->queue_status === 'partial' || $job->queue_status === 'failed') {
                $failed++;
            }
        }

        return [
            'employee_nip' => $employeeNip,
            'processed_jobs' => $processed,
            'success_jobs' => $success,
            'failed_jobs' => $failed,
        ];
    }
}