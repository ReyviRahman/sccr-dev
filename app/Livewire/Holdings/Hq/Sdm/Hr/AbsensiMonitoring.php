<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AbsensiMonitoring extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $viewMode = 'no_attendance';

    public string $search = '';

    public string $filterHolding = '';

    public string $filterDepartment = '';

    public string $filterDivision = '';

    public int $perPage = 25;

    public int $refreshInterval = 30;

    public string $nextRefreshIn = '30';

    public array $summary = [
        'total_required' => 0,
        'did_attendance' => 0,
        'no_attendance' => 0,
        'percentage' => 0,
        'blocked' => 0,
        'lunch_budget' => 0,
    ];

    public array $holdings = [];

    public array $departments = [];

    public array $divisions = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Monitoring Absensi', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->loadFilters();
        $this->loadSummary();
    }

    public function loadFilters(): void
    {
        $this->holdings = DB::table('holdings')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();

        $this->departments = DB::table('departments')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();

        $this->divisions = DB::table('divisions')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();
    }

    public function loadSummary(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $baseQuery = DB::table('employees')
            ->where('is_deleted', 0)
            ->where('is_attendance_required', 1);

        $this->summary['total_required'] = (clone $baseQuery)->count();

        $this->summary['did_attendance'] = DB::table('emp_attendance_daily')
            ->where('work_date', $today)
            ->whereIn('employee_nip', (clone $baseQuery)->pluck('nip'))
            ->count();

        $this->summary['no_attendance'] = $this->summary['total_required'] - $this->summary['did_attendance'];

        $this->summary['percentage'] = $this->summary['total_required'] > 0
            ? round(($this->summary['did_attendance'] / $this->summary['total_required']) * 100, 1)
            : 0;

        $this->summary['blocked'] = DB::table('employees')
            ->where('is_deleted', 0)
            ->where('is_attendance_required', 1)
            ->where('attendance_note', 'Block employee attendance')
            ->count();

        $this->summary['lunch_budget'] = DB::table('emp_attendance_daily')
            ->where('work_date', $today)
            ->where('lunch_budget', '>', 0)
            ->count();
    }

    public function getEmployeesProperty()
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        if ($this->viewMode === 'did_attendance') {
            return $this->getDidAttendanceQuery()->paginate($this->perPage);
        }

        if ($this->viewMode === 'blocked') {
            return $this->getBlockedEmployeesQuery()->paginate($this->perPage);
        }

        if ($this->viewMode === 'lunch_budget') {
            return $this->getLunchBudgetEmployeesQuery()->paginate($this->perPage);
        }

        return $this->getNoAttendanceQuery()->paginate($this->perPage);
    }

    private function getDidAttendanceQuery()
    {
        $today = Carbon::today()->format('Y-m-d');

        $query = DB::table('employees as e')
            ->join('emp_attendance_daily as a', 'e.nip', '=', 'a.employee_nip')
            ->leftJoin('holdings as h', 'e.holding_id', '=', 'h.id')
            ->leftJoin('departments as d', 'e.department_id', '=', 'd.id')
            ->leftJoin('divisions as div', 'e.division_id', '=', 'div.id')
            ->where('a.work_date', $today)
            ->where('e.is_deleted', 0)
            ->where('e.is_attendance_required', 1)
            ->select([
                'e.nip',
                'e.nama',
                'h.name as holding_name',
                'd.name as department_name',
                'div.name as division_name',
                'a.first_in_at',
                'a.last_out_at',
                'a.attendance_status',
                'a.is_late',
                'a.late_minutes',
            ]);

        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('e.nip', 'like', $search)
                    ->orWhere('e.nama', 'like', $search);
            });
        }

        if ($this->filterHolding) {
            $query->where('e.holding_id', $this->filterHolding);
        }

        if ($this->filterDepartment) {
            $query->where('e.department_id', $this->filterDepartment);
        }

        if ($this->filterDivision) {
            $query->where('e.division_id', $this->filterDivision);
        }

        return $query->orderByDesc('a.first_in_at');
    }

    private function getNoAttendanceQuery()
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $policy = DB::table('emp_attendance_policies')->where('id', 1)->first();
        $graceMinutes = $policy->grace_late_minutes ?? 15;
        $checkinStart = $policy->default_checkin_start ?? '08:00:00';

        $thresholdTime = Carbon::parse($today.' '.$checkinStart)->addMinutes($graceMinutes);

        $subQuery = DB::table('emp_attendance_daily')
            ->where('work_date', $today)
            ->pluck('employee_nip')
            ->toArray();

        $query = DB::table('employees as e')
            ->leftJoin('holdings as h', 'e.holding_id', '=', 'h.id')
            ->leftJoin('departments as d', 'e.department_id', '=', 'd.id')
            ->leftJoin('divisions as div', 'e.division_id', '=', 'div.id')
            ->where('e.is_deleted', 0)
            ->where('e.is_attendance_required', 1)
            ->whereNotIn('e.nip', $subQuery)
            ->where('e.nip', 'NOT LIKE', '%OUT%')
            ->select([
                'e.nip',
                'e.nama',
                'h.name as holding_name',
                'd.name as department_name',
                'div.name as division_name',
                DB::raw("'{$checkinStart}' as checkin_start"),
                DB::raw("{$graceMinutes} as grace_minutes"),
            ]);

        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('e.nip', 'like', $search)
                    ->orWhere('e.nama', 'like', $search);
            });
        }

        if ($this->filterHolding) {
            $query->where('e.holding_id', $this->filterHolding);
        }

        if ($this->filterDepartment) {
            $query->where('e.department_id', $this->filterDepartment);
        }

        if ($this->filterDivision) {
            $query->where('e.division_id', $this->filterDivision);
        }

        return $query->orderBy('e.nama');
    }

    private function getBlockedEmployeesQuery()
    {
        $query = DB::table('employees as e')
            ->leftJoin('holdings as h', 'e.holding_id', '=', 'h.id')
            ->leftJoin('departments as d', 'e.department_id', '=', 'd.id')
            ->leftJoin('divisions as div', 'e.division_id', '=', 'div.id')
            ->where('e.is_deleted', 0)
            ->where('e.is_attendance_required', 1)
            ->where('e.attendance_note', 'Block employee attendance')
            ->select([
                'e.nip',
                'e.nama',
                'h.name as holding_name',
                'd.name as department_name',
                'div.name as division_name',
                'e.attendance_note',
            ]);

        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('e.nip', 'like', $search)
                    ->orWhere('e.nama', 'like', $search);
            });
        }

        if ($this->filterHolding) {
            $query->where('e.holding_id', $this->filterHolding);
        }

        if ($this->filterDepartment) {
            $query->where('e.department_id', $this->filterDepartment);
        }

        if ($this->filterDivision) {
            $query->where('e.division_id', $this->filterDivision);
        }

        return $query->orderBy('e.nama');
    }

    private function getLunchBudgetEmployeesQuery()
    {
        $today = Carbon::today()->format('Y-m-d');

        $query = DB::table('employees as e')
            ->join('emp_attendance_daily as a', 'e.nip', '=', 'a.employee_nip')
            ->leftJoin('holdings as h', 'e.holding_id', '=', 'h.id')
            ->leftJoin('departments as d', 'e.department_id', '=', 'd.id')
            ->leftJoin('divisions as div', 'e.division_id', '=', 'div.id')
            ->where('a.work_date', $today)
            ->where('e.is_deleted', 0)
            ->where('e.is_attendance_required', 1)
            ->where('a.lunch_budget', '>', 0)
            ->select([
                'e.nip',
                'e.nama',
                'h.name as holding_name',
                'd.name as department_name',
                'div.name as division_name',
                'a.lunch_budget',
            ]);

        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('e.nip', 'like', $search)
                    ->orWhere('e.nama', 'like', $search);
            });
        }

        if ($this->filterHolding) {
            $query->where('e.holding_id', $this->filterHolding);
        }

        if ($this->filterDepartment) {
            $query->where('e.department_id', $this->filterDepartment);
        }

        if ($this->filterDivision) {
            $query->where('e.division_id', $this->filterDivision);
        }

        return $query->orderBy('e.nama');
    }

    public function unblockEmployee(string $nip): void
    {
        DB::table('employees')
            ->where('nip', $nip)
            ->update(['attendance_note' => null]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Karyawan berhasil di-unblock'];
        $this->dispatch('scannerToast', $this->toast);
        $this->refreshData();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterHolding(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDivision(): void
    {
        $this->resetPage();
    }

    public function refreshData(): void
    {
        $this->loadSummary();
        $this->dispatch('data-refreshed');
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-monitoring')
            ->layout('components.sccr-layout');
    }
}
