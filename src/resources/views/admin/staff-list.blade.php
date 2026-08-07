@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff-list.css') }}">
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
        <h1 class="staff-list-title">スタッフ一覧</h1>

        <table class="staff-list-table">
            <thead>
                <tr class="staff-list-tr">
                    <th class="staff-list-th">名前</th>
                    <th class="staff-list-th">メールアドレス</th>
                    <th class="staff-list-th">月次勤怠</th>
                </tr>
            </thead>

            <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="staff-list-td name">{{ $user->name }}</td>
                    <td class="staff-list-td email">{{ $user->email }}</td>
                    <td class="staff-list-td detail"><a href="{{ route ('admin.staff-attendance-list',$user->id ) }}">詳細</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection

