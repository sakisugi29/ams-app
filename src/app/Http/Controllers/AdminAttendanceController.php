<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\User;
use App\Traits\CalculatesAttendanceHours;


class AdminAttendanceController extends Controller
{
    use CalculatesAttendanceHours;

    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $currentDate = \Carbon\Carbon::parse($date);

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        $attendances = $this->calculateBreakAndWorkHours(
            AttendanceRecord::with(['user', 'breaks'])
                ->whereDate('date', $currentDate)
                ->orderBy('date')
                ->get()
        );

        return view('admin.attendance-list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    public function show($id)
    {
        [$userId, $date] = explode('_', $id);
        $user = User::findOrFail($userId);

        $attendance = AttendanceRecord::with(['breaks', 'correctionRequests.details'])
            ->where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            $attendance = new AttendanceRecord([
                'user_id' => $userId,
                'date' => $date,
            ]);
            $pendingRequest = null;
        } else {
            $pendingRequest = $attendance->correctionRequests->firstWhere('status', '承認待ち');
        }

        return view('admin.attendance-detail', compact('attendance','user','pendingRequest'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        [$userId, $date] = explode('_', $id);

        $attendance = AttendanceRecord::with('breaks')->updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            [
                'clock_in' => $request->input('clock_in'),
                'clock_out' => $request->input('clock_out'),
                'comment' => $request->input('comment'),
            ]
        );

        for ($i = 1; $i <= 2; $i++) {
            $breakStart = $request->input("break_start{$i}");
            $breakEnd = $request->input("break_end{$i}");

            if (!$breakStart && !$breakEnd) {
                continue;
            }

            $existingBreak = $attendance->breaks[$i - 1] ?? null;

            if ($existingBreak) {
                $existingBreak->break_start = $breakStart;
                $existingBreak->break_end = $breakEnd;
                $existingBreak->save();
            } else {
                $attendance->breaks()->create([
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                ]);
            }
        }

        return redirect()->route('admin.attendance-show', ['id' => $userId . '_' . $date]);

    }
}
