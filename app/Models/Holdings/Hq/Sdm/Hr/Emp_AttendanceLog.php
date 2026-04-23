<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use App\Models\Holding;
use Illuminate\Database\Eloquent\Model;

class Emp_AttendanceLog extends Model
{
    protected $table = 'emp_attendance_logs';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'employee_nip',
        'holding_id',
        'device_id',
        'card_id',
        'attendance_policy_id',
        'shift_type_id',
        'work_date',
        'attendance_at',
        'attendance_type',
        'source_type',
        'latitude',
        'longitude',
        'accuracy_meter',
        'reference_latitude',
        'reference_longitude',
        'reference_radius_meter',
        'distance_meter',
        'is_within_geofence',
        'raw_payload',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_meter' => 'float',
        'reference_latitude' => 'float',
        'reference_longitude' => 'float',
        'reference_radius_meter' => 'float',
        'distance_meter' => 'float',
        'is_within_geofence' => 'boolean',
        'holding_id' => 'integer',
        'device_id' => 'integer',
        'card_id' => 'integer',
        'attendance_policy_id' => 'integer',
        'shift_type_id' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Emp_Employee::class, 'employee_nip', 'nip');
    }

    public function holding()
    {
        return $this->belongsTo(Holding::class, 'holding_id');
    }
}
