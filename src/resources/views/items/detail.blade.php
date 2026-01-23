@extends('layouts.app')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}">
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
<div class="item-detail-content">
    {{-- 商品画像 --}}
    <div class="item__image-area">
        <img
            src="{{ asset('storage/' . $product->product_image_path) }}"
            alt="{{ $product->name }}"
            class="item__image"
        >
        @if ($product->is_sold)
            <span class="item__sold-label">Sold</span>
        @endif
    </div>

    {{-- 商品説明 --}}
    <div class="item__description-area">
        {{-- 商品タイトルブロック --}}
        <div class="item__title-block">
            <h2 class="item__title">{{ $product->name }}</h2>
            <p class="item__brand">{{ $product->brand_name }}</p>
            <p class="item__price">\{{ number_format($product->price) }} (税込)</p>
            <div class="item__action-area">
                <div class="item__likes-area">
                    {{-- いいねボタンを後で実装 (今はロゴを仮置き) --}}
                    <img src="{{ asset('images/heart-logo-default.png') }}" alt="heart-logo-default">
                    <p class="item__likes-count">{{ $product->productLikes->count() }}</p>
                </div>
                <div class="item__comments-area">
                    <img src="{{ asset('images/speech-bubble-logo.png') }}" alt="speech-bubble-logo">
                    <p class="item__comments-count">{{ $product->comments_count }}</p>
                </div>
            </div>
            <a href="/purchase/{{ $product->id }}" class="item__purchase-link">購入手続きへ</a>
        </div>

        {{-- 商品説明ブロック --}}
        <div class="item__description-block">
            <h3 class="item__description-title">商品説明</h3>
            <p class="item__description-text">{{ $product->description }}</p>
        </div>

        {{-- 商品情報ブロック --}}
        <div class="item__info-block">
            <div class="item__category">
                <span class="item__category-title">カテゴリー</span>
                @foreach($product->productCategories as $category)
                    <p class="item__category-text">{{ $category->name }}</p>
                @endforeach
            </div>
            <div class="item__condition">
                <span class="item__condition-title">商品の状態</span>
                <p class="item__condition-text">{{ $product->productCondition->name }}</p>
            </div>
        </div>

        {{-- コメントブロック --}}
        <div class="item__comment-block">
            {{-- コメント表示 --}}
            <h3 class="item__comment-title">コメント ({{ $product->productComments->count() }})</h3>
            @if ($product->productComments->isNotEmpty())
                @foreach($product->productComments as $comment)
                    <div class="item__comment-user">
                        <span class="item__comment-user--mark"></span>
                        <span class="item__comment-user--name">{{ $comment->user->name }}</span>
                    </div>
                    <p class="item__comment-text">{{ $comment->comment }}</p>
                @endforeach
            @endif

            {{-- コメント入力 --}}
            <form action="/comment/{{ $product->id }}" method="post" class="comment-form">
                @csrf
                <label for="comment" class="comment-form__label">商品へのコメント</label>
                <textarea
                    name="comment"
                    id="comment"
                    cols="30"
                    rows="10"
                    class="comment-form__textarea"
                >{{ old('comment') }}</textarea>
                @error('comment')
                <p class="comment-form__error-message">{{ $message }}</p>
                @enderror
                <button type="submit" class="contact-form__btn">コメントを送信する</button>
            </form>
        </div>
    </div>
</div>
@endsection