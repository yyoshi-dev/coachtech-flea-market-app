@extends('layouts.app')

{{-- ヘッダーの検索フォーム部分 --}}
@section('header-search')
<form action="/" method="get" class="search-form">
    <input
        type="text"
        name="keyword"
        placeholder="なにをお探しですか？"
        value="{{ request('keyword') }}"
        class="search-form__input"
    >
    {{-- 現在のタブ状態を検索時にも引き継ぐ --}}
    <input type="hidden" name="tab" value="{{ request('tab') }}">
    <button type="submit" class="search-form__hidden-button"></button>
</form>
@endsection

{{-- ヘッダーのリンク部分 --}}
@section('header-nav')
@guest
    <a href="/login" class="header__link">ログイン</a>
@endguest

@auth
    <form action="/logout" method="post" class="logout-form">
        @csrf
        <button type="submit" class="header__link">ログアウト</button>
    </form>
@endauth

<a href="/mypage" class="header__link">マイページ</a>
<a href="/sell" class="header__link header__link--sell">出品</a>
@endsection