<?php

namespace App\Livewire\Auth\Sso\DevTools;

use App\Services\Devices\Provision\DeviceProvisionJobRunnerService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SsoProvisionQueuePage extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];
    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $searchNip = '';
    public string $filterDevice = '';
    public string $filterStatus = '';
    public string $filterAction = '';
    public int $perPage = 25;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'Auth', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'SSO Dev Tools', 'route' => 'sso.devtools.index', 'color' => 'text-white'],
            ['label' => 'Provision Queue', 'color' => 'text-white font-semibold'],
        ];
    }

    public function retryOne(int $jobId, DeviceProvisionJobRunnerService $runner): void
    {
        try {
            $job = \App\Models\DeviceProvisionJob::findOrFail($jobId);
            $runner->runOne($job);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => "Job #{$jobId} diproses ulang."];
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }
    }

    protected function baseQuery()
    {
        return DB::table('emp_device_provision_jobs')
            ->when(trim($this->searchNip) !== '', fn ($q) => $q->where('employee_nip', 'like', '%'.trim($this->searchNip).'%'))
            ->when($this->filterDevice !== '', fn ($q) => $q->where('target_device_code', $this->filterDevice))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('queue_status', $this->filterStatus))
            ->when($this->filterAction !== '', fn ($q) => $q->where('action_type', $this->filterAction))
            ->orderByDesc('id');
    }

    public function render()
    {
        $rows = $this->baseQuery()->paginate($this->perPage);

        $deviceOptions = DB::table('emp_attendance_devices')
            ->where('provision_is_enabled', 1)
            ->orderBy('code')
            ->pluck('code', 'code')
            ->toArray();

        return view('livewire.auth.sso.dev-tools.sso-provision-queue-page', [
            'rows' => $rows,
            'deviceOptions' => $deviceOptions,
        ])->layout('components.sccr-layout');
    }
}