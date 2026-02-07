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
@section('header-nav')
<form action="/logout" method="post" class="logout-form">
    @csrf
    <button type="submit" class="logout-form__button">ログアウト</button>
</form>
<a href="/mypage" class="mypage-link">マイページ</a>
<a href="/sell" class="sell-link">出品</a>
@endsection

@section('content')
<div class="profile-edit-content">
    <div class="profile-form">
        <h2 class="profile-form__heading content__heading">プロフィール設定</h2>

        <div class="profile-form__inner">
            <form action="/mypage/profile" method="post" enctype="multipart/form-data" class="profile-form__form">
                @csrf

                <div class="profile-form__image-group">
                    <div class="profile-form__image-wrapper">
                        @if ($user->profile_image_path)
                            <img
                                src="{{ asset('storage/' . $user->profile_image_path) }}"
                                alt="{{ $user->name }}"
                                class="profile-form__image"
                            >
                        @else
                            <div class="profile-form__image-placeholder"></div>
                        @endif
                    </div>
                    <label for="profile_image" class="profile-form__image-button">画像を選択する</label>
                    <input
                        type="file"
                        name="profile_image"
                        id="profile_image"
                        accept=".jpeg,.png"
                        class="profile-form__image-input"
                    >
                    @error('profile_image')
                    <p class="profile-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-form__group">
                    <label for="name" class="profile-form__label">ユーザー名</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        class="profile-form__input"
                    >
                    @error('name')
                    <p class="profile-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-form__group">
                    <label for="postal_code" class="profile-form__label">郵便番号</label>
                    <input
                        type="text"
                        name="postal_code"
                        id="postal_code"
                        value="{{ old('postal_code', $user->postal_code) }}"
                        class="profile-form__input"
                    >
                    @error('postal_code')
                    <p class="profile-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-form__group">
                    <label for="address" class="profile-form__label">住所</label>
                    <input
                        type="text"
                        name="address"
                        id="address"
                        value="{{ old('address', $user->address) }}"
                        class="profile-form__input"
                    >
                    @error('address')
                    <p class="profile-form__error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-form__group">
                    <label for="building" class="profile-form__label">建物名</label>
                    <input
                        type="text"
                        name="building"
                        id="building"
                        value="{{ old('building', $user->building) }}"
                        class="profile-form__input"
                    >
                </div>

                <div>
                    <input type="submit" value="更新する" class="profile-form__btn btn">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection