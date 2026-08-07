<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function setDateAttribute($value)
    {
    $this->attributes['date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function applications()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function getTotalTimeAttribute()
    {
        if (!$this->clock_out) {
            return null;
    }

        $minutes = \Carbon\Carbon::parse($this->clock_in)->diffInMinutes(\Carbon\Carbon::parse($this->clock_out));

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public function getTotalBreakTimeAttribute()
    {
        $breakMinutes = 0;
        foreach($this->breaks as $break){
            if (!$break->break_start || !$break->break_end) {
                continue;
            }
            $breakMinutes += \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
        }

        return sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
    }
}
