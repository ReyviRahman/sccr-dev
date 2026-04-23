<?php

namespace App\Console\Commands;

use App\Models\DeviceProvisionJob;
use App\Services\Devices\Provision\DeviceProvisionJobRunnerService;
use Illuminate\Console\Command;

class RunDeviceProvisionQueue extends Command
{
    protected $signature = 'devices:provision-runner {--once} {--limit=20}';
    protected $description = 'Process queued employee provisioning jobs to biometric devices';

    public function handle(DeviceProvisionJobRunnerService $runner): int
    {
        $limit = (int) $this->option('limit');

        $jobs = DeviceProvisionJob::query()
            ->where('queue_status', 'pending')
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                  ->orWhere('next_retry_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No pending device provisioning jobs.');
            return self::SUCCESS;
        }

        foreach ($jobs as $job) {
            $this->info("Processing job #{$job->id} {$job->action_type} {$job->employee_nip}");
            $runner->runOne($job);
        }

        return self::SUCCESS;
    }
}