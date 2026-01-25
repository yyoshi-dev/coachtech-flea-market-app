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
            <form action="/purchase/payment/{{ $product->id }}" method="post" class="payment-methods__form">
                @csrf
                <label for="payment_method_id" class="payment-methods__title">支払い方法</label>
                <select
                    name="payment_method_id"
                    id="payment_method_id"
                    class="payment-methods__select"
                    onchange="this.form.submit()"
                >
                    <option disabled {{ !$selectedPaymentMethod ? 'selected' : '' }}>選択してください</option>
                    @foreach ($paymentMethods as $paymentMethod)
                        <option value="{{ $paymentMethod->id }}"
                            {{ $selectedPaymentMethod && $selectedPaymentMethod->id == $paymentMethod->id ? 'selected' : '' }}>
                            {{ $selectedPaymentMethod && $selectedPaymentMethod->id == $paymentMethod->id ? '✓' : '' }}
                            {{ $paymentMethod->name }}
                        </option>
                    @endforeach
                </select>
            </form>
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
                <span class="delivery-address__text">{{ $address['postal_code'] }}</span>
                <span class="delivery-address__text">{{ $address['address'] }}</span>
                <span class="delivery-address__text">{{ $address['building'] }}</span>
            </div>
        </div>
    </div>

    <div class="purchase-execution">
        <form action="/purchase/{{ $product->id }}" method="post" class="purchase-form">
            @csrf
            {{-- 小計 --}}
            <div class="purchase-summary">
                <table class="purchase-summary__table">
                    <tr class="purchase-summary__row">
                        <th class="purchase-summary__header">商品代金</th>
                        <td class="purchase-summary__text">{{ number_format($product->price) }}</td>
                    </tr>
                    <tr class="purchase-summary__row">
                        <th class="purchase-summary__header">支払い方法</th>
                        <td class="purchase-summary__text">{{ $summaryPaymentMethod->name }}</td>
                    </tr>
                </table>
            </div>

            {{-- 購入処理 --}}
            <input type="hidden" name="delivery_address" value="{{ serialize($address) }}">
            @error('delivery_address')
                <p class="purchase-form__error-message">{{ $message }}</p>
            @enderror
            <input type="hidden" name="payment_method_id"
                value="{{ $selectedPaymentMethod ? $selectedPaymentMethod->id : '' }}">
            @error('payment_method_id')
                <p class="purchase-form__error-message">{{ $message }}</p>
            @enderror
            <button type="submit" class="purchase__btn">購入する</button>
        </form>
    </div>
</div>
@endsection

