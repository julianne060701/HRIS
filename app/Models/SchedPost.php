<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedPost extends Model
{
    use HasFactory;

    protected $table = 'schedposts'; // match your migration

    protected $fillable = [
        'employee_id',  // FK to employees.id
        'date',
        'shift',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
