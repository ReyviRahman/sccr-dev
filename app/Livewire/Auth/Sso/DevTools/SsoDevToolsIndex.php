<?php

namespace App\Livewire\Auth\Sso\DevTools;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoDevToolsIndex extends Component
{
    public array $breadcrumbs = [];
    public array $stats = [];
    public array $recentFailedJobs = [];
    public array $recentPendingJobs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'Auth', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'SSO Dev Tools', 'color' => 'text-white font-semibold'],
        ];

        $this->loadStats();
        $this->loadRecentFailedJobs();
        $this->loadRecentPendingJobs();
    }

    protected function loadStats(): void
    {
        $this->stats = [
            'active_devices' => DB::table('emp_attendance_devices')
                ->where('is_active', 1)
                ->count(),

            'provision_enabled_devices' => DB::table('emp_attendance_devices')
                ->where('is_active', 1)
                ->where('provision_is_enabled', 1)
                ->count(),

            'pending_jobs' => DB::table('emp_device_provision_jobs')
                ->where('queue_status', 'pending')
                ->count(),

            'failed_jobs' => DB::table('emp_device_provision_jobs')
                ->whereIn('queue_status', ['failed', 'partial'])
                ->count(),

            'registry_synced' => DB::table('emp_device_person_registry')
                ->where('sync_status', 'synced')
                ->count(),

            'registry_failed' => DB::table('emp_device_person_registry')
                ->where('sync_status', 'failed')
                ->count(),
        ];
    }

    protected function loadRecentFailedJobs(): void
    {
        $this->recentFailedJobs = DB::table('emp_device_provision_jobs')
            ->whereIn('queue_status', ['failed', 'partial'])
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'employee_nip',
                'employee_name',
                'action_type',
                'target_device_code',
                'queue_status',
                'last_error_message',
                'updated_at',
            ])
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }

    protected function loadRecentPendingJobs(): void
    {
        $this->recentPendingJobs = DB::table('emp_device_provision_jobs')
            ->where('queue_status', 'pending')
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'employee_nip',
                'employee_name',
                'action_type',
                'target_device_code',
                'created_at',
            ])
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }

    public function refreshDashboard(): void
    {
        $this->loadStats();
        $this->loadRecentFailedJobs();
        $this->loadRecentPendingJobs();
    }

    public function render()
    {
        return view('livewire.auth.sso.dev-tools.sso-dev-tools-index')
            ->layout('components.sccr-layout');
    }
}