<?php

namespace App\Livewire\Auth\Sso\DevTools;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SsoDeviceRegistryPage extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $searchNip = '';
    public string $filterDevice = '';
    public string $filterStatus = '';
    public int $perPage = 25;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'Auth', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'SSO Dev Tools', 'route' => 'sso.devtools.index', 'color' => 'text-white'],
            ['label' => 'Person Registry', 'color' => 'text-white font-semibold'],
        ];
    }

    protected function baseQuery()
    {
        return DB::table('emp_device_person_registry as r')
            ->when(trim($this->searchNip) !== '', fn ($q) => $q->where('r.employee_nip', 'like', '%'.trim($this->searchNip).'%'))
            ->when($this->filterDevice !== '', fn ($q) => $q->where('r.device_code', $this->filterDevice))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('r.sync_status', $this->filterStatus))
            ->orderByDesc('r.id');
    }

    public function render()
    {
        $rows = $this->baseQuery()->paginate($this->perPage);

        $deviceOptions = DB::table('emp_attendance_devices')
            ->where('provision_is_enabled', 1)
            ->orderBy('code')
            ->pluck('code', 'code')
            ->toArray();

        return view('livewire.auth.sso.dev-tools.sso-device-registry-page', [
            'rows' => $rows,
            'deviceOptions' => $deviceOptions,
        ])->layout('components.sccr-layout');
    }
}