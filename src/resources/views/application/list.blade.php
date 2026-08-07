@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/application.list.css') }}">
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
    <div class="application-container">
        <h1 class="application-title">申請一覧</h1>

        <div class="tab-container">
            <a href="{{ route('application.index', ['tab' => 'pending']) }}"
               class="tab-item {{ $tab !== 'approved' ? 'tab-active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('application.index', ['tab' => 'approved']) }}"
               class="tab-item {{ $tab === 'approved' ? 'tab-active' : '' }}">
                承認済み
            </a>
        </div>

        <table class="application-table">
            <thead>
                <tr class="application-tr">
                    <th class="application-list">状態</th>
                    <th class="application-list">名前</th>
                    <th class="application-list">対象日時</th>
                    <th class="application-list">申請理由</th>
                    <th class="application-list">申請日時</th>
                    <th class="application-list">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr>
                        <td class="application-data">
                            {{ $application->status === '承認済み' ? '承認済み' : '承認待ち' }}
                        </td>
                        <td class="application-data">{{ $application->attendanceRecord->user->name }}</td>
                        <td class="application-data">{{ \Carbon\Carbon::parse($application->attendanceRecord->date)->format('Y/m/d') }}</td>
                        <td class="application-data">{{ $application->details->firstWhere('field_name', 'comment')->after_value ?? '' }}</td>
                        <td class="application-data">{{ $application->created_at->format('Y/m/d') }}</td>
                        <td class="application-data">
                            <a href="{{ route('attendance.show', \Carbon\Carbon::parse($application->attendanceRecord->date)->format('Y-m-d')) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
