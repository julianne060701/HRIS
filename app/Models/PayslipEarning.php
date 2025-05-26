<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipEarning extends Model
{
    protected $fillable = ['payslip_id', 'name', 'amount'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
