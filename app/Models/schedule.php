<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class schedule extends Model
{
    use HasFactory;

    protected $table = 'schedule';

    protected $fillable = [
        'shift',
        'desc',
        'xptd_time_in',
        'xptd_time_out',
        'xptd_brk_in',
        'xptd_brk_out',
        'wrkhrs',
        'stat',
        'date',
        'emp_id',

    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class); // Replace with actual model if needed
    }
}
