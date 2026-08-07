@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register">
    <h2 class="register_title">会員登録</h2>

    <form class="register_form" method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="register_form-group">
            <label class="register_label" for="name">名前</label>
            <input
                class="register_input"
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
            >
            @error('name')
                <p class="register_error">{{ $message }}</p>
            @enderror
        </div>
        <div class="register_form-group">
            <label class="register_label" for="email">メールアドレス</label>
            <input
                class="register_input"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
            >
            @error('email')
                <p class="register_error">{{ $message }}</p>
            @enderror
        </div>
        <div class="register_form-group">
            <label class="register_label" for="password">パスワード</label>
            <input
                class="register_input"
                type="password"
                id="password"
                name="password"
            >
            @error('password')
                <p class="register_error">{{ $message }}</p>
            @enderror
        </div>
        <div class="register_form-group">
            <label class="register_label" for="password_confirmation">パスワード確認</label>
            <input
                class="register_input"
                type="password"
                id="password_confirmation"
                name="password_confirmation"
            >
        </div>
        <button class="register_btn" type="submit">登録する</button>

    </form>
    <a class="register_login-link" href="{{ route('login') }}">ログインはこちら</a>

</div>
@endsection