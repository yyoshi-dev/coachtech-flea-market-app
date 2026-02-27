{{-- Header --}}
@extends('layouts.app_header_guest')

{{-- CSS --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endsection

{{-- Content --}}
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