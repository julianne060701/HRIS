<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;
     protected $table = 'payrolls';
      protected $fillable = [
        'payroll_code',
        'title',
        'from_date',
        'to_date',
        'status',
    ];
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

}
