<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceEventInbox extends Model
{
    protected $table = 'emp_device_event_inbox';

    protected $guarded = [];

    protected $casts = [
        'event_time_device' => 'datetime',
        'event_time_server' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function liveEvents()
    {
        return $this->hasMany(DeviceLiveEvent::class, 'inbox_id');
    }
}