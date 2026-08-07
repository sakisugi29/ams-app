<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Traits\CalculatesAttendanceHours;

class AttendanceController extends Controller

{
    use CalculatesAttendanceHours;
    
    public function index()
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();
        $status = $attendance->status ?? 'not_working';

        $now = now();

        return view('attendance.index', compact('attendance', 'status', 'now'));
    }

    public function clockIn(Request $request)
    {
        $exists = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->exists();

        if ($exists) {
            return redirect()->route('attendance.index');
        }

        AttendanceRecord::create([
            'user_id' => auth()->id(),
            'date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index');
    }

    public function startBreak(Request $request)
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        $attendance->update(['status' => 'on_break']); // 休憩中
            BreakTime::create([
                'attendance_record_id' => $attendance->id,
                'break_start' => now(),
    ]);

    return redirect()->route('attendance.index');
    }

    public function endBreak(Request $request)
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        $attendance->update(['status' => 'working']); // 出勤中に戻る
        $attendance->breaks()->latest()->first()->update(['break_end' => now()]);

    return redirect()->route('attendance.index');
    }

    public function clockOut(Request $request)
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        $attendance->update([
            'clock_out' => now(),
            'status' => 'finished', // 退勤済
    ]);

    return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = \Carbon\Carbon::parse($month);

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendanceRecords = $this->calculateBreakAndWorkHours(
            AttendanceRecord::with('breaks')
                ->where('user_id', auth()->id())
                ->whereYear('date', $currentMonth->year)
                ->whereMonth('date', $currentMonth->month)
                ->get()
                )->keyBy(fn($attendance) => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'));

        $attendances = collect(range(1, $currentMonth->daysInMonth))->map(function ($day) use ($currentMonth, $attendanceRecords) {
            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
            return $attendanceRecords->get($date, (object)[
                'id' => null,
                'date' => $date,
                'user_id' => auth()->id(),
                'clock_in' => null,
                'clock_out' => null,
                'break_total' => '',
                'work_hours' => '',
            ]);
        });

        return view('attendance.list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function show($id)
    {
        $attendance = AttendanceRecord::with(['breaks', 'correctionRequests.details'])
            ->where('user_id', auth()->id())
            ->where('date', $id)
            ->first();

        if (!$attendance) {
            $attendance = new AttendanceRecord([
                'user_id' => auth()->id(),
                'date' => $id,
            ]);
            $pendingRequest = null;
        } else {
            $pendingRequest = $attendance->correctionRequests->firstWhere('status', '承認待ち');
        }

        return view('attendance.detail', compact('attendance', 'pendingRequest'));
    }

    public function update(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->where('date', $id)
            ->first();

        if (!$attendance) {
        $attendance = AttendanceRecord::create([
            'user_id' => auth()->id(),
            'date' => $id,
            'status' => 'off_work',
            ]);
        }

        if ($attendance->correctionRequests()->where('status', '承認待ち')->exists()) {
        return back()->withErrors(['申請は既に承認待ちです。']);
    }

        $correctionRequest = $attendance->correctionRequests()->create([
        'status' => '承認待ち',
    ]);

        $fields = [
        'clock_in'  => [$attendance->clock_in, $request->input('clock_in')],
        'clock_out' => [$attendance->clock_out, $request->input('clock_out')],
        'comment'   => [$attendance->comment, $request->input('comment')],
    ];

    foreach ($fields as $fieldName => [$before, $after]) {
        if (!is_null($after) && $after !== '') {
            $correctionRequest->details()->create([
                'field_name'   => $fieldName,
                'before_value' => $before,
                'after_value'  => $after,
            ]);
        }
    }

    // 休憩1, 休憩2（固定2件分をチェック）
    for ($i = 1; $i <= 2; $i++) {
        $existingBreak = $attendance->breaks[$i - 1] ?? null;

        $startInput = $request->input("break_start{$i}");
        $endInput   = $request->input("break_end{$i}");

        if (!empty($startInput)) {
            $correctionRequest->details()->create([
                'field_name'   => "break_start{$i}",
                'before_value' => $existingBreak->break_start ?? null,
                'after_value'  => $startInput,
            ]);
        }

        if (!empty($endInput)) {
            $correctionRequest->details()->create([
                'field_name'   => "break_end{$i}",
                'before_value' => $existingBreak->break_end ?? null,
                'after_value'  => $endInput,
            ]);
        }
    }

        return redirect()->route('attendance.show', ['id' => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]);
    }

    public function adminShow($id)
    {
        $attendance = AttendanceRecord::with(['breaks', 'correctionRequests.details'])
            ->where('id', $id)
            ->firstOrFail();

        $pendingRequest = $attendance->correctionRequests
            ->firstWhere('status', '承認待ち');

        return view('attendance.show', compact('attendance', 'pendingRequest'));
    }
}
