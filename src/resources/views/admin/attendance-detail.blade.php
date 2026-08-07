@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.attendance-detail.css') }}">
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
            @if ($pendingRequest)
            @php
            $details = $pendingRequest->details->keyBy('field_name');
            @endphp

        <div class="attendance-detail-box">
            <div class="form-item">
                <span class="attendance-label">名前</span>
                <span class="attendance-name">{{ $attendance->user->name }}</span>
            </div>

            <div class="form-item">
                <span class="attendance-label">日付</span>
                <span class="attendance-year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                <span class="attendance-month">{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</span>
            </div>

            <div class="form-item">
            <label class="attendance-label">出勤・退勤</label>
                <div class="attendance-time">
                    <span class="attendance-item">{{ optional($details->get('clock_in'))->after_value ?? ($attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}</span>
                    <span class="attendance-dash">〜</span>
                    <span class="attendance-item">{{ optional($details->get('clock_out'))->after_value ?? ($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}</span>
                </div>
            </div>

            <div class="form-item">
            <label class="attendance-label">休憩</label>
                <div class="attendance-break">
                    <span class="attendance-item">{{ optional($details->get('break_start1'))->after_value }}</span>
                    <span class="attendance-dash">〜</span>
                    <span class="attendance-item">{{ optional($details->get('break_end1'))->after_value }}</span>
                </div>
            </div>

            @if ($details->has('break_start2') || $details->has('break_end2'))
            <div class="form-item">
            <label class="attendance-label">休憩2</label>
                <div class="attendance-break2">
                    <span class="attendance-item">{{ optional($details->get('break_start2'))->after_value }}</span>
                    <span class="attendance-dash">〜</span>
                    <span class="attendance-item">{{ optional($details->get('break_end2'))->after_value }}</span>
                </div>
            </div>
            @endif

            <div class="form-item">
            <label class="attendance-label">備考</label>
                <div class="attendance-comment">
                    <span class="attendance-text">{{ optional($details->get('comment'))->after_value ?? $attendance->comment }}</span>
                </div>
            </div>
        </div>

    <p class="pending-message">*承認待ちのため修正はできません。</p>
        @else
    <form class="attendance-form" id="attendance-form" action="{{ route('admin.attendance-update', ['id' => $user->id .'_' . \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-item">
            <span class="attendance-label">名前</span>
            <span class="attendance-name">{{ $attendance->user->name }}</span>
        </div>

        <div class="form-item">
            <span class="attendance-label">日付</span>
            <span class="attendance-year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
            <span class="attendance-month">{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</span>
        </div>

        <div class="form-item">
            <label class="attendance-label">出勤・退勤</label>
            <div class="attendance-time">
            <input type="text" name="clock_in" class="attendance-input-clock" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
            <span class="attendance-dash">〜</span>
            <input type="text" name="clock_out" class="attendance-input-clock" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
            </div>
        <div class="error-message">
            @if ($errors->has('clock_in'))
                <span class="error">{{ $errors->first('clock_in') }}</span>
            @endif
            @if ($errors->has('clock_out'))
                <span class="error">{{ $errors->first('clock_out') }}</span>
            @endif
        </div>
        </div>

        <div class="form-item">
            <label class="attendance-label">休憩</label>
            <div class="attendance-break">
            <input type="text" name="break_start1" class="attendance-input-break" value="{{ old('break_start1', optional($attendance->breaks[0] ?? null)->break_start ? \Carbon\Carbon::parse($attendance->breaks[0]->break_start)->format('H:i') : '') }}">
            <span class="attendance-dash">〜</span>
            <input type="text" name="break_end1" class="attendance-input-break" value="{{ old('break_end1', optional($attendance->breaks[0] ?? null)->break_end ? \Carbon\Carbon::parse($attendance->breaks[0]->break_end)->format('H:i') : '') }}">
            </div>
        <div class="error-message">
            @if ($errors->has('break_start1'))
                <span class="error">{{ $errors->first('break_start1') }}</span>
            @endif
            @if ($errors->has('break_end1'))
                <span class="error">{{ $errors->first('break_end1') }}</span>
            @endif
        </div>
        </div>

        <div class="form-item">
            <label class="attendance-label">休憩2</label>
            <div class="attendance-break2">
            <input type="text" name="break_start2" class="attendance-input-break2" value="{{ old('break_start2', optional($attendance->breaks[1] ?? null)->break_start ? \Carbon\Carbon::parse($attendance->breaks[1]->break_start)->format('H:i') : '') }}">
            <span class="attendance-dash">〜</span>
            <input type="text" name="break_end2" class="attendance-input-break2" value="{{ old('break_end2', optional($attendance->breaks[1] ?? null)->break_end ? \Carbon\Carbon::parse($attendance->breaks[1]->break_end)->format('H:i') : '') }}">
            </div>
        <div class="error-message">
            @if ($errors->has('break_start2'))
                <span class="error">{{ $errors->first('break_start2') }}</span>
            @endif
            @if ($errors->has('break_end2'))
                <span class="error">{{ $errors->first('break_end2') }}</span>
            @endif
        </div>
        </div>

        <div class="form-item">
            <label for="comment" class="attendance-label">備考</label>
            <div class="attendance-comment">
                <textarea id="comment" name="comment" class="attendance-textarea">{{ old('comment', $attendance->comment) }}</textarea>
            </div>
        <div class="error-message">
            @if ($errors->has('comment'))
                <span class="error">{{ $errors->first('comment') }}</span>
            @endif
        </div>
        </div>
    </form>
    <button type="submit" form="attendance-form" class="attendance-button">修正</button>
@endif
        </div>
    </div>
@endsection