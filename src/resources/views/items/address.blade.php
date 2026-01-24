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
<div class="address-content">
    <div class="address-form">
        <h2 class="address-form__heading content__heading">住所の変更</h2>

        <div class="address-form__inner">
            <form action="/purchase/address/{{ $item_id }}" method="post" class="address-form__form">
                @csrf

                <div class="address-form__group">
                    <label for="postal_code" class="address-form__label">郵便番号</label>
                    <input
                        type="text"
                        name="postal_code"
                        id="postal_code"
                        value="{{ old('postal_code') }}"
                        class="address-form__input"
                    >
                    @error('postal_code')
                    <p class="address-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="address-form__group">
                    <label for="address" class="address-form__label">住所</label>
                    <input
                        type="text"
                        name="address"
                        id="address"
                        value="{{ old('address') }}"
                        class="address-form__input"
                    >
                    @error('address')
                    <p class="address-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="address-form__group">
                    <label for="building" class="address-form__label">建物名</label>
                    <input
                        type="text"
                        name="building"
                        id="building"
                        value="{{ old('building') }}"
                        class="address-form__input"
                    >
                </div>

                <div>
                    <input type="submit" value="更新する" class="address-form__btn btn">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection