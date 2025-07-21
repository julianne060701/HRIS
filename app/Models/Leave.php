<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
     public $timestamps = false;
     
    protected $fillable = [
        'employee_id',
        'date_start',
        'date_end',
        'leave_type_id', 
        'reason',
        'total_days',    
        'status',       
        'approved_by',   
        'approved_at',   
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the leave type associated with the leave.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Get the employee that owns the leave.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}