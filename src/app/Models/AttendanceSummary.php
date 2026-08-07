<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_month',
        'total_working_minutes',
        'total_overtime_minutes',
        'average_working_minutes',
        'late_count',
        'early_leave_count',
        'long_work_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
