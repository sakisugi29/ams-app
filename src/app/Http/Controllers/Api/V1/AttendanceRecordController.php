<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Http\Resources\AttendanceRecordResource;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;


class AttendanceRecordController extends Controller
{
    public function index(IndexAttendanceRecordRequest $request)
    {
        $perPage = $request->query('per_page',20);

        $attendanceRecords = AttendanceRecord::query()->with('user','breaks')
            ->when($request->filled('user_id'), function ($query) use ($request) {
                return $query->where('user_id', $request->query('user_id'));
                })
            ->when($request->filled('date'), function ($query) use ($request) {
                return $query->where('date', $request->query('date'));
                })
            ->when($request->filled('month'), function ($query) use ($request) {
                return $query->where('date', 'like', $request->query('month') . '%');
                })
                ->latest('date')
                ->paginate($perPage);

        return AttendanceRecordResource::collection($attendanceRecords);
    }

    public function show(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->load(['user', 'breaks', 'applications']);

        return new AttendanceRecordResource($attendanceRecord);
    }

    public function store(StoreAttendanceRecordRequest $request)
    {
        $validated = $request->validated();

        $attendanceRecord = $request->user()->attendanceRecords()->create($validated);

        $attendanceRecord->load(['user', 'breaks']);

        return (new AttendanceRecordResource($attendanceRecord))->response()->setStatusCode(201);

    }

    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord)
    {
        $this->authorize('update', $attendanceRecord);

        $validated = $request->validated();

        $attendanceRecord->update($validated);

        $attendanceRecord->load(['user', 'breaks']);

        return new AttendanceRecordResource($attendanceRecord);

    }

    public function destroy(AttendanceRecord $attendanceRecord)
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->json(null,204);
    }
}
