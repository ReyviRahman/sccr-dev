<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceProvisionJob extends Model
{
    protected $table = 'emp_device_provision_jobs';

    protected $guarded = [];

    protected $casts = [
        'payload_json' => 'array',
        'last_attempt_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function results()
    {
        return $this->hasMany(DeviceProvisionResult::class, 'provision_job_id');
    }
}