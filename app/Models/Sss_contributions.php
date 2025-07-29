<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sss_contributions extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_range_from',
        'salary_range_to',
        'reg_ee_share',
        'reg_er_share',
        'ec_er_share',
        'wisp_ee_share',
        'wisp_er_share',
        // If 'total_contribution' is a column you plan to save via mass assignment, add it here too
    ];

    protected $casts = [
        'salary_range_from' => 'float',
        'salary_range_to' => 'float',
        'reg_ee_share' => 'float',
        'reg_er_share' => 'float',
        'ec_er_share' => 'float',
        'wisp_ee_share' => 'float',
        'wisp_er_share' => 'float',
        'total_contribution' => 'float',
    ];

}
