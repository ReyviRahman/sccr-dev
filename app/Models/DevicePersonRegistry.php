<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevicePersonRegistry extends Model
{
    protected $table = 'emp_device_person_registry';

    protected $guarded = [];

    protected $casts = [
        'remote_exists' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_error_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }
}