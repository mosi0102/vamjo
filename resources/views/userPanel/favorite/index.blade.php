@extends('userPanel.layouts.layout')

@section('title', 'آگهی های برگزیده')
@section('page_title', 'برگزیده ها')
@section('body_class', 'pn-favorites')

@php
    // داده واقعی از کنترلر: User\FavoriteController@index (Presenter::favoriteRow)
    // active=false → آگهی فروخته‌شده/منقضی | age = دقیقه از ذخیره‌سازی | drop = درصد کاهش قیمت
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $col = collect($favorites);
    $counts = ['all' => $col->count(), 'active' => $col->where('active', true)->count(), 'off' => $col->where('active', false)->count()];
    $tabs = ['all' => 'همه', 'active' => 'فعال', 'off' => 'فروخته شده / منقضی'];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'bookmark'])</span>
        <div>
            <h2>آگهی های برگزیده</h2>
            <p>آگهی‌هایی که ذخیره کرده‌اید را اینجا ببینید و به‌راحتی پیگیری کنید.</p>
        </div>
        <a href="{{ route('site.ads') }}"
           class="btn btn-brand pn-head-act">@include('userPanel.partials.icon', ['name' => 'search']) مشاهده آگهی
            ها</a>
    </div>

    <section class="pn-panel pn-reveal" style="--d:.08s">

        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $key => $label)
                    <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-fgroup="{{ $key }}"
                            role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}<span
                            class="rq-count" data-count="{{ $key }}">{{ $faId($counts[$key]) }}</span></button>
                @endforeach
            </div>
            <div class="of-tools">
                <label class="rq-search">
                    @include('userPanel.partials.icon', ['name' => 'search'])
                    <input type="search" id="fvSearch" placeholder="جستجو بانک یا مبلغ..." autocomplete="off"
                           aria-label="جستجو در برگزیده‌ها">
                </label>
                <label class="of-sort"><span class="visually-hidden">مرتب سازی</span>
                    <select id="fvSort" aria-label="مرتب سازی">
                        <option value="saved">تازه ذخیره‌شده</option>
                        <option value="amount-asc">کمترین مبلغ</option>
                        <option value="amount-desc">بیشترین مبلغ</option>
                        <option value="rate">کمترین سود</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="fv-grid" id="fvGrid">
            @foreach ($favorites as $a)
                <div class="fv-item pn-reveal {{ $a['active'] ? '' : 'fv-off' }}"
                     data-group="{{ $a['active'] ? 'active' : 'off' }}" data-amount="{{ $a['amount'] }}"
                     data-rate="{{ $a['rate'] }}" data-age="{{ $a['age'] }}"
                     data-search="{{ $a['bank'] }} {{ $a['amount'] }} {{ $a['buy'] }}"
                     data-remove-url="{{ route('user.favorites.destroy', $a['id']) }}"
                     style="--d:{{ .12 + $loop->index * .06 }}s">
                    <button type="button" class="fv-remove" aria-label="حذف از برگزیده‌ها"
                            title="حذف از برگزیده‌ها">@include('userPanel.partials.icon', ['name' => 'bookmark'])</button>
                    @if ($a['active'])
                        <a class="d-block text-decoration-none" href="{{ route('site.ads.show', $a['id']) }}" aria-label="مشاهده آگهی">
                            @include('website.partials.ad-card', ['a' => $a, 'fa' => $fa])
                        </a>
                    @else
                        @include('website.partials.ad-card', ['a' => $a, 'fa' => $fa])
                    @endif
                    <div class="fv-saved">
                        <span
                            class="fv-saved-t">@include('userPanel.partials.icon', ['name' => 'clock']) ذخیره شده {{ $a['saved'] }}</span>
                        <span class="fv-badges">
              @if (! $a['active'])
                                <span class="fv-badge off">فروخته شد</span>
                            @endif
                            @if ($a['drop'])
                                <span class="fv-badge drop">↓ {{ $faId($a['drop']) }}٪ کاهش قیمت</span>
                            @endif
            </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rq-empty" id="fvEmpty" @if (count($favorites)) hidden @endif>
            <div class="rq-empty-ic"
                 aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'bookmark'])</div>
            <h3 id="fvEmptyTitle">هنوز آگهی‌ای ذخیره نکرده‌اید</h3>
            <p id="fvEmptyText">با زدن دکمه نشان‌گذاری روی هر آگهی، آن را اینجا پیدا می‌کنید.</p>
            <a href="{{ route('site.ads') }}" class="btn btn-brand">مشاهده آگهی ها</a>
        </div>
    </section>
@endsection
