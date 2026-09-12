<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\CorrectionRequestDetail;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tab = $request->query('tab','pending');

        $status = $tab === 'approved' ? '承認済み' : '承認待ち';

        $applications = CorrectionRequest::with(['attendanceRecord.user','details'])
            ->when(! $user->isAdmin(),function($query){
            return $query->whereHas('attendanceRecord',function($innerQuery){
                $innerQuery->where('user_id', auth()->id());
                });
            })
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        if($user->isAdmin()){
            return view('admin.application-list',compact('tab', 'applications'));
        } else {
            return view('application.list', compact('tab', 'applications'));
        }
    }

    public function show($id)
    {

        $correctionRequest = CorrectionRequest::with(['attendanceRecord','details'])
            ->findOrFail($id);

        return view('admin.application-detail',compact('correctionRequest'));

    }

    public function update($id)
    {
        $correctionRequest = CorrectionRequest::with(['attendanceRecord','details'])
            ->findOrFail($id);

        $attendanceRecord = $correctionRequest->attendanceRecord;
        $attendanceData = [];
        $breakData = [];

        foreach ($correctionRequest->details as $detail){
            $fieldName = $detail->field_name;
            $afterValue = $detail->after_value;

            if(in_array($fieldName,['clock_in','clock_out','comment'],true)){
                $attendanceData[$fieldName] = $afterValue;
            }elseif (in_array($fieldName, ['break_start1', 'break_end1', 'break_start2', 'break_end2'], true)) {
            $breakData[$fieldName] = $afterValue;
            }
        }

        $attendanceRecord->fill($attendanceData);
        $attendanceRecord->save();

        $breaks = $attendanceRecord->breaks;

        if ($breaks->count() > 0) {
            $break1 = $breaks[0];
            if (isset($breakData['break_start1'])) {
                $break1->break_start = $breakData['break_start1'];
            }
            if (isset($breakData['break_end1'])) {
                $break1->break_end = $breakData['break_end1'];
            }
            $break1->save();
        }else{
            if( isset($breakData['break_start1']) || isset($breakData['break_end1'])) {
                $attendanceRecord->breaks()->create([
                    'break_start' => $breakData['break_start1'] ?? null,
                    'break_end' => $breakData['break_end1'] ?? null,
                ]);
            }
        }

        if ($breaks->count() > 1) {
            $break2 = $breaks[1];
            if (isset($breakData['break_start2'])) {
                $break2->break_start = $breakData['break_start2'];
            }
            if (isset($breakData['break_end2'])) {
                $break2->break_end = $breakData['break_end2'];
            }
            $break2->save();
        }

            $correctionRequest->status = '承認済み';
            $correctionRequest->save();

        return view('admin.application-detail',compact('correctionRequest'));
    }
}

