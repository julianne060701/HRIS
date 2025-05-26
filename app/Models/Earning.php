<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    protected $table = 'payslip_earnings';

    protected $fillable = [
        'payslip_id',
        'name',
        'amount',
    ];
}
