<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'user1@example.com')->first();

        for ($i = 5; $i >= 1; $i--) {
            $targetMonth = now()->startOfMonth()->subMonths($i);
            $endOfMonth = $targetMonth->copy()->endOfMonth();
            $weekdayCount = 0;
            $date = $targetMonth->copy();

            while ($weekdayCount < 15 && $date->lte($endOfMonth)) {
                if ($date->isWeekday()) {
                    $clockIn = $date->copy()->setTime(9, 0, 0);
                    $clockOut = $date->copy()->setTime(18, 0, 0);

                    $attendanceRecord = AttendanceRecord::create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => 'finished',
                    ]);

                    BreakTime::create([
                        'attendance_record_id' => $attendanceRecord->id,
                        'break_start' => $date->copy()->setTime(12, 0, 0),
                        'break_end' => $date->copy()->setTime(13, 0, 0),
                    ]);

                    $weekdayCount++;
                }
                $date->addDay();
            }
        }

        $targetMonth = now()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();
        $weekdayCount = 0;
        $date = $targetMonth->copy();

        while ($weekdayCount < 17 && $date->lte($endOfMonth)) {
            if ($date->isWeekday()) {
                if ($weekdayCount < 10){
                    $clockIn = $date->copy()->setTime(9, 0, 0);
                    $clockOut = $date->copy()->setTime(18, 0, 0);
                }
                elseif($weekdayCount < 13){
                    $clockIn = $date->copy()->setTime(9, 0, 0);
                    $clockOut = $date->copy()->setTime(20, 0, 0);
                }
                elseif($weekdayCount < 15){
                    $clockIn = $date->copy()->setTime(9, 30, 0);
                    $clockOut = $date->copy()->setTime(18, 0, 0);
                }
                elseif($weekdayCount < 16){
                    $clockIn = $date->copy()->setTime(9, 0, 0);
                    $clockOut = $date->copy()->setTime(17, 0, 0);
                }
                elseif($weekdayCount < 17){
                    $clockIn = $date->copy()->setTime(8, 0, 0);
                    $clockOut = $date->copy()->setTime(21, 0, 0);
                }

            $attendanceRecord = AttendanceRecord::create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => 'finished',
                    ]);

                    BreakTime::create([
                        'attendance_record_id' => $attendanceRecord->id,
                        'break_start' => $date->copy()->setTime(12, 0, 0),
                        'break_end' => $date->copy()->setTime(13, 0, 0),
                    ]);

            $weekdayCount++;
            }
            $date->addDay();
        }
    }
}
