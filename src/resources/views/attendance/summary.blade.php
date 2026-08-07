@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/summary.css') }}">
@endsection

@section('nav')
    <nav>
        <a href="{{ route('attendance.index') }}" class="index-button">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="list-button">勤怠一覧</a>
        <a href="{{ route('application.index') }}" class="application-list-button">申請</a>
        <a href="{{ route('attendance.report') }}" class="report-button">レポート</a>

    <form action="/logout" method="POST">
        @csrf
        <button type="submit" class="logout-button">ログアウト</button>
    </form>
</nav>
@endsection

@section('content')
    <div class="attendance-report-container">
        <h1 class="attendance-report-title">マイ勤怠レポート</h1>
        <p class="attendance-report-lead">
            過去6ヶ月の勤怠データから集計しています。
        </p>

        <h2 class="attendance-summary-title">基本サマリー</h2>
        <div class="attendance-summary">
            <div class="summary-card">
                <label class="summary-data-title">総労働時間</label>
                <div class="summary-data">
                    {{ $totalWorkTimeFormatted }}
                </div>
            </div>

            <div class="summary-card">
                <label class="summary-data-title">総残業時間</label>
                <div class="summary-data">
                    {{ $totalOvertimeFormatted }}
                </div>
            </div>

            <div class="summary-card">
            <label class="summary-data-title">平均労働時間/日</label>
                <div class="summary-data">
                    {{ $averageWorkTimeFormatted }}
                </div>
            </div>
        </div>

        <h2 class="attendance-monthly-trend-title">月次推移  (過去6ヶ月)</h2>
        <table class="attendance-monthly-trend-table">
            <thead>
                <tr class="attendance-monthly-trend-tr">
                    <th class="monthly-th">月</th>
                    <th class="monthly-th">労働時間</th>
                    <th class="monthly-th">残業時間</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyData as $data)
                <tr>
                    <td class="monthly-trend-data">{{ $data['month'] }}</td>
                    <td class="monthly-trend-data">{{ $data['work'] }}</td>
                    <td class="monthly-trend-data">{{ $data['overtime'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h2 class="attendance-anomaly-detection-title">今月の異常探知</h2>
        <p class="attendance-anomaly-detection-lead">
            基準:始業09:00/終業18:00/長時間労働は1日10時間超
        </p>

        <div class="attendance-detectAnomalies">
            <div class="count-card">
                <label class="count-title">遅刻回数</label>
                <div class="count-data">{{ $anomalies['lateCount'] }}回</div>
            </div>
            <div class="count-card">
                <label class="count-title">早退回数</label>
                <div class="count-data">{{ $anomalies['earlyLeaveCount'] }}回</div>
            </div>
            <div class="count-card">
                <label class="count-title">長時間労働日数</label>
                <div class="count-data">{{ $anomalies['overworkCount'] }}日</div>
            </div>
        </div>
    </div>
@endsection