@extends('layouts.app')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

{{-- ヘッダーの検索フォーム部分 --}}
@section('header-search')
<form action="/search" method="get" class="search-form">
    <input
        type="text"
        name="keyword"
        placeholder="なにをお探しですか？"
        value="{{request('keyword')}}"
        class="search-form__input"
    >
    <button type="submit" class="search-form__hidden-button"></button>
</form>
@endsection

{{-- ヘッダーのリンク部分 --}}
@section('header-link')
<form action="/logout" method="post" class="logout-form">
    @csrf
    <button type="submit" class="logout-form__button">ログアウト</button>
</form>
<a href="/mypage" class="mypage-link">マイページ</a>
<a href="/sell" class="sell-link">出品</a>
@endsection

@section('content')
<div class="profile-content">
    {{-- ユーザー情報 --}}
    <div class="user-profile">
        <div class="user-info">
            @if ($user->profile_image_path)
                <img
                    src="{{ asset('storage/' . $user->profile_image_path) }}"
                    alt="{{ $user->name }}"
                    class="profile__image"
                >
            @else
                <div class="profile__image--placeholder"></div>
            @endif
            <span class="user__name">{{ $user->name }}</span>
        </div>
        <a href="/mypage/profile" class="profile-edit__link">プロフィールを編集</a>
    </div>

    {{-- ページ切り替え部分 --}}
    <div class="page-menu">
        <a
            href="/mypage?page=sell"
            class="page-menu__link {{ $page === 'sell' ? 'active' : '' }}"
        >
            出品した商品
        </a>
        <a
            href="/mypage?page=buy"
            class="page-menu__link {{ $page === 'buy' ? 'active' : '' }}"
        >
            購入した商品
        </a>
    </div>

    {{-- 商品一覧 --}}
    <ul class="item-list">
        @foreach ($products as $product)
            <li class="item-list__item">
                <img
                    src="{{ asset('storage/' . $product->product_image_path) }}"
                    alt="{{ $product->name }}"
                    class="item-list__image"
                >
                <div class="item-list__name-wrapper">
                    <p class="item-list__name">{{ $product->name }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endsection