<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leavecredit extends Model
{
    protected $table = 'leave_credits';
    
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'all_leave',
        'rem_leave',
    ];
    
    public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id', 'employee_id'); 
}

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
