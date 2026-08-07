@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff-attendance-list.css') }}">
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
    <div class="staff-list-container">
        <h1 class="staff-list-title">{{ $user->name }}さんの勤怠</h1>

        <div class="month-nav">
            <a href="{{ route('admin.staff-attendance-list', ['id' => $user->id,'month' => $prevMonth]) }}" class="pre-month">
                <img src="{{ asset('images/088deff71873c09816bca59dd0d7efa7308e8fba.png') }}" class="pre-icon">
                前月
            </a>
            <span class="current-month">
                <img src="{{ asset('images/50f4850c610ecd6f85b7ef666143260b91151a78.png') }}" class="month-icon">
                {{ $currentMonth->format('Y/m') }}
            </span>
            <a href="{{ route('admin.staff-attendance-list', ['id' => $user->id,'month' => $nextMonth]) }}" class="next-month">
                翌月
                <img src="{{ asset('images/088deff71873c09816bca59dd0d7efa7308e8fba.png') }}" class="next-icon">
            </a>
        </div>

        <table class="staff-attendance-list-table">
            <thead>
                <tr class="attendance-tr">
                    <th class="attendance-list">日付</th>
                    <th class="attendance-list">出勤</th>
                    <th class="attendance-list">退勤</th>
                    <th class="attendance-list">休憩</th>
                    <th class="attendance-list">合計</th>
                    <th class="attendance-list">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td class="attendance-data">{{ \Carbon\Carbon::parse($attendance->date)->format('m/d(') }}{{ ['日','月','火','水','木','金','土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }})</td>
                    <td class="attendance-data">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td class="attendance-data">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td class="attendance-data">{{ $attendance->break_total }}</td>
                    <td class="attendance-data">{{ $attendance->work_hours }}</td>
                    <td class="attendance-data"><a href="{{ route('admin.attendance-show', ['id'=> $attendance->user_id . '_' . $attendance->date]) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('admin.staff-attendance-csv', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}" class="csv-button">
        CSV出力
        </a>
    </div>
@endsection