@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.application-list.css') }}">
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
    <div class="admin-application-container">
        <h1 class="admin-application-title">申請一覧</h1>

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

        <table class="admin-application-table">
            <thead>
                <tr class="admin-application-tr">
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
                            <a href="{{ route('admin.application-detail',$application->id) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection