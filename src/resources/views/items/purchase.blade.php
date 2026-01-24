@extends('layouts.app')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
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
<div class="purchase-content">
    <form action="/purchase/{{ $product->id }}" method="post" class="purchase-form">
        @csrf
        <div class="purchase-settings">
            {{-- 商品情報 --}}
            <div class="purchase-item__description">
                <div class="purchase-item__image-area">
                    <img
                        src="{{ asset('storage/' . $product->product_image_path) }}"
                        alt="{{ $product->name }}"
                        class="purchase-item__image"
                    >
                </div>
                <div class="purchase-item__info-area">
                    <span class="purchase-item__name">{{ $product->name }}</span>
                    <p class="purchase-item__price">\{{ number_format($product->price) }}</p>
                </div>
            </div>

            {{-- 支払い方法の設定 --}}
            <div class="payment-methods__settings">
                <label for="payment_method_id" class="payment-methods__title">支払い方法</label>
                <select name="payment_method_id" id="payment_method_id" class="payment-methods__select">
                    <option disabled selected>選択してください</option>
                    @foreach ($paymentMethods as $paymentMethod)
                        {{-- 選択した方法のみ先頭に✓マークを表示したいが、以下の書き方だと不十分であり、要修正になると思う --}}
                        <option value="{{ $paymentMethod->id }}"
                            {{ old('payment_method_id')==$paymentMethod->id ? 'selected' : '' }}>
                            ✓{{ $paymentMethod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 配送先の設定 --}}
            <div class="delivery-address__settings">
                <div class="delivery-address__heading">
                    <span class="delivery-address__title">配送先</span>
                    <a href="/purchase/address/{{ $product->id }}" class="delivery-address__edit-link">
                        変更する
                    </a>
                </div>
                <div class="delivery-address__content">
                    {{-- 以下だと、配送先を変更した事を反映出来ないので、要修正 --}}
                    <span class="delivery-address__text">{{ $address['postal_code'] }}</span>
                    <span class="delivery-address__text">{{ $address['address'] }}</span>
                    <span class="delivery-address__text">{{ $address['building'] }}</span>
                    <input type="hidden" name="postal_code" value="{{ $address['postal_code'] }}">
                    <input type="hidden" name="address" value="{{ $address['address'] }}">
                    <input type="hidden" name="building" value="{{ $address['building'] }}">
                </div>
            </div>
        </div>

        <div class="purchase-execution">
            <div class="purchase-summary">
                <table class="purchase-summary__table">
                    <tr class="purchase-summary__row">
                        <th class="purchase-summary__header">商品代金</th>
                        <td class="purchase-summary__text">{{ number_format($product->price) }}</td>
                    </tr>
                    <tr class="purchase-summary__row">
                        <th class="purchase-summary__header">支払い方法</th>
                        {{-- 以下だと、選択した方法が反映されないので、要修正 --}}
                        <td class="purchase-summary__text">{{ $paymentMethod->name }}</td>
                    </tr>
                </table>
            </div>

            {{-- 以下だと、stripeの決済画面に接続されないので、要修正 --}}
            <button type="submit" class="purchase__btn">購入する</button>
        </div>
    </form>
</div>
@endsection