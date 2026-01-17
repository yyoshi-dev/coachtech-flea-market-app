@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-content">
    <div class="register-form">
        <h2 class="register-form__heading content__heading">会員登録</h2>

        <div class="register-form__inner">
            <form action="/register" method="post" class="register-form__form" novalidate>
                @csrf

                <div class="register-form__group">
                    <label for="name" class="register-form__label">ユーザー名</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="register-form__input">
                    @error('name')
                    <p class="register-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="register-form__group">
                    <label for="email" class="register-form__label">メールアドレス</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="register-form__input">
                    @error('email')
                    <p class="register-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="register-form__group">
                    <label for="password" class="register-form__label">パスワード</label>
                    <input type="password" name="password" id="password" class="register-form__input">
                    @error('password')
                    <p class="register-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="register-form__group">
                    <label for="password_confirmation" class="register-form__label">確認用パスワード</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="register-form__input">
                    @error('password_confirmation')
                    <p class="register-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="submit" value="登録する" class="register-form__btn btn">
                </div>
            </form>
        </div>
    </div>

    <a href="/login" class="register-form__login-link">ログインはこちら</a>
</div>
@endsection