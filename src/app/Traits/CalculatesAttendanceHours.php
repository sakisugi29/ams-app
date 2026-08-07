<?php

namespace App\Traits;

trait CalculatesAttendanceHours
{
    private function calculateBreakAndWorkHours($attendances)
    {
        return $attendances->map(function ($attendance) {
            $breakMinutes = $attendance->breaks->sum(function ($break) {
                if (!$break->break_start || !$break->break_end) {
                    return 0;
                }
                return \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
            });

            $attendance->break_total = $breakMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                : '';

            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = \Carbon\Carbon::parse($attendance->clock_in)
                    ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $breakMinutes;

                $attendance->work_hours = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
            } else {
                $attendance->work_hours = '';
            }

            return $attendance;
        });
    }
}