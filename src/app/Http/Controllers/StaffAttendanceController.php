<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Traits\CalculatesAttendanceHours;

class StaffAttendanceController extends Controller
{
    use CalculatesAttendanceHours;

    public function index()
    {
        $users = User::where('role', '!=', 'admin')->get();

        return view('admin.staff-list',compact('users'));
    }



    public function list($id, Request $request)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = \Carbon\Carbon::parse($month);

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendanceRecords = $this->calculateBreakAndWorkHours(
            AttendanceRecord::with('breaks')
                ->where('user_id', $user->id)
                ->whereYear('date', $currentMonth->year)
                ->whereMonth('date', $currentMonth->month)
                ->get()
                )->keyBy(fn($attendance) => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'));

        $attendances = collect(range(1, $currentMonth->daysInMonth))->map(function ($day) use ($currentMonth, $attendanceRecords, $user) {
            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
            return $attendanceRecords->get($date, (object)[
                'id' => null,
                'date' => $date,
                'user_id' => $user->id,
                'clock_in' => null,
                'clock_out' => null,
                'break_total' => '',
                'work_hours' => '',
            ]);
        });

        return view('admin.staff-attendance-list', compact('user', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function exportCsv($id, Request $request)
    {
        $user = User::findOrFail($id);
        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = \Carbon\Carbon::parse($month);

        $attendanceRecords = $this->calculateBreakAndWorkHours(
            AttendanceRecord::with('breaks')
                ->where('user_id', $id)
                ->whereYear('date', $currentMonth->year)
                ->whereMonth('date', $currentMonth->month)
                ->orderBy('date')
                ->get()
                )->keyBy(fn($attendance) => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'));

        $attendances = collect(range(1, $currentMonth->daysInMonth))->map(function ($day) use ($currentMonth,$attendanceRecords, $user) {
            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
            return $attendanceRecords->get($date, (object)[
                    'id' => null,
                    'date' => $date,
                    'user_id' => $user->id,
                    'clock_in' => null,
                    'clock_out' => null,
                    'break_total' => '',
                    'work_hours' => '',
                ]);
        });

        $fileName = $user->name . '_' . $currentMonth->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($attendances) {

            $handle = fopen('php://output', 'w');

        // 文字化け対策(Excelで開いたときに日本語が正しく表示されるようにする)
            fwrite($handle, "\xEF\xBB\xBF");

        // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

        // データ行
            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'),
                    $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    $attendance->break_total ?? '',
                    $attendance->work_hours ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
