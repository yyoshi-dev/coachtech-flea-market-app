@extends('layouts.app')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endsection

{{-- ヘッダーの検索フォーム部分 --}}
@section('header-search')
<form action="/" method="get" class="search-form">
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
@section('header-nav')
{{-- 未認証時の表示内容 --}}
@guest
    <a href="/login" class="login-link">ログイン</a>
@endguest
{{-- 認証時の表示内容 --}}
@auth
    <form action="/logout" method="post" class="logout-form">
        @csrf
        <button type="submit" class="logout-form__button">ログアウト</button>
    </form>
@endauth
{{-- 未認証・認証共通の表示内容 --}}
<a href="/mypage" class="mypage-link">マイページ</a>
<a href="/sell" class="sell-link">出品</a>
@endsection


@section('content')
<div class="item-content">
    {{-- タブ部分 --}}
    <div class="tab-menu">
        <a
            href="/?keyword={{ request('keyword') }}"
            class="tab-menu__link {{ request('tab') !== 'mylist' ? 'active' : '' }}"
        >
            おすすめ
        </a>
        <a
            href="/?tab=mylist&keyword={{ request('keyword') }}"
            class="tab-menu__link {{ request('tab') === 'mylist' ? 'active' : '' }}"
        >
            マイリスト
        </a>
    </div>

    {{-- 商品一覧 --}}
    <ul class="item-list">
        @foreach ($products as $product)
            <li class="item-list__item">
                <a href="/item/{{ $product->id }}" class="item-list__detail-link">
                    <img
                        src="{{ asset('storage/' . $product->product_image_path) }}"
                        alt="{{ $product->name }}"
                        class="item-list__image"
                    >
                    <div class="item-list__name-wrapper">
                        <p class="item-list__name">{{ $product->name }}</p>
                        @if ($product->is_sold)
                            <span data-testid="sold-badge" class="item-list__sold-label">Sold</span>
                        @endif
                    </div>
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endsection