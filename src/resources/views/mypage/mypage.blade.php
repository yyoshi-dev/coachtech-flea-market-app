{{-- Header --}}
@extends('layouts.app_header_auth')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

{{-- Content --}}
@section('content')
<div class="mypage-content">
    {{-- ユーザー情報 --}}
    <div class="user-profile">
        <div class="user-profile__info">
            @if ($user->profile_image_path)
                <img
                    src="{{ asset('storage/' . $user->profile_image_path) }}"
                    alt="{{ $user->name }}"
                    class="user-profile__image"
                >
            @else
                <div class="user-profile__image-placeholder"></div>
            @endif
            <span class="user-profile__name">{{ $user->name }}</span>
        </div>
        <a href="/mypage/profile" class="user-profile__edit-link">プロフィールを編集</a>
    </div>

    {{-- ページ切り替え部分 --}}
    <div class="page-menu">
        <a
            href="/mypage?page=sell"
            class="page-menu__link {{ $page === 'sell' ? 'page-menu__link--active' : '' }}"
        >
            出品した商品
        </a>
        <a
            href="/mypage?page=buy"
            class="page-menu__link {{ $page === 'buy' ? 'page-menu__link--active' : '' }}"
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