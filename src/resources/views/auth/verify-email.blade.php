@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="verification-content">
    <span class="verification-content__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </span>

    <div class="verification-content__link">
        <a href="{{ route('verification.notice') }}">認証はこちらから</a>
    </div>

    <form action="{{ route('verification.send') }}" method="post" class="verification-form">
        @csrf
        <button type="submit" class="verification-form__notification_btn">認証メールを再送する</button>
    </form>

</div>
@endsection