<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EmployeeSchedule extends Model
{
    use HasFactory;

    protected $table = 'employee_schedules';

    protected $fillable = [
        'employee_id',
        'date',
        'shift_code',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
