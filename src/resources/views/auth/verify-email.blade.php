@extends('layout.app2')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="container">
    @if (session('error'))
        <div class="form_error">
            {{ session('error') }}
        </div>
    @endif
    @if (session('message'))
        <div class="form_error">
            {{ session('message') }}
        </div>
    @endif

    <div class="text">登録していただいたメールアドレスに認証メールを送付しました。メール認証を完了してください。</div>

    <div class="btn">
        <a href="/attendance" class="btn-link">認証はこちらから</a>
    </div>

    <form action="{{ route('verification.resend') }}" method="POST">
        @csrf
        <button type="submit" class="button">認証メールを再送する</button>
    </form>
</div>
@endsection
