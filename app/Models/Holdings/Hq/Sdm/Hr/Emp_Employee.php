<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use App\Models\Department;
use App\Models\Division;
use App\Models\Holding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emp_Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = [
        'tanggal_join' => 'date',
        'tanggal_lahir' => 'date',
        'tanggal_keluar' => 'date',
        'holding_id' => 'integer',
        'department_id' => 'integer',
        'division_id' => 'integer',
        'position_id' => 'integer',
        'job_title_id' => 'integer',
        'daily_lunch_budget' => 'decimal:2',
        'daily_snack_budget' => 'decimal:2',
        'is_attendance_required' => 'boolean',
        'is_multi_location_attendance_allowed' => 'boolean',
        'is_company_snack_eligible' => 'boolean',
        'is_company_lunch_eligible' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    protected $fillable = [
        'nip',
        'person_uuid',
        'source_company_db',
        'home_company_db',
        'gelar_depan',
        'nama',
        'gelar_belakang',

        'holding_id',
        'department_id',
        'division_id',
        'org_scope_level',
        'position_id',
        'job_title_id',
        'job_title',

        'tanggal_join',
        'employee_status',
        'jenis_keluar',
        'tanggal_keluar',
        'employee_code',

        'alamat_asal',
        'kota_asal',
        'alamat_domisili',
        'kota_domisili',

        'jenis_kelamin',
        'status_perkawinan',
        'agama',
        'gol_darah',

        'tempat_lahir',
        'tanggal_lahir',

        'pendidikan',
        'jurusan',

        'email',
        'no_hp',
        'no_ektp',
        'npwp',
        'kis',
        'bpjs_tk',

        'no_rekening',
        'pemilik_rekening',
        'nama_bank',

        'is_attendance_required',
        'is_multi_location_attendance_allowed',
        'attendance_note',
        'is_company_snack_eligible',
        'is_company_lunch_eligible',
        'daily_lunch_budget',
        'daily_snack_budget',
        'meal_deduction_method',

        'is_deleted',

        // optional:
        // 'foto',
        // 'employee_finger',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class, 'holding_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function position()
    {
        return $this->belongsTo(Emp_Position::class, 'position_id');
    }

    public function jobTitleMaster()
    {
        return $this->belongsTo(Emp_JobTitle::class, 'job_title_id');
    }

    public function jobTitles()
    {
        return $this->belongsToMany(Emp_JobTitle::class, 'emp_employee_job_title', 'employee_nip', 'job_title_id')
            ->withPivot(['holding_id'])
            ->withTimestamps();
    }

    public function cards()
    {
        return $this->hasMany(Emp_EmployeeCard::class, 'employee_nip', 'nip');
    }

    public function activeCards()
    {
        return $this->hasMany(Emp_EmployeeCard::class, 'employee_nip', 'nip')->where('is_active', 1);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(Emp_AttendanceLog::class, 'employee_nip', 'nip');
    }
}
