<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipDeduction extends Model
{
    protected $fillable = ['payslip_id', 'name', 'amount'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
