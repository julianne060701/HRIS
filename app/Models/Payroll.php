<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    // Define the table associated with the model if different from the pluralized model name
    // protected $table = 'payrolls';

    // If you have specific columns for this model
    protected $fillable = [
        'employee_id',
        'payroll_code',
        'title',
        'from_date',
        'to_date',
        'status',
    ];
}
