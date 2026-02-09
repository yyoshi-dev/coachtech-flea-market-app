@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-content">
    <div class="auth-form">
        <h2 class="auth-form__heading">会員登録</h2>

        <div class="auth-form__inner">
            <form action="/register" method="post" class="auth-form__form" novalidate>
                @csrf

                <div class="auth-form__group">
                    <label for="name" class="auth-form__label">ユーザー名</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="auth-form__input">
                    @error('name')
                    <p class="auth-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-form__group">
                    <label for="email" class="auth-form__label">メールアドレス</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="auth-form__input">
                    @error('email')
                    <p class="auth-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-form__group">
                    <label for="password" class="auth-form__label">パスワード</label>
                    <input type="password" name="password" id="password" class="auth-form__input">
                    @error('password')
                    <p class="auth-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-form__group">
                    <label for="password_confirmation" class="auth-form__label">確認用パスワード</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="auth-form__input">
                    @error('password_confirmation')
                    <p class="auth-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="submit" value="登録する" class="auth-form__btn">
                </div>
            </form>
        </div>
    </div>

    <a href="/login" class="auth-form__login-link">ログインはこちら</a>
</div>
@endsection