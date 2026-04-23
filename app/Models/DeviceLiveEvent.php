<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLiveEvent extends Model
{
    protected $table = 'emp_device_live_events';

    protected $guarded = [];

    protected $casts = [
        'event_time_device' => 'datetime',
        'event_time_server' => 'datetime',
        'is_live' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inbox()
    {
        return $this->belongsTo(DeviceEventInbox::class, 'inbox_id');
    }
}