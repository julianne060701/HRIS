<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedPost extends Model
{
   use HasFactory;

    protected $table = 'schedpost';

    protected $fillable = [
        'id',
        'emp_id',
        'schedule',
        'created_at',
        'updated_at',
    ];
}
