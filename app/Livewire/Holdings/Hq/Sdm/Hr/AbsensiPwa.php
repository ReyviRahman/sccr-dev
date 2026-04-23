<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Auth\AuthIdentity;
use App\Models\Holding;
use App\Models\Holdings\Hq\Sdm\Hr\Emp_AttendanceLog;
use App\Models\Holdings\Hq\Sdm\Hr\Emp_Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AbsensiPwa extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?Emp_Employee $employee = null;

    public ?Holding $holding = null;

    public array $todayLogs = [];

    public bool $canAbsen = false;

    public string $selectedHoldingId = '';

    public string $latitude = '';

    public string $longitude = '';

    public array $holdings = [];

    public string $attendanceType = '';

    public int $holdingId = 1;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Absensi', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->holdings = Holding::orderBy('name')->get()->toArray();

        $this->loadEmployeeData();
    }

    private function loadEmployeeData(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $identity = AuthIdentity::where('auth_user_id', $user->id)
            ->where('identity_type', 'employee')
            ->where('is_active', 1)
            ->first();

        if (! $identity) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data employee tidak ditemukan.'];

            return;
        }

        $this->employee = Emp_Employee::where('nip', $identity->identity_key)->first();

        if (! $this->employee) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Employee tidak ditemukan.'];

            return;
        }

        if ($this->employee->is_deleted) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Employee tidak aktif.'];

            return;
        }

        $this->canAbsen = (bool) $this->employee->is_attendance_required;
        $this->holdingId = $this->employee->holding_id;
        $this->holding = Holding::find($this->holdingId);

        $this->selectedHoldingId = (string) $this->holdingId;

        $this->loadTodayLogs();
    }

    private function loadTodayLogs(): void
    {
        if (! $this->employee) {
            return;
        }

        $this->todayLogs = Emp_AttendanceLog::where('employee_nip', $this->employee->nip)
            ->where('work_date', Carbon::today())
            ->orderBy('attendance_at', 'desc')
            ->get(['attendance_type', 'attendance_at'])
            ->toArray();
    }

    public function getLocation(): void
    {
        $this->dispatch('getBrowserLocation');
    }

    public function absen(string $type): void
    {
        if (! $this->canAbsen) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak wajib absensi.'];

            return;
        }

        if (! $this->employee) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Session expired. Silakan login ulang.'];

            return;
        }

        $this->attendanceType = $type;

        $this->validate([
            'selectedHoldingId' => 'required|integer|min:1',
        ]);

        $this->recordAttendance($type);
    }

    public function recordAttendance(string $type): void
    {
        $today = Carbon::today();
        $now = Carbon::now();
        $holdingId = (int) $this->selectedHoldingId;
        $holding = Holding::find($holdingId);

        if (! $holding) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Holding tidak valid.'];

            return;
        }

        try {
            DB::beginTransaction();

            $logId = DB::table('emp_attendance_logs')->insertGetId([
                'employee_nip' => $this->employee->nip,
                'holding_id' => $holdingId,
                'work_date' => $today,
                'attendance_at' => $now,
                'attendance_type' => strtoupper($type),
                'source_type' => 'pwa_gps',
                'latitude' => $this->latitude ?: null,
                'longitude' => $this->longitude ?: null,
                'reference_latitude' => $holding->latitude,
                'reference_longitude' => $holding->longitude,
                'reference_radius_meter' => $holding->radius_meter,
                'created_at' => $now,
            ]);

            $daily = DB::table('emp_attendance_daily')
                ->where('employee_nip', $this->employee->nip)
                ->where('work_date', $today)
                ->first();

            if (! $daily) {
                DB::table('emp_attendance_daily')->insert([
                    'employee_nip' => $this->employee->nip,
                    'holding_id' => $holdingId,
                    'work_date' => $today,
                    'attendance_policy_id' => 1,
                    'first_in_log_id' => $type === 'IN' ? $logId : null,
                    'first_in_at' => $type === 'IN' ? $now : null,
                    'last_out_log_id' => $type === 'OUT' ? $logId : null,
                    'last_out_at' => $type === 'OUT' ? $now : null,
                    'attendance_status' => 'present',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $updateData = ['updated_at' => $now];

                if ($type === 'IN' && ! $daily->first_in_at) {
                    $updateData['first_in_log_id'] = $logId;
                    $updateData['first_in_at'] = $now;
                }

                if ($type === 'OUT') {
                    $updateData['last_out_log_id'] = $logId;
                    $updateData['last_out_at'] = $now;
                }

                DB::table('emp_attendance_daily')
                    ->where('id', $daily->id)
                    ->update($updateData);
            }

            DB::commit();

            $message = 'Berhasil ABSENSI '.strtoupper($type);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => $message];

            $this->loadTodayLogs();
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Gagal menyimpan absensi.'];
        }
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-pwa')
            ->layout('components.sccr-layout');
    }
}
