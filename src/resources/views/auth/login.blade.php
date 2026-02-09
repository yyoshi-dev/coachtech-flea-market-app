@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-content">
    <div class="auth-form">
        <h2 class="auth-form__heading">ログイン</h2>

        <div class="auth-form__inner">
            <form action="/login" method="post" class="auth-form__form" novalidate>
                @csrf

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

                <div>
                    <input type="submit" value="ログインする" class="auth-form__btn">
                </div>
            </form>
        </div>
    </div>

    <a href="/register" class="auth-form__register-link">会員登録はこちら</a>
</div>
@endsection