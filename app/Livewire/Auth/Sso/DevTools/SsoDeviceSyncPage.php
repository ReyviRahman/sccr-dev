<?php

namespace App\Livewire\Auth\Sso\DevTools;

use App\Services\Devices\Provision\DeviceProvisionBootstrapService;
use App\Services\Devices\Provision\EmployeeDeviceProvisionOrchestratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class SsoDeviceSyncPage extends Component
{
    public array $breadcrumbs = [];
    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $deviceOptions = [];

    // Bootstrap by device
    public string $deviceCode = 'HQ-BIO-01';
    public int $limit = 100;
    public bool $onlyOutOfSync = true;
    public bool $processNow = true;

    // Sync by NIP
    public string $singleNip = '';
    public string $singleMode = 'auto'; // auto|single_device
    public string $singleAction = 'upsert_person'; // upsert_person|disable_person|delete_person
    public string $singleDeviceCode = 'HQ-BIO-01';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'Auth', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'SSO Dev Tools', 'route' => 'sso.devtools.index', 'color' => 'text-white'],
            ['label' => 'Device Sync', 'color' => 'text-white font-semibold'],
        ];

        $this->deviceOptions = $this->getProvisionDeviceOptions();

        if (! isset($this->deviceOptions[$this->deviceCode]) && ! empty($this->deviceOptions)) {
            $first = array_key_first($this->deviceOptions);
            $this->deviceCode = $first;
            $this->singleDeviceCode = $first;
        }
    }

    protected function getProvisionDeviceOptions(): array
    {
        return DB::table('emp_attendance_devices')
            ->where('is_active', 1)
            ->where('provision_is_enabled', 1)
            ->orderBy('code')
            ->pluck('code', 'code')
            ->toArray();
    }

    public function runBootstrap(DeviceProvisionBootstrapService $service): void
    {
        try {
            $result = $service->bootstrapAndProcessSingleDevice(
                trim($this->deviceCode),
                max(1, min((int) $this->limit, 500)),
                (bool) $this->onlyOutOfSync,
                (bool) $this->processNow
            );

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "Device {$result['device_code']}: queued {$result['queued']}, processed {$result['processed']}, success {$result['success']}, failed {$result['failed']}.",
            ];
        } catch (\Throwable $e) {
            $this->toast = [
                'show' => true,
                'type' => 'warning',
                'message' => 'Bootstrap gagal: '.$e->getMessage(),
            ];
        }
    }

    public function syncSingle(EmployeeDeviceProvisionOrchestratorService $orchestrator): void
    {
        $nip = trim($this->singleNip);

        if ($nip === '') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'NIP wajib diisi.'];
            return;
        }

        try {
            if ($this->singleMode === 'single_device') {
                $employee = DB::table('employees')->where('nip', $nip)->first();

                if (! $employee) {
                    throw new \RuntimeException("Employee {$nip} tidak ditemukan.");
                }

                DB::table('emp_device_provision_jobs')->insert([
                    'job_uuid' => (string) Str::uuid(),
                    'employee_nip' => $nip,
                    'employee_name' => $employee->nama,
                    'person_uuid' => $employee->person_uuid ?? null,
                    'action_type' => $this->singleAction,
                    'target_scope' => 'single_device',
                    'target_device_code' => trim($this->singleDeviceCode),
                    'target_holding_id' => $employee->holding_id,
                    'source_table' => 'employees',
                    'source_pk' => $nip,
                    'source_company_db' => $employee->home_company_db ?? $employee->source_company_db ?? 'sccr_db',
                    'payload_json' => json_encode([
                        'nip' => $nip,
                        'nama' => $employee->nama,
                        'reason' => 'manual_single_nip_sync',
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'queue_status' => 'pending',
                    'attempt_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $result = $orchestrator->processEmployeeNow($nip, 20);

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "NIP {$nip}: processed {$result['processed_jobs']}, success {$result['success_jobs']}, failed {$result['failed_jobs']}.",
            ];
        } catch (\Throwable $e) {
            $this->toast = [
                'show' => true,
                'type' => 'warning',
                'message' => 'Sync NIP gagal: '.$e->getMessage(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.auth.sso.dev-tools.sso-device-sync-page')
            ->layout('components.sccr-layout');
    }
}