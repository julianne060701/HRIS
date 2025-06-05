<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DTR extends Model
{
    protected $table = 'dtr';

    // Define the fillable columns
    protected $fillable = [
        'employee_id',
        'transindate',
        'time_in',
        'transoutdate',
        'time_out',
        'created_at', 
        'updated_at'
    ];
    public function shift()
{
    return $this->belongsTo(Shift::class, 'shift_id');  // Assuming `shift_id` exists in 'dtr' table
}
}
