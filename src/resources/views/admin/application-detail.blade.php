@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.application-detail.css') }}">
@endsection


@section('nav')
    <nav>
        <a href="{{ route('admin.attendance-list') }}" class="attendance-list-button">勤怠一覧</a>
        <a href="{{ route('admin.staff-list') }}" class="staff-list-button">スタッフ一覧</a>
        <a href="{{ route('application.index') }}" class="application-list-button">申請一覧</a>

    <form action="/admin/logout" method="POST">
        @csrf
        <button type="submit" class="logout-button">ログアウト</button>
    </form>

</nav>
@endsection

@section('content')
<div class="attendance-detail-container">
        <h1 class="attendance-detail-title">勤怠詳細</h1>

    <div class="attendance-info">
        <form class="attendance-form" id="attendance-form" action="{{ route('admin.application-update', $correctionRequest->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="attendance-detail-box">
            <div class="form-item">
                <span class="attendance-label">名前</span>
                <span class="attendance-name">{{ $correctionRequest->attendanceRecord->user->name }}</span>
            </div>

            <div class="form-item">
                <span class="attendance-label">日付</span>
                <span class="attendance-year">{{ \Carbon\Carbon::parse($correctionRequest->attendanceRecord->date)->format('Y年') }}</span>
                <span class="attendance-month">{{ \Carbon\Carbon::parse($correctionRequest->attendanceRecord->date)->format('m月d日') }}</span>
            </div>

            <div class="form-item">
            <label class="attendance-label">出勤・退勤</label>
                <div class="attendance-time">
                    <span class="attendance-item">{{ optional($correctionRequest->details->firstWhere('field_name','clock_in'))->after_value ?? ($correctionRequest->attendanceRecord->clock_in ? \Carbon\Carbon::parse($correctionRequest->attendanceRecord->clock_in)->format('H:i') : '') }}</span>
                    <span class="attendance-dash">〜</span>
                    <span class="attendance-item">{{ optional($correctionRequest->details->firstWhere('field_name','clock_out'))->after_value ?? ($correctionRequest->attendanceRecord->clock_out ? \Carbon\Carbon::parse($correctionRequest->attendanceRecord->clock_out)->format('H:i') : '') }}</span>
                </div>
            </div>

            <div class="form-item">
            <label class="attendance-label">休憩</label>
                <div class="attendance-break">
                    <span class="attendance-item">{{ optional($correctionRequest->details->firstWhere('field_name','break_start1'))->after_value ?? ($correctionRequest->attendanceRecord->break_start1 ? \Carbon\Carbon::parse($correctionRequest->attendanceRecord->break_start1)->format('H:i') : '') }}</span>
                    <span class="attendance-dash">〜</span>
                    <span class="attendance-item">{{ optional($correctionRequest->details->firstWhere('field_name','break_end1'))->after_value ?? ($correctionRequest->attendanceRecord->break_end1 ? \Carbon\Carbon::parse($correctionRequest->attendanceRecord->break_end1)->format('H:i') : '') }}</span>
                </div>
            </div>

            @php
            $break2Start = optional($correctionRequest->details->firstWhere('field_name', 'break_start2'))->after_value
                ?? ($correctionRequest->attendanceRecord->break_start2 ? \Carbon\Carbon::parse($correctionRequest->attendanceRecord->break_start2)->format('H:i') : '');
            $break2End = optional($correctionRequest->details->firstWhere('field_name', 'break_end2'))->after_value 
                ?? ($correctionRequest->attendanceRecord->break_end2 ? \Carbon\Carbon::parse($correctionRequest->attendanceRecord->break_end2)->format('H:i') : '');
            @endphp
            <div class="form-item">
            <label class="attendance-label">休憩2</label>
                <div class="attendance-break2">
                    @if($break2Start || $break2End)
                        <span class="attendance-item">{{ $break2Start}}</span>
                        <span class="attendance-dash">〜</span>
                        <span class="attendance-item">{{ $break2End}}</span>
                    @endif
                </div>
            </div>


            <div class="form-item">
            <label class="attendance-label">備考</label>
                <div class="attendance-comment">
                    <span class="attendance-text">{{ $correctionRequest->details->firstWhere('field_name','comment')->after_value ?? ''}}</span>
                </div>
            </div>
        </form>
    </div>
        @if($correctionRequest->status === '承認済み')
            <button type="button" form="attendance-form" class="approved-button" disabled>承認済み</button>
        @else
            <button type="submit" form="attendance-form" class="approved-button">承認</button>
        @endif
</div>
@endsection