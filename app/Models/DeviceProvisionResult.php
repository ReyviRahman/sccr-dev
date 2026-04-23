<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceProvisionResult extends Model
{
    protected $table = 'emp_device_provision_results';

    protected $guarded = [];

    protected $casts = [
        'request_payload_json' => 'array',
        'response_payload_json' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(DeviceProvisionJob::class, 'provision_job_id');
    }
}