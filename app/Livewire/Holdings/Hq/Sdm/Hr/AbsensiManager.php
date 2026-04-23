<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use Livewire\Component;

class AbsensiManager extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canUpload = false;

    public bool $canGenerate = false;

    public bool $canDownload = false;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Absensi', 'color' => 'text-gray-900 font-semibold'],
        ];

        $user = auth()->user();
        $this->canUpload = (bool) ($user?->hasPermission('ABS_UPLOAD') ?? false);
        $this->canGenerate = (bool) ($user?->hasPermission('ABS_GENERATE') ?? false);
        $this->canDownload = (bool) ($user?->hasPermission('ABS_DOWNLOAD') ?? true);
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-manager')
            ->layout('components.sccr-layout');
    }
}
