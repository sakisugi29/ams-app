<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;


class AttendanceReportController extends Controller
{
    public function index()
    {
        $startDate = now()->startOfMonth()->subMonths(5);
        $endDate = now()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id',auth()->id())
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $stats = $this->calculateWorkStats($attendanceRecords);

        $totalWorkTimeFormatted = $this->formatMinutesToHM($stats['totalWorkMinutes']);
        $totalOvertimeFormatted = $this->formatMinutesToHM($stats['totalOvertimeMinutes']);
        $averageWorkTimeFormatted = $this->formatMinutesToHM($stats['averageWorkMinutes']);

        //月次推移//
        $monthlyData = collect(range(5, 0))->map(function ($i) {
            $targetMonth = now()->startOfMonth()->subMonths($i);
            $endOfMonth = $targetMonth->copy()->endOfMonth();

            $attendanceRecords = AttendanceRecord::with('breaks')
                ->where('user_id', auth()->id())
                ->whereBetween('date', [$targetMonth, $endOfMonth])
                ->get();

            $stats = $this->calculateWorkStats($attendanceRecords);

            return [
                'month' => $targetMonth->format('Y-m'),
                'work' => $this->formatMinutesToHM($stats['totalWorkMinutes']),
                'overtime' => $this->formatMinutesToHM($stats['totalOvertimeMinutes']),
            ];
        })->values()->all();

        //異常探知//
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();

        $thisMonthRecords = AttendanceRecord::with('breaks')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$thisMonthStart, $thisMonthEnd])
            ->get();

        $anomalies = $this->detectAnomalies($thisMonthRecords);


        return view('attendance.summary',compact('totalWorkTimeFormatted','totalOvertimeFormatted','averageWorkTimeFormatted','monthlyData','anomalies'));
    }

    private function formatMinutesToHM($minutes){
        $hours = intdiv($minutes,60);
        $mins = $minutes % 60;
        return $hours . 'h' . $mins .'m';
    }

    private function calculateWorkStats($attendanceRecords){
        $validRecords = $attendanceRecords->filter(fn($record) => $record->clock_out);

        $totalWorkMinutes = $validRecords->sum(fn($record) => $this->calculateDailyWorkMinutes($record));
        $countedDays = $validRecords->count();

        $totalOvertimeMinutes = $validRecords->sum(function ($record) {
            $dailyWorkMinutes = $this->calculateDailyWorkMinutes($record);
            return $dailyWorkMinutes > 480 ? $dailyWorkMinutes - 480 : 0;
        });

        $averageWorkMinutes = $countedDays > 0 ? $totalWorkMinutes / $countedDays : 0;

        return[
            'totalWorkMinutes' => $totalWorkMinutes,
            'totalOvertimeMinutes' => $totalOvertimeMinutes,
            'averageWorkMinutes' => $averageWorkMinutes,
        ];
    }

    private function calculateDailyWorkMinutes($record){
        $work = \Carbon\Carbon::parse($record->clock_in)->diffInMinutes(\Carbon\Carbon::parse($record->clock_out));

        $breakMinutes = $record->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) {
                return 0;
            }
            return \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
        });

        return $work - $breakMinutes;
    }

    private function detectAnomalies($attendanceRecords){
        $validRecords = $attendanceRecords->filter(fn($record) => $record->clock_out);

        $lateCount = $validRecords->filter(function ($record) {
            $startTime = \Carbon\Carbon::parse($record->date)->setTime(9, 0, 0);
            $clockInTime = \Carbon\Carbon::parse(\Carbon\Carbon::parse($record->date)->format('Y-m-d') . ' ' . $record->clock_in);
            return $clockInTime->isAfter($startTime);
        })->count();

        $earlyLeaveCount = $validRecords->filter(function ($record) {
            $endTime = \Carbon\Carbon::parse($record->date)->setTime(18, 0, 0);
            $clockOutTime = \Carbon\Carbon::parse(\Carbon\Carbon::parse($record->date)->format('Y-m-d') . ' ' . $record->clock_out);
            return $clockOutTime->isBefore($endTime);
        })->count();

        $overworkCount = $validRecords->filter(function ($record) {
            return $this->calculateDailyWorkMinutes($record) > 600;
        })->count();

        return[
            'lateCount' => $lateCount,
            'earlyLeaveCount' => $earlyLeaveCount,
            'overworkCount' => $overworkCount,
        ];
    }
}