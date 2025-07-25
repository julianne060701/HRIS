<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loans extends Model
{
    protected $fillable = [
        'employee_id',
        'loan_type',
        'loan_amount',
        'loan_date_str',
        'loan_date_end',
        'payment_counter',
        'monthly_payment',
        'loan_status',
        
    ];
}
