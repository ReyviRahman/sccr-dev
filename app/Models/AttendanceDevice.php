<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDevice extends Model
{
    protected $table = 'emp_attendance_devices';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'provision_is_enabled' => 'boolean',
        'unit_id' => 'integer',
        'last_seen_at' => 'datetime',
        'provision_last_success_at' => 'datetime',
        'provision_last_error_at' => 'datetime',
    ];

    public function getProvisionPasswordDecryptedAttribute(): ?string
    {
        if (empty($this->provision_password_ciphertext)) {
            return null;
        }

        try {
            return decrypt($this->provision_password_ciphertext);
        } catch (\Throwable $e) {
            return null;
        }
    }
}