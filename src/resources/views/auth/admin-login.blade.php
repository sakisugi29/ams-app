@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login">
    <h2 class="login_title">ログイン</h2>

    <form class="login_form" method="POST" action="{{ route('admin.login') }}" novalidate>
        @csrf
        <div class="login_form-group">
            <label class="login_label" for="email">メールアドレス</label>
            <input
                class="login_input"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
            >
            @error('email')
                <p class="login_error">{{ $message }}</p>
            @enderror
        </div>
        <div class="login_form-group">
            <label class="login_label" for="password">パスワード</label>
            <input
                class="login_input"
                type="password"
                id="password"
                name="password"
            >
            @error('password')
                <p class="login_error">{{ $message }}</p>
            @enderror
        </div>
        <button class="login_btn" type="submit">管理者ログインする</button>

    </form>
</div>
@endsection