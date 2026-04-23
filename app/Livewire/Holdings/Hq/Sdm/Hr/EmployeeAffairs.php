<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Holdings\Hq\Sdm\Hr\Emp_Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EmployeeAffairs extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?string $nip = null;

    public ?Emp_Employee $employee = null;

    public string $actionType = 'unlock';

    public string $notes = '';

    public bool $canUpdate = false;

    public function mount(?string $nip = null): void
    {
        $this->nip = $nip;

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Employee', 'route' => 'holdings.hq.sdm.hr.employee-table', 'color' => 'text-gray-800'],
            ['label' => 'Employee Affairs', 'color' => 'text-gray-900 font-semibold'],
        ];

        $user = auth()->user();
        $this->canUpdate = (bool) ($user?->hasPermission('EMP_UPDATE') ?? false);

        if ($nip) {
            $this->loadEmployee($nip);
        }
    }

    private function loadEmployee(string $nip): void
    {
        $this->employee = Emp_Employee::where('nip', $nip)->first();

        if (! $this->employee) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Employee tidak ditemukan.'];

            return;
        }

        $this->notes = $this->employee->attendance_note ?? '';
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.employee-affairs')
            ->layout('components.sccr-layout');
    }

    public function __invoke(?string $nip = null)
    {
        $this->mount($nip);

        return $this->render();
    }

    public function processAction(): void
    {
        if (! $this->employee) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Employee tidak ditemukan.'];

            return;
        }

        if (! $this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin untuk update.'];

            return;
        }

        $this->validate([
            'actionType' => 'required|in:unlock,resign,cuti',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $nip = $this->employee->nip;

            match ($this->actionType) {
                'unlock' => DB::table('employees')
                    ->where('nip', $nip)
                    ->update([
                        'attendance_note' => null,
                        'updated_at' => $now,
                    ]),
                'resign' => DB::table('employees')
                    ->where('nip', $nip)
                    ->update([
                        'employee_status' => 'Resign',
                        'tanggal_keluar' => $now->format('Y-m-d'),
                        'attendance_note' => $this->notes ?: 'Resign',
                        'updated_at' => $now,
                    ]),
                'cuti' => DB::table('employees')
                    ->where('nip', $nip)
                    ->update([
                        'attendance_note' => $this->notes ?: 'Cuti/Izin',
                        'updated_at' => $now,
                    ]),
                default => null,
            };

            DB::commit();

            $this->employee = Emp_Employee::where('nip', $nip)->first();
            $this->notes = $this->employee->attendance_note ?? '';

            $actionMessages = [
                'unlock' => 'Attendance berhasil di-unlock.',
                'resign' => 'Employee status改为 Resign.',
                'cuti' => 'Cuti/Izin dicatat.',
            ];

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => $actionMessages[$this->actionType] ?? 'Berhasil.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Gagal memproses.'];
        }
    }
}
