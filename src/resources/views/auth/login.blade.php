@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-content">
    <div class="login-form">
        <h2 class="login-form__heading content__heading">ログイン</h2>

        <div class="login-form__inner">
            <form action="/login" method="post" class="login-form__form" novalidate>
                @csrf

                <div class="login-form__group">
                    <label for="email" class="login-form__label">メールアドレス</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="login-form__input">
                    @error('email')
                    <p class="login-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-form__group">
                    <label for="password" class="login-form__label">パスワード</label>
                    <input type="password" name="password" id="password" class="login-form__input">
                    @error('password')
                    <p class="login-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="submit" value="ログインする" class="login-form__btn btn">
                </div>
            </form>
        </div>
    </div>

    <a href="/register" class="login-form__register-link">会員登録はこちら</a>
</div>
@endsection