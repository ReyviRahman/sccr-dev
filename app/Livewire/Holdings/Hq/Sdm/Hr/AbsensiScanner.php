<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AbsensiScanner extends Component
{
    public array $toast = [
        'show' => false,
        'type' => 'success',
        'message' => '',
    ];

    public int $toastSeq = 0;

    public string $nipInput = '';
    public string $lastEmployeeName = '';
    public string $lastAttendanceType = '';
    public string $lastTime = '';
    public string $lastStatus = '';

    protected string $scannerDeviceCode = 'HQ-SCAN-01';

    /**
     * 3 scan berhasil dalam 5 menit masih boleh.
     * Scan ke-4 langsung block.
     */
    protected int $spamWindowMinutes = 5;
    protected int $spamMaxSuccessBeforeBlock = 3;

    /**
     * Guard anti double submit di server
     */
    public string $lastSubmittedNip = '';
    public int $lastSubmittedAtTs = 0;

    public function mount(): void
    {
        $this->dispatch('focusInput');
    }

    public function updatedNipInput(string $value): void
    {
        // sengaja kosong
        // auto process ditangani JS scanner
    }

    public function submitNip(): void
    {
        $normalized = $this->normalizeNip($this->nipInput);

        if ($normalized === '') {
            $this->dispatch('focusInput');
            return;
        }

        $this->processAttendance($normalized);
    }

    private function normalizeNip(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?? '';
    }

    private function getScannerDevice(): ?object
    {
        return DB::table('emp_attendance_devices')
            ->where('code', $this->scannerDeviceCode)
            ->where('is_active', 1)
            ->first();
    }

    private function getEmployeeByNip(string $nip): ?object
    {
        return DB::table('employees')
            ->where('nip', $nip)
            ->first();
    }

    private function getActiveEmployeeIdentity(string $nip): ?object
    {
        return DB::table('auth_identities')
            ->where('identity_type', 'employee')
            ->where('identity_key', $nip)
            ->where('is_active', 1)
            ->first();
    }

    private function getActiveAssignment(string $nip): ?object
    {
        return DB::table('emp_employee_assignments')
            ->where('employee_nip', $nip)
            ->where('assignment_status', 'active')
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->first();
    }

    private function resolveDefaultPolicyId(): int
    {
        $policy = DB::table('emp_attendance_policies')
            ->where('code', 'REGULAR')
            ->where('is_active', 1)
            ->first();

        if ($policy) {
            return (int) $policy->id;
        }

        $fallback = DB::table('emp_attendance_policies')
            ->orderBy('id')
            ->first();

        return $fallback ? (int) $fallback->id : 1;
    }

    private function pushToast(string $type, string $message): void
    {
        $this->toastSeq++;

        $this->toast = [
            'show' => true,
            'type' => $type,
            'message' => $message,
        ];

        $this->dispatch('scannerToast', $this->toast);
        $this->dispatch('focusInput');
    }

    public function processAttendance(string $searchNip): void
    {
        $searchNip = $this->normalizeNip($searchNip);

        if ($searchNip === '') {
            $this->dispatch('focusInput');
            return;
        }

        /**
         * Guard anti double submit server-side
         */
        $nowTs = time();
        if (
            $this->lastSubmittedNip === $searchNip
            && ($nowTs - $this->lastSubmittedAtTs) <= 1
        ) {
            return;
        }

        $this->lastSubmittedNip = $searchNip;
        $this->lastSubmittedAtTs = $nowTs;

        $this->nipInput = '';
        $this->dispatch('resetInput');

        $device = $this->getScannerDevice();
        if (! $device) {
            $this->pushToast('error', 'Device scanner HQ-SCAN-01 belum dikonfigurasi atau tidak aktif.');
            return;
        }

        /**
         * STEP 1
         * Cari employee di central registry
         */
        $employee = $this->getEmployeeByNip($searchNip);
        if (! $employee) {
            $this->pushToast('error', 'Data karyawan tidak ditemukan. NIP: ' . $searchNip);
            return;
        }

        /**
         * STEP 2
         * Validasi employee
         */
        if ((int) ($employee->is_deleted ?? 0) === 1) {
            $this->pushToast('error', 'Karyawan tidak aktif.');
            return;
        }

        if (in_array((string) ($employee->employee_status ?? ''), ['RESIGN', 'TERMINATED'], true)) {
            $this->pushToast('error', 'Karyawan tidak aktif. Status: ' . $employee->employee_status);
            return;
        }

        if ((int) ($employee->is_attendance_required ?? 1) !== 1) {
            $this->pushToast('warning', 'Karyawan tidak wajib absensi.');
            return;
        }

        if (($employee->attendance_note ?? null) === 'Block employee attendance') {
            $this->pushToast('error', 'Absensi diblok. Hubungi HR.');
            return;
        }

        /**
         * STEP 3
         * Validasi identity aktif
         */
        $identity = $this->getActiveEmployeeIdentity($searchNip);
        if (! $identity) {
            $this->pushToast('error', 'Identity employee tidak ditemukan / tidak aktif di SSO.');
            return;
        }

        /**
         * STEP 4
         * Validasi assignment aktif
         */
        $assignment = $this->getActiveAssignment($searchNip);
        if (! $assignment) {
            $this->pushToast('error', 'Tidak ada assignment aktif untuk karyawan ini.');
            return;
        }

        if ((int) ($assignment->attendance_required ?? 1) !== 1) {
            $this->pushToast('warning', 'Karyawan tidak wajib absensi berdasarkan assignment aktif.');
            return;
        }

        $employeeData = [
            'nip' => (string) $employee->nip,
            'nama' => (string) $employee->nama,
            'employee' => $employee,
            'identity' => $identity,
            'assignment' => $assignment,
            'device' => $device,
        ];

        $this->recordAttendance($employeeData);
    }

    private function recordAttendance(array $employeeData): void
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $employee = $employeeData['employee'];
        $assignment = $employeeData['assignment'];
        $device = $employeeData['device'];

        $employeeNip = (string) $employeeData['nip'];
        $employeeName = (string) $employeeData['nama'];

        $deviceId = (int) $device->id;
        $scanHoldingId = (int) $device->holding_id;

        $isMultiLocationAllowed = property_exists($employee, 'is_multi_location_attendance_allowed')
            ? ((int) $employee->is_multi_location_attendance_allowed === 1)
            : false;

        /**
         * RULE LOCK:
         * kalau sudah ada 3 log berhasil dalam 5 menit,
         * scan ke-4 langsung block
         */
        $previousLogs = DB::table('emp_attendance_logs')
            ->where('employee_nip', $employeeNip)
            ->where('work_date', $today)
            ->where('attendance_at', '>=', $now->copy()->subMinutes($this->spamWindowMinutes))
            ->count();

        if ($previousLogs >= $this->spamMaxSuccessBeforeBlock) {
            DB::table('employees')
                ->where('nip', $employeeNip)
                ->update([
                    'attendance_note' => 'Block employee attendance',
                    'updated_at' => $now,
                ]);

            $this->pushToast(
                'error',
                'Absensi diblok. Terdeteksi scan ke-4 dalam 5 menit. Silakan menghadap HR untuk unlock.'
            );
            return;
        }

        try {
            DB::beginTransaction();

            $lastLog = DB::table('emp_attendance_logs')
                ->where('employee_nip', $employeeNip)
                ->where('work_date', $today)
                ->orderByDesc('attendance_at')
                ->first();

            /**
             * Multi-location rule
             */
            if (
                $lastLog
                && (int) $lastLog->holding_id !== $scanHoldingId
                && ! $isMultiLocationAllowed
            ) {
                DB::rollBack();

                $this->pushToast(
                    'error',
                    'Karyawan ini tidak diizinkan absensi lintas lokasi pada hari yang sama.'
                );
                return;
            }

            /**
             * Toggle IN / OUT
             */
            $attendanceType = (! $lastLog || $lastLog->attendance_type === 'OUT') ? 'IN' : 'OUT';

            /**
             * Insert raw log
             */
            $logId = DB::table('emp_attendance_logs')->insertGetId([
                'employee_nip' => $employeeNip,
                'holding_id' => $scanHoldingId,
                'device_id' => $deviceId,
                'work_date' => $today,
                'attendance_at' => $now,
                'attendance_type' => $attendanceType,
                'source_type' => 'card_scan',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /**
             * Daily
             * Rule:
             * - first IN hanya sekali
             * - last OUT selalu ditimpa
             */
            $daily = DB::table('emp_attendance_daily')
                ->where('employee_nip', $employeeNip)
                ->where('work_date', $today)
                ->first();

            $assignmentPolicyId = property_exists($assignment, 'attendance_policy_id_override')
                ? $assignment->attendance_policy_id_override
                : null;

            $policyId = (int) (
                $daily->attendance_policy_id
                ?? $assignmentPolicyId
                ?? $this->resolveDefaultPolicyId()
            );

            $policy = DB::table('emp_attendance_policies')->find($policyId);

            if (! $daily) {
                $insertData = [
                    'employee_nip' => $employeeNip,
                    'holding_id' => $scanHoldingId,
                    'work_date' => $today,
                    'attendance_policy_id' => $policyId,
                    'attendance_status' => 'present',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($attendanceType === 'IN') {
                    $insertData['first_in_log_id'] = $logId;
                    $insertData['first_in_at'] = $now;

                    if ($policy && ! empty($policy->default_checkin_start)) {
                        $checkinStart = Carbon::parse($today->format('Y-m-d') . ' ' . $policy->default_checkin_start);
                        $graceMinutes = (int) ($policy->grace_late_minutes ?? 0);
                        $lateThreshold = $checkinStart->copy()->addMinutes($graceMinutes);

                        if ($now->gt($lateThreshold)) {
                            $insertData['is_late'] = 1;
                            $insertData['late_minutes'] = $checkinStart->diffInMinutes($now);
                        }
                    }
                }

                if ($attendanceType === 'OUT') {
                    $insertData['last_out_log_id'] = $logId;
                    $insertData['last_out_at'] = $now;
                }

                DB::table('emp_attendance_daily')->insert($insertData);
            } else {
                $updateData = [
                    'attendance_policy_id' => $policyId,
                    'updated_at' => $now,
                ];

                if ($attendanceType === 'IN' && empty($daily->first_in_at)) {
                    $updateData['first_in_log_id'] = $logId;
                    $updateData['first_in_at'] = $now;

                    if ($policy && ! empty($policy->default_checkin_start)) {
                        $checkinStart = Carbon::parse($today->format('Y-m-d') . ' ' . $policy->default_checkin_start);
                        $graceMinutes = (int) ($policy->grace_late_minutes ?? 0);
                        $lateThreshold = $checkinStart->copy()->addMinutes($graceMinutes);

                        if ($now->gt($lateThreshold)) {
                            $updateData['is_late'] = 1;
                            $updateData['late_minutes'] = $checkinStart->diffInMinutes($now);
                        }
                    }
                }

                if ($attendanceType === 'OUT') {
                    $updateData['last_out_log_id'] = $logId;
                    $updateData['last_out_at'] = $now;

                    $firstIn = ! empty($daily->first_in_at)
                        ? Carbon::parse($daily->first_in_at)
                        : null;

                    if ($firstIn) {
                        $updateData['work_minutes'] = $firstIn->diffInMinutes($now);

                        if ($policy && ! empty($policy->default_checkout_end)) {
                            $checkoutEnd = Carbon::parse($today->format('Y-m-d') . ' ' . $policy->default_checkout_end);
                            $graceEarly = (int) ($policy->grace_early_out_minutes ?? 0);
                            $earlyThreshold = $checkoutEnd->copy()->subMinutes($graceEarly);

                            if ($now->lt($earlyThreshold)) {
                                $updateData['is_early_out'] = 1;
                                $updateData['early_out_minutes'] = $now->diffInMinutes($checkoutEnd);
                            } elseif ($now->gt($checkoutEnd)) {
                                $updateData['overtime_minutes'] = $checkoutEnd->diffInMinutes($now);
                            }
                        }
                    }
                }

                DB::table('emp_attendance_daily')
                    ->where('id', $daily->id)
                    ->update($updateData);
            }

            DB::commit();

            $this->lastEmployeeName = $employeeName;
            $this->lastAttendanceType = $attendanceType;
            $this->lastTime = $now->format('H:i:s');
            $this->lastStatus = 'success';

            $message = $attendanceType === 'IN'
                ? "Selamat Datang - {$employeeName} - Anda BERHASIL melakukan Absensi Masuk"
                : "Sampai Jumpa, hati-hati dijalan - {$employeeName} - Anda BERHASIL melakukan Absensi Pulang";

            $this->pushToast('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->pushToast('error', 'Gagal: ' . substr($e->getMessage(), 0, 120));
        }
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-scanner')
            ->layout('components.sccr-layout');
    }
}