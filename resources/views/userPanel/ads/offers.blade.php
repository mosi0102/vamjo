
@extends('userPanel.layouts.layout')

@section('title', 'درخواست‌های ثبت‌شده برای آگهی')
@section('page_title', 'درخواست‌های آگهی')
@section('body_class', 'pn-offers')

@php
    $title = 'وام ' . number_format($ad['amount'])
        . ' تومان بانک ' . $ad['bank'];

    $toFa = fn ($value) => strtr((string) $value, [
        '0'=>'۰', '1'=>'۱', '2'=>'۲', '3'=>'۳', '4'=>'۴',
        '5'=>'۵', '6'=>'۶', '7'=>'۷', '8'=>'۸', '9'=>'۹',
    ]);
@endphp

@section('main')

    <a href="{{ route('user.ads.index') }}" class="of-back pn-reveal">
        @include('userPanel.partials.icon', ['name' => 'arrow'])
        بازگشت به آگهی‌های من
    </a>

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">
            @include('userPanel.partials.icon', ['name' => 'clipboard'])
        </span>
        <div>
            <h2>درخواست‌های ثبت‌شده</h2>
            <p>درخواست خریداران را بررسی و تأیید یا رد کنید.</p>
        </div>
    </div>

    {{-- خلاصه آگهی --}}
    <section class="of-ad pn-reveal" style="--d:.06s">
        <img
            src="{{ asset('website/img/bank/' . $ad['image'] . '.png') }}"
            alt="{{ $ad['bank'] }}"
            height="90"
        >

        <div class="of-ad-info">
            <h3>{{ $title }}</h3>

            <div class="rq-meta">
                <span class="pn-id">
                    شناسه وام <b>#{{ $ad['code'] }}</b>
                </span>

                <span class="rq-meta-i">
                    @include('userPanel.partials.icon', ['name' => 'pin'])
                    {{ $ad['city'] ?: 'شهر ثبت نشده' }}
                </span>

                <span class="pn-pill pending">
                    {{ $ad['state'] }}
                </span>
            </div>
        </div>

        <div class="of-ad-price">
            <small>قیمت پیشنهادی شما</small>
            <div>
                <b>{{ $fa($ad['price']) }}</b>
                <span>تومان</span>
            </div>
        </div>

        <a href="{{ url('/ads/' . $ad['id']) }}"
           class="btn btn-ghost of-ad-link">
            @include('userPanel.partials.icon', ['name' => 'eye'])
            مشاهده آگهی
        </a>
    </section>

    {{-- آمار --}}
    <section class="of-stats" aria-label="خلاصه درخواست‌ها">

        @foreach ([
            ['کل درخواست‌ها', $counts['all'], 'clipboard', ''],
            ['بالاترین پیشنهاد', $ad['best'], 'trend', 'green'],
            ['میانگین پیشنهادها', $ad['avg'], 'card', ''],
            ['در انتظار پاسخ', $counts['pending'], 'clock', 'amber'],
        ] as $i => $stat)

            <article
                class="of-stat pn-reveal {{ $stat[3] }}"
                style="--d:{{ .1 + $i * .06 }}s"
            >
                <span class="of-stat-ic">
                    @include('userPanel.partials.icon', ['name' => $stat[2]])
                </span>

                <div>
                    <small>{{ $stat[0] }}</small>
                    <b>{{ $fa($stat[1]) }}</b>
                </div>
            </article>

        @endforeach

    </section>

    <section class="pn-panel pn-reveal" style="--d:.2s">

        <div class="of-note" role="note">
            @include('userPanel.partials.icon', ['name' => 'shield'])
            <span>
                با تأیید یک درخواست، خریدار ۷ روز برای پرداخت مهلت دارد
                و سایر درخواست‌های در انتظار طبق منطق ثبت‌شده در سیستم تعیین تکلیف می‌شوند.
            </span>
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="rq-toolbar">

            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">

                @foreach ($tabs as $key => $label)

                    <button
                        type="button"
                        class="rq-tab {{ $loop->first ? 'active' : '' }}"
                        data-group="{{ $key }}"
                        role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        {{ $label }}

                        <span class="rq-count">
                            {{ $faId($counts[$key] ?? 0) }}
                        </span>
                    </button>

                @endforeach

            </div>

            <div class="of-tools">

                <label class="rq-search">
                    @include('userPanel.partials.icon', ['name' => 'search'])

                    <input
                        type="search"
                        id="rqSearch"
                        placeholder="جستجوی نام خریدار..."
                        autocomplete="off"
                        aria-label="جستجوی خریدار"
                    >
                </label>

                <label class="of-sort">
                    <span class="visually-hidden">مرتب‌سازی</span>

                    <select id="ofSort" aria-label="مرتب‌سازی">
                        <option value="amount">بیشترین مبلغ</option>
                        <option value="new">جدیدترین</option>
                    </select>
                </label>

            </div>
        </div>

        {{-- فهرست درخواست‌ها --}}
        <div class="rq-list" id="rqList">

            @forelse ($offers as $o)

                @php
                    $status = $o['status'] ?? 'pending';

                    [$pillClass, $statusLabel] =
                        $statusMap[$status] ?? ['muted', $status];

                    $amount = (int) ($o['amount'] ?? 0);
                    $price = (int) ($ad['price'] ?? 0);

                    $delta = $price > 0
                        ? round((($amount - $price) / $price) * 100)
                        : 0;

                    $isBest = $status === 'pending'
                        && $amount === (int) $best
                        && $amount > 0;

                    $color = $avatarColors[
                        $o['id'] % count($avatarColors)
                    ];

                    $name = $o['name'] ?? 'خریدار';
                    $initial = mb_substr($name, 0, 1);

                    $group = $groupOf($o);
                @endphp

                <article
                    class="rq-card of-card pn-reveal
                        {{ $isBest ? 'of-best' : '' }}
                        {{ in_array($status, ['rejected', 'expired']) ? 'of-muted' : '' }}"
                    data-group="{{ $group }}"
                    data-search="{{ $name }} {{ $amount }}"
                    data-amount="{{ $amount }}"
                    data-age="{{ $o['age'] ?? 0 }}"
                    style="--d:{{ .22 + $loop->index * .06 }}s"
                >

                    @if ($isBest)
                        <span class="of-ribbon">
                            @include('userPanel.partials.icon', ['name' => 'star'])
                            بهترین پیشنهاد
                        </span>
                    @endif

                    <div class="rq-main">

                        <div class="rq-head">

                            <span
                                class="of-avatar"
                                style="background: {{ $color }}"
                                aria-hidden="true"
                            >
                                {{ $initial }}
                            </span>

                            <div class="rq-info">

                                <h3>
                                    {{ $name }}

                                    @if (!empty($o['verified']))
                                        <span class="of-verified" title="هویت احراز شده">
                                            @include('userPanel.partials.icon', ['name' => 'shield'])
                                            احراز شده
                                        </span>
                                    @else
                                        <span class="of-unverified">
                                            احراز نشده
                                        </span>
                                    @endif
                                </h3>

                                <div class="rq-meta">

                                    <span class="rq-meta-i">
                                        @include('userPanel.partials.icon', ['name' => 'clock'])
                                        {{ $o['ago'] ?? 'زمان نامشخص' }}
                                    </span>

                                    <span class="rq-meta-i">
                                        @include('userPanel.partials.icon', ['name' => 'check'])

                                        @if (!empty($o['deals']))
                                            {{ $faId($o['deals']) }} معامله موفق
                                        @else
                                            اولین معامله
                                        @endif
                                    </span>

                                    @if (isset($o['rating']) && $o['rating'] !== null)
                                        <span class="rq-meta-i star">
                                            @include('userPanel.partials.icon', ['name' => 'star'])
                                            {{ $toFa($o['rating']) }}
                                        </span>
                                    @endif

                                </div>
                            </div>
                        </div>

                        <div class="rq-side">

                            <div>
                                <div class="rq-price">
                                    <b>{{ $fa($amount) }}</b>
                                    <small>تومان</small>
                                </div>

                                @if ($delta > 0)
                                    <span class="of-delta up">
                                        ↑ {{ $faId($delta) }}٪ بالاتر از قیمت شما
                                    </span>
                                @elseif ($delta < 0)
                                    <span class="of-delta">
                                        {{ $faId(abs($delta)) }}٪ پایین‌تر از قیمت شما
                                    </span>
                                @else
                                    <span class="of-delta">
                                        هم‌قیمت با پیشنهاد شما
                                    </span>
                                @endif
                            </div>

                            <div class="rq-act">

                                <span class="pn-pill {{ $pillClass }}">
                                    {{ $statusLabel }}
                                </span>

                                @if ($status === 'pending')

                                    <button
                                        type="button"
                                        class="btn btn-ghost of-reject"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmModal"
                                        data-confirm-title="رد درخواست"
                                        data-confirm-text="درخواست {{ $name }} به مبلغ {{ $fa($amount) }} تومان رد شود؟"
                                        data-confirm-action="{{ url('/panel/ads/' . $ad['id'] . '/offers/' . $o['id'] . '/reject') }}"
                                        data-confirm-method="POST"
                                        data-confirm-label="رد درخواست"
                                        data-confirm-tone="danger"
                                        data-confirm-reason="1"
                                    >
                                        رد درخواست
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-brand rq-cta"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmModal"
                                        data-confirm-title="تأیید درخواست"
                                        data-confirm-text="درخواست {{ $name }} به مبلغ {{ $fa($amount) }} تومان تأیید شود؟"
                                        data-confirm-action="{{ url('/panel/ads/' . $ad['id'] . '/offers/' . $o['id'] . '/accept') }}"
                                        data-confirm-method="POST"
                                        data-confirm-label="بله، تأیید شود"
                                        data-confirm-tone="brand"
                                    >
                                        تأیید درخواست
                                        <i class="la la-check"></i>
                                    </button>

                                @endif

                            </div>

                            @if ($status === 'pending')
                                <small class="rq-deadline">
                                    @include('userPanel.partials.icon', ['name' => 'clock'])
                                    {{ $o['expires'] ?? 'مهلت پاسخ مشخص نشده' }}
                                    تا پایان مهلت پاسخ
                                </small>
                            @endif

                        </div>
                    </div>
                </article>

            @empty

                <div class="rq-empty">
                    <div class="rq-empty-ic" aria-hidden="true">
                        @include('userPanel.partials.icon', ['name' => 'clipboard'])
                    </div>

                    <h3>هنوز درخواستی ثبت نشده است</h3>
                    <p>درخواست‌های خریداران پس از ثبت در این صفحه نمایش داده می‌شوند.</p>
                </div>

            @endforelse

        </div>

        <div class="rq-empty" id="rqEmpty" hidden>
            <div class="rq-empty-ic" aria-hidden="true">
                @include('userPanel.partials.icon', ['name' => 'clipboard'])
            </div>

            <h3>درخواستی پیدا نشد</h3>
            <p>فیلتر یا عبارت جستجو را تغییر دهید.</p>
        </div>

    </section>

    @include('userPanel.partials.confirm-modal')

    {{-- فیلتر سمت مرورگر --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('rqList');
            const search = document.getElementById('rqSearch');
            const sort = document.getElementById('ofSort');
            const empty = document.getElementById('rqEmpty');
            const tabs = document.querySelectorAll('.rq-tab');

            if (!list) return;

            let activeGroup = 'all';

            function filterOffers() {
                const query = (search?.value || '').trim().toLocaleLowerCase();
                const cards = Array.from(list.querySelectorAll('.rq-card'));
                let visible = 0;

                cards.forEach(function (card) {
                    const group = card.dataset.group || '';
                    const text = (card.dataset.search || '').toLocaleLowerCase();

                    const matchesGroup =
                        activeGroup === 'all' || group === activeGroup;

                    const matchesSearch = text.includes(query);
                    const show = matchesGroup && matchesSearch;

                    card.hidden = !show;

                    if (show) visible++;
                });

                if (sort) {
                    cards.sort(function (a, b) {
                        if (sort.value === 'new') {
                            return Number(b.dataset.age || 0)
                                - Number(a.dataset.age || 0);
                        }

                        return Number(b.dataset.amount || 0)
                            - Number(a.dataset.amount || 0);
                    });

                    cards.forEach(card => list.appendChild(card));
                }

                if (empty) {
                    empty.hidden = visible !== 0;
                }
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    activeGroup = tab.dataset.group || 'all';

                    tabs.forEach(function (item) {
                        const selected = item === tab;

                        item.classList.toggle('active', selected);
                        item.setAttribute(
                            'aria-selected',
                            selected ? 'true' : 'false'
                        );
                    });

                    filterOffers();
                });
            });

            search?.addEventListener('input', filterOffers);
            sort?.addEventListener('change', filterOffers);

            filterOffers();
        });
    </script>

@endsection
