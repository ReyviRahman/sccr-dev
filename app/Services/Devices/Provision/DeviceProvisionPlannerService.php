<?php

namespace App\Services\Devices\Provision;

use App\Models\AttendanceDevice;
use App\Models\DeviceProvisionJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeviceProvisionPlannerService
{
    public function resolveTargetDevices(DeviceProvisionJob $job): Collection
    {
        $query = AttendanceDevice::query()
            ->where('is_active', 1)
            ->where('provision_is_enabled', 1)
            ->where('device_type', 'biometric_terminal');

        return match ($job->target_scope) {
            'single_device' => $query
                ->where('code', $job->target_device_code)
                ->get(),

            'holding_devices' => $query
                ->where('holding_id', $job->target_holding_id)
                ->get(),

            'assignment_devices' => $this->resolveAssignmentDevices($job, $query),

            'all_devices' => $query->get(),

            default => collect(),
        };
    }

    protected function resolveAssignmentDevices(DeviceProvisionJob $job, $baseQuery): Collection
    {
        $companyDbs = DB::table('emp_employee_assignments')
            ->where('employee_nip', $job->employee_nip)
            ->where('assignment_status', 'active')
            ->selectRaw('COALESCE(attendance_company_db, company_db) as target_company_db')
            ->pluck('target_company_db')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($companyDbs)) {
            return (clone $baseQuery)
                ->whereIn('company_db', $companyDbs)
                ->get();
        }

        return (clone $baseQuery)
            ->where('holding_id', $job->target_holding_id)
            ->get();
    }
}