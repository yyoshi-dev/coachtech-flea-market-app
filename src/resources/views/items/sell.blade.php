@extends('layouts.app')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
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
@section('header-nav')
<form action="/logout" method="post" class="logout-form">
    @csrf
    <button type="submit" class="logout-form__button">ログアウト</button>
</form>
<a href="/mypage" class="mypage-link">マイページ</a>
<a href="/sell" class="sell-link">出品</a>
@endsection

@section('content')
<div class="exhibition-content">
    <div class="exhibition-form">
        <h2 class="exhibition-form__heading">商品の出品</h2>

        <div class="exhibition-form__inner">
            <form action="/sell" method="post" enctype="multipart/form-data" class="exhibition-form__form">
                @csrf

                <div class="exhibition-form__image-group">
                    <span class="exhibition-form__image-header">商品画像</span>
                    <div class="exhibition-form__image-box">
                        <label for="product_image" class="exhibition-form__image-button">画像を選択する</label>
                        <input
                            type="file"
                            name="product_image"
                            id="product_image"
                            accept=".jpeg,.png"
                            class="exhibition-form__image-input"
                        >
                    </div>
                    @error('product_image')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <h3 class="exhibition-form__sub-heading">商品の詳細</h3>

                <div class="exhibition-form__group">
                    <label for="product_category_ids" class="exhibition-form__label">カテゴリー</label>
                    <div class="exhibition-form__checkbox-group">
                        @foreach($categories as $category)
                            <label for="category_{{ $category->id }}" class="exhibition-form__checkbox-label">
                                <input
                                    type="checkbox"
                                    name="product_category_ids[]"
                                    id="category_{{ $category->id }}"
                                    value="{{ $category->id }}"
                                    {{ is_array(old('product_category_ids')) && in_array($category->id, old('product_category_ids')) ? 'checked' : '' }}
                                >
                                {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                    @error('product_category_ids')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="exhibition-form__group">
                    <label for="product_condition_id" class="exhibition-form__label">商品の状態</label>
                    <div class="exhibition-form__select-inner">
                        <select
                            name="product_condition_id"
                            id="product_condition_id"
                            class="exhibition-form__select"
                        >
                            <option value="" disabled selected>
                                選択してください
                            </option>
                            @foreach($conditions as $condition)
                                <option
                                    value="{{ $condition->id }}"
                                    {{ old('product_condition_id')==$condition->id ? 'selected' : '' }}>
                                    {{ $condition->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('product_condition_id')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <h3 class="exhibition-form__sub-heading">商品名と説明</h3>

                <div class="exhibition-form__group">
                    <label for="name" class="exhibition-form__label">商品名</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        class="exhibition-form__input"
                    >
                    @error('name')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="exhibition-form__group">
                    <label for="brand_name" class="exhibition-form__label">ブランド名</label>
                    <input
                        type="text"
                        name="brand_name"
                        id="brand_name"
                        value="{{ old('brand_name') }}"
                        class="exhibition-form__input"
                    >
                    @error('brand_name')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="exhibition-form__group">
                    <label for="description" class="exhibition-form__label">商品の説明</label>
                    <textarea
                        name="description"
                        id="description"
                        cols="30"
                        rows="10"
                        class="exhibition-form__textarea"
                    >{{ old('description') }}</textarea>
                    @error('description')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="exhibition-form__group">
                    <label for="price" class="exhibition-form__label">販売価格</label>
                    <div class="exhibition-form__price-wrapper">
                        <input
                            type="text"
                            name="price"
                            id="price"
                            value="{{ old('price') }}"
                            class="exhibition-form__input exhibition-form__input--price"
                        >
                    </div>
                    @error('price')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="submit" value="出品する" class="exhibition-form__btn">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection