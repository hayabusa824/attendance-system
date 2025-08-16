@extends('layout.app2')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')

<div class="register-box">
    <h2 class="main-title">会員登録</h2>
    <form action="{{ route('register') }}" method="POST" class="register-form">
        @csrf
        <div class="form">
        <div class="form-group">
            <div class="form-text-1">
            <label for="name">ユーザー名</label>
            </div>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" autocomplete="name">
            @error('name')
            <div class="form_error">
            {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <div class="form-text">
            <label for="email">メールアドレス</label>
            </div>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" autocomplete="email">
            @error('email')
            <div class="form_error">
            {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <div class="form-text">
            <label for="password">パスワード</label>
            </div>
            <input type="password" id="password" name="password" value="{{ old('password') }}" class="form-input" autocomplete="new-password">
            @error('password')
            <div class="form_error">
            {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <div class="form-text">
            <label for="password_confirmation">確認用パスワード</label>
            </div>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
            @error('password_confirmation')
            <div class="form_error">
            {{ $message }}
            </div>
            @enderror
        </div>
        </div>

        <div class="register-button">
        <button type="submit" class="register-button__text">登録</button>
        </div>
    </form>

    <div class="register-link">
        <a href="/login" class="register-link__text--link">ログインこちら</a>
    </div>
</div>

@endsection