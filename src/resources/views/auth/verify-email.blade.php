@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="verify-container">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        @if(session('message'))
            <p class="verify-resent">{{ session('message') }}</p>
        @endif

        <a href="http://localhost:8025" class="verify-button">認証はこちらから</a>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-resend-link">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection