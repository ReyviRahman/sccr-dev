<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use Illuminate\Database\Eloquent\Model;

class Emp_EmployeeCard extends Model
{
    protected $table = 'emp_employee_cards';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'employee_nip',
        'card_uid',
        'card_number',
        'is_primary',
        'is_active',
        'issued_at',
        'expired_at',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'issued_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Emp_Employee::class, 'employee_nip', 'nip');
    }
}
