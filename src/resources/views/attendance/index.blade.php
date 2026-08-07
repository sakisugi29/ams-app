@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('nav')
    <nav>
        <a href="{{ route('attendance.index') }}" class="index-button">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="list-button">勤怠一覧</a>
        <a href="{{ route('application.index') }}" class="application-list-button">申請</a>

    <form action="/logout" method="POST">
        @csrf
        <button type="submit" class="logout-button">ログアウト</button>
    </form>
</nav>
@endsection

@section('content')
    <div class="attendance-container">
        <p class="status">
            @switch($status)
            @case('not_working')
                勤務外
                @break
            @case('working')
                出勤中
                @break
            @case('on_break')
                休憩中
                @break
            @case('finished')
                退勤済
                @break
            @endswitch
        </p>

    <div id="current-date" class="current-date">
        <span id="date-text">{{ $now->format('Y年n月j日') }}</span>
        <span id="day-text">({{ ['日','月','火','水','木','金','土'][$now->dayOfWeek] }})</span>
    </div>
    <div id="current-time" class="current-time">{{ $now->format('H:i') }}</div>

    <script>
        function updateDateTime() {
            const now = new Date();

            const days = ['日', '月', '火', '水', '木', '金', '土'];
            const dateStr = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日`;
            const dayStr = `(${days[now.getDay()]})`;

            document.getElementById('date-text').textContent = dateStr;
            document.getElementById('day-text').textContent = dayStr;

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}`;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>

    <div class="button-group">
        @if ($status === 'not_working')
            <form method="POST" action="{{ route('attendance.clockIn') }}">
            @csrf
            <button type="submit" class="button">出勤</button>
            </form>
        @elseif ($status === 'working')
            <form method="POST" action="{{ route('attendance.clockOut') }}">
            @csrf
            <button type="submit" class="button">退勤</button>
            </form>
            <form method="POST" action="{{ route('attendance.startBreak') }}">
            @csrf
            <button type="submit" class="button">休憩入</button>
            </form>
        @elseif ($status === 'on_break')
            <form method="POST" action="{{ route('attendance.endBreak') }}">
            @csrf
            <button type="submit" class="button">休憩戻</button>
            </form>
            @elseif ($status === 'finished')
            <p class="finished-message">お疲れ様でした。</p>
        @endif
    </div>

@endsection