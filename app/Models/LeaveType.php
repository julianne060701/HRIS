<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',

        
    ];

    /**
     * Get the leaves of this type.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class, 'leave_type', 'name');
    }

    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }
}