<?php

namespace App\Livewire\Employee;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EmployeeDashboard extends Component
{
    public array $employee = [];

    public string $periodStart = '';

    public string $periodEnd = '';

    public string $periodLabel = '';

    public array $todayAttendance = [];

    public array $periodAttendance = [];

    public array $mealOrders = [];

    public array $payrollDeductions = [];

    public array $budgetSummary = [];

    public bool $hasEmployee = false;

    public string $errorMessage = '';

    public function mount(): void
    {
        $this->loadEmployee();

        if ($this->hasEmployee) {
            $this->calculatePeriod();
            $this->loadTodayAttendance();
            $this->loadPeriodAttendance();
            $this->loadMealOrders();
            $this->loadPayrollDeductions();
            $this->loadBudgetSummary();
        }
    }

    public function refreshDashboardData(): void
    {
        if (! $this->hasEmployee) {
            return;
        }

        $this->calculatePeriod();
        $this->loadTodayAttendance();
        $this->loadPeriodAttendance();
        $this->loadMealOrders();
        $this->loadPayrollDeductions();
        $this->loadBudgetSummary();
    }

    private function loadEmployee(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->hasEmployee = false;
            $this->errorMessage = 'User not authenticated';

            return;
        }

        $employeeNip = DB::table('auth_identities')
            ->where('auth_user_id', $user->id)
            ->where('identity_type', 'employee')
            ->where('is_active', 1)
            ->value('identity_key');

        if (! $employeeNip) {
            $this->hasEmployee = false;
            $this->errorMessage = 'Employee data not found. Please contact HR.';

            return;
        }

        $emp = DB::table('employees')
            ->where('nip', $employeeNip)
            ->where('is_deleted', 0)
            ->first();

        if (! $emp) {
            $this->hasEmployee = false;
            $this->errorMessage = 'Employee record not found or inactive.';

            return;
        }

        $this->employee = [
            'nip' => $emp->nip,
            'nama' => $emp->nama,
            'holding_id' => $emp->holding_id,
            'department_id' => $emp->department_id,
            'division_id' => $emp->division_id,
            'job_title' => $emp->job_title,
            'daily_lunch_budget' => $emp->daily_lunch_budget ?? 0,
            'daily_snack_budget' => $emp->daily_snack_budget ?? 0,
            'is_company_lunch_eligible' => $emp->is_company_lunch_eligible ?? 0,
            'is_company_snack_eligible' => $emp->is_company_snack_eligible ?? 0,
        ];

        $this->hasEmployee = true;
    }

    private function calculatePeriod(): void
    {
        $now = Carbon::now();
        $nowMonth = (int) $now->format('n');
        $nowYear = (int) $now->format('Y');
        $nowDay = (int) $now->format('j');

        $endDay = 25;
        $startDay = 26;

        $startMonthNum = ($nowMonth == 1) ? 12 : $nowMonth - 1;
        $startYear = ($nowMonth == 1) ? $nowYear - 1 : $nowYear;

        $startMonthName = $this->getIndonesianMonthName($startMonthNum);
        $endMonthName = $this->getIndonesianMonthName($nowMonth);

        $this->periodStart = $startYear.'-'.str_pad($startMonthNum, 2, '0', STR_PAD_LEFT).'-26';
        $this->periodEnd = $nowYear.'-'.str_pad($nowMonth, 2, '0', STR_PAD_LEFT).'-25';

        $this->periodLabel = $startDay.' '.$startMonthName.' '.$startYear.' - '.$endDay.' '.$endMonthName.' '.$nowYear;
    }

    private function getIndonesianMonthName(int $month): string
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return $months[$month] ?? '';
    }

    private function getIndonesianDayName(string $date): string
    {
        $days = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];

        return $days[Carbon::parse($date)->format('D')] ?? Carbon::parse($date)->format('d/m/Y');
    }

    private function loadTodayAttendance(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $attendance = DB::table('emp_attendance_daily')
            ->where('employee_nip', $this->employee['nip'])
            ->where('work_date', $today)
            ->first();

        if ($attendance) {
            $firstIn = $attendance->first_in_at ? Carbon::parse($attendance->first_in_at) : null;
            $lastOut = $attendance->last_out_at ? Carbon::parse($attendance->last_out_at) : null;

            $workMinutes = 0;
            if ($firstIn && $lastOut) {
                $workMinutes = $firstIn->diffInMinutes($lastOut);
            }

            $this->todayAttendance = [
                'work_date' => $attendance->work_date,
                'first_in_at' => $attendance->first_in_at,
                'last_out_at' => $attendance->last_out_at,
                'attendance_status' => $attendance->attendance_status ?? 'present',
                'is_late' => $attendance->is_late ?? 0,
                'late_minutes' => $attendance->late_minutes ?? 0,
                'is_early_out' => $attendance->is_early_out ?? 0,
                'early_out_minutes' => $attendance->early_out_minutes ?? 0,
                'work_minutes' => $workMinutes,
                'lunch_budget' => $attendance->lunch_budget ?? 0,
                'snack_budget' => $attendance->snack_budget ?? 0,
                'has_attendance' => true,
            ];
        } else {
            $this->todayAttendance = [
                'work_date' => $today,
                'first_in_at' => null,
                'last_out_at' => null,
                'attendance_status' => 'none',
                'is_late' => 0,
                'late_minutes' => 0,
                'is_early_out' => 0,
                'early_out_minutes' => 0,
                'work_minutes' => 0,
                'lunch_budget' => 0,
                'snack_budget' => 0,
                'has_attendance' => false,
            ];
        }
    }

    private function loadPeriodAttendance(): void
    {
        $this->periodAttendance = DB::table('emp_attendance_daily')
            ->where('employee_nip', $this->employee['nip'])
            ->whereBetween('work_date', [$this->periodStart, $this->periodEnd])
            ->orderByDesc('work_date')
            ->get()
            ->map(function ($item) {
                return [
                    'work_date' => $item->work_date,
                    'day_name' => $this->getIndonesianDayName($item->work_date),
                    'first_in_at' => $item->first_in_at,
                    'last_out_at' => $item->last_out_at,
                    'attendance_status' => $item->attendance_status ?? 'present',
                    'is_late' => $item->is_late ?? 0,
                    'late_minutes' => $item->late_minutes ?? 0,
                    'is_early_out' => $item->is_early_out ?? 0,
                    'early_out_minutes' => $item->early_out_minutes ?? 0,
                    'work_minutes' => $item->work_minutes ?? 0,
                ];
            })
            ->toArray();
    }

    private function loadMealOrders(): void
    {
        $this->mealOrders = DB::table('emp_meal_orders')
            ->where('employee_nip', $this->employee['nip'])
            ->whereBetween('work_date', [$this->periodStart, $this->periodEnd])
            ->orderByDesc('ordered_at')
            ->get()
            ->map(function ($item) {
                return [
                    'work_date' => $item->work_date,
                    'ordered_at' => $item->ordered_at,
                    'day_name' => $this->getIndonesianDayName($item->work_date),
                    'meal_type' => $item->meal_type,
                    'source' => $item->source,
                    'base_amount' => $item->base_amount ?? 0,
                    'extra_amount' => $item->extra_amount ?? 0,
                    'total_amount' => $item->total_amount ?? 0,
                    'company_budget_amount' => $item->company_budget_amount ?? 0,
                    'employee_over_amount' => $item->employee_over_amount ?? 0,
                    'settlement_method' => $item->settlement_method,
                    'settlement_status' => $item->settlement_status,
                    'notes' => $item->notes,
                ];
            })
            ->toArray();
    }

    private function loadPayrollDeductions(): void
    {
        $this->payrollDeductions = DB::table('emp_payroll_deductions')
            ->where('employee_nip', $this->employee['nip'])
            ->whereBetween('created_at', [$this->periodStart.' 00:00:00', $this->periodEnd.' 23:59:59'])
            ->where('source_type', 'meal_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'created_at' => $item->created_at,
                    'description' => $item->description,
                    'source_type' => $item->source_type,
                    'amount' => $item->amount ?? 0,
                    'status' => $item->status,
                ];
            })
            ->toArray();
    }

    private function loadBudgetSummary(): void
    {
        $attendanceInPeriod = DB::table('emp_attendance_daily')
            ->where('employee_nip', $this->employee['nip'])
            ->whereBetween('work_date', [$this->periodStart, $this->periodEnd])
            ->count();

        $mealOrdersTotal = DB::table('emp_meal_orders')
            ->where('employee_nip', $this->employee['nip'])
            ->whereBetween('work_date', [$this->periodStart, $this->periodEnd])
            ->selectRaw('SUM(CASE WHEN meal_type = "lunch" THEN total_amount ELSE 0 END) as total_lunch, SUM(CASE WHEN meal_type = "snack" THEN total_amount ELSE 0 END) as total_snack')
            ->first();

        $deductionsTotal = DB::table('emp_payroll_deductions')
            ->where('employee_nip', $this->employee['nip'])
            ->whereBetween('created_at', [$this->periodStart.' 00:00:00', $this->periodEnd.' 23:59:59'])
            ->where('source_type', 'meal_order')
            ->selectRaw('SUM(amount) as total_deduction')
            ->first();

        $this->budgetSummary = [
            'work_days' => $attendanceInPeriod,
            'total_lunch_budget' => $this->employee['daily_lunch_budget'] * $attendanceInPeriod,
            'total_lunch_used' => $mealOrdersTotal->total_lunch ?? 0,
            'total_snack_budget' => $this->employee['daily_snack_budget'] * $attendanceInPeriod,
            'total_snack_used' => $mealOrdersTotal->total_snack ?? 0,
            'total_meal_orders' => count($this->mealOrders),
            'total_deductions' => $deductionsTotal->total_deduction ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.employee.employee-dashboard')
            ->layout('components.sccr-layout');
    }
}
