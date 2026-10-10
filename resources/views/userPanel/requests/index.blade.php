@extends('userPanel.layouts.layout')

@section('title', 'درخواست های من')
@section('page_title', 'درخواست های من')
@section('body_class', 'pn-requests')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('requests')
    // step = مرحله جاری (۱ تا ۷) | state = active | completed | rejected
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $steps = ['ثبت درخواست', 'تایید درخواست', 'پرداخت', 'تایید پرداخت', 'انتقال امتیاز', 'تایید انتقال', 'تکمیل'];
    $waiting = [1 => 'در انتظار ثبت', 2 => 'در انتظار تایید', 3 => 'در انتظار پرداخت', 4 => 'در انتظار تایید پرداخت', 5 => 'در انتظار انتقال امتیاز', 6 => 'در انتظار انتقال', 7 => 'در حال تکمیل'];

    $requests = [
      ['id' => 1235896, 'bank' => 'رسالت', 'image'=>'8',   'l' => 'ر', 'c' => '#1f8a9e', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'step' => 3, 'state' => 'active', 'deadline' => 7],
      ['id' => 1235897, 'bank' => 'شهر', 'image'=>'7',     'l' => 'ش', 'c' => '#d6322c', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'step' => 6, 'state' => 'active', 'doc' => null,'deadline' => 7],
      ['id' => 1235898, 'bank' => 'جاویدان', 'image'=>'4', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'step' => 7, 'state' => 'completed'],
      ['id' => 1235899, 'bank' => 'جاویدان', 'image'=>'4', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'step' => 2, 'state' => 'rejected', 'note' => 'متاسفانه درخواست شما رد شد.'],
    ];
    $group = fn ($r) => $r['state'] === 'completed' ? 'done' : ($r['state'] === 'rejected' ? 'rejected' : ($r['step'] === 3 ? 'pay' : 'progress'));
    $tabs = ['all' => 'همه', 'pay' => 'در انتظار پرداخت', 'progress' => 'در حال انجام', 'done' => 'تکمیل شده', 'rejected' => 'رد شده'];
    $counts = collect($requests)->groupBy($group)->map->count();
    $counts['all'] = count($requests);
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'doc'])</span>
        <div>
            <h2>درخواست های من</h2>
            <p>تمامی درخواست های شما در این صفحه دیده می‌شود.</p>
        </div>
    </div>

    <section class="pn-panel pn-reveal" style="--d:.08s">

        {{-- فیلتر وضعیت + جستجو --}}
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $key => $label)
                    <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-group="{{ $key }}"
                            role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $label }}<span class="rq-count">{{ $faId($counts[$key] ?? 0) }}</span>
                    </button>
                @endforeach
            </div>
            <label class="rq-search">
                @include('userPanel.partials.icon', ['name' => 'search'])
                <input type="search" id="rqSearch" placeholder="جستجو شناسه، بانک یا مبلغ..." autocomplete="off"
                       aria-label="جستجو در درخواست‌ها">
            </label>
        </div>

        <div class="rq-list" id="rqList">
            @foreach ($requests as $r)
                @php
                    $g = $group($r);
                    $pill = $r['state'] === 'completed' ? ['approved', 'تکمیل شده'] : ($r['state'] === 'rejected' ? ['rejected', 'رد شده'] : ['pending', $waiting[$r['step']]]);
                @endphp
                <article class="rq-card pn-reveal" data-group="{{ $g }}"
                         data-search="{{ $r['id'] }} {{ $r['bank'] }} {{ $r['amount'] }} {{ $r['price'] }}"
                         style="--d:{{ .12 + $loop->index * .07 }}s">
                    <div class="rq-main">
                        <div class="rq-head">
                            <img src="{{asset('website/img/bank/'.$r['image'].'.png')}}" alt="" height="90">
                            <div class="rq-info">
                                <h3>وام {{ $fa($r['amount']) }} تومان بانک {{ $r['bank'] }}</h3>
                                <div class="rq-meta">
                                    <button type="button" class="pn-id rq-id" data-copy="{{ $r['id'] }}"
                                            title="کپی شناسه وام">
                                        شناسه وام
                                        <b>#{{ $faId($r['id']) }}</b>@include('userPanel.partials.icon', ['name' => 'copy'])
                                    </button>
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $r['ago'] }}</span>
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'pin']){{ $r['city'] }}</span>
                                </div>
                                @if (! empty($r['note']))
                                    <p class="rq-note">{{ $r['note'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="rq-side">
                            <div class="rq-price"><b>{{ $fa($r['price']) }}</b> <small>تومان</small></div>
                            <div class="rq-act">
                                <span class="pn-pill {{ $pill[0] }}">{{ $pill[1] }}</span>
                                @if ($r['state'] === 'active' && $r['step'] === 3)
                                    <small
                                        class="rq-deadline">@include('userPanel.partials.icon', ['name' => 'clock']) {{ $faId($r['deadline']) }}
                                        روز مهلت پرداخت</small>
                                @endif
                                @if ($r['state'] === 'active' && $r['step'] === 6)
                                    <small
                                        class="rq-deadline">@include('userPanel.partials.icon', ['name' => 'clock']) {{ $faId($r['deadline']) }}
                                        روز مهلت تایید</small>
                                @endif
                                @if ($r['state'] === 'active' && $r['step'] === 3)
                                    <button type="button" class="btn btn-brand rq-cta" data-bs-toggle="modal"
                                            data-bs-target="#payModal"
                                            data-id="{{ $r['id'] }}"
                                            data-title="وام {{ $fa($r['amount']) }} تومان بانک {{ $r['bank'] }}"
                                            data-price="{{ $r['price'] }}" data-bank-l="{{ $r['l'] }}"
                                            data-bank-c="{{ $r['c'] }}"
                                            data-action="{{ url('/panel/requests/' . $r['id'] . '/pay') }}">
                                        پرداخت مبلغ وام @include('userPanel.partials.icon', ['name' => 'arrow'])
                                    </button>
                                @endif

                                @if ($r['state'] === 'active' && $r['step'] === 6)
                                    <button type="button" class="btn btn-brand rq-cta" data-bs-toggle="modal"
                                            data-bs-target="#transferModal"
                                            data-id="{{ $r['id'] }}"
                                            data-title="وام {{ $fa($r['amount']) }} تومان بانک {{ $r['bank'] }}"
                                            data-bank-l="{{ $r['l'] }}" data-bank-c="{{ $r['c'] }}"
                                            data-doc="{{ $r['doc'] ?? '' }}"
                                            data-action="{{ url('/panel/requests/' . $r['id'] . '/confirm-transfer') }}">
                                        مشاهده مدرک و تایید
                                        انتقال @include('userPanel.partials.icon', ['name' => 'arrow'])
                                    </button>
                                @endif

                            </div>

                        </div>
                    </div>

                    {{-- مراحل معامله --}}
                    <ol class="rq-steps" aria-label="مراحل درخواست">
                        @foreach ($steps as $i => $label)
                            @php
                                $n = $i + 1;
                                if ($r['state'] === 'completed') $cls = $n < 7 ? 'done' : 'final';
                                elseif ($r['state'] === 'rejected') $cls = $n < $r['step'] ? 'done' : ($n === $r['step'] ? 'rejected' : 'todo');
                                else $cls = $n < $r['step'] ? 'done' : ($n === $r['step'] ? 'current' : 'todo');
                            @endphp
                            <li class="{{ $cls }}" style="--i:{{ $i }}"
                                @if (in_array($cls, ['current', 'rejected'])) aria-current="step" @endif>
                                @if (in_array($cls, ['done', 'final']))
                                    @include('userPanel.partials.icon', ['name' => 'check'])
                                @endif
                                <span>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ol>
                    @php
                        $cur = $r['state'] === 'completed' ? 7 : $r['step'];
                        $nowText = $r['state'] === 'rejected' ? 'رد شده در مرحله «' . $steps[$cur - 1] . '»' : ($r['state'] === 'completed' ? 'معامله با موفقیت تکمیل شد' : 'مرحله ' . $faId($cur) . ' از ۷ · ' . $steps[$cur - 1]);
                    @endphp
                    <p class="rq-now {{ $r['state'] }}">{{ $nowText }}</p>
                </article>
            @endforeach
        </div>

        <div class="rq-empty" id="rqEmpty" hidden>
            <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'doc'])</div>
            <h3>درخواستی پیدا نشد</h3>
            <p>فیلتر یا عبارت جستجو را تغییر دهید، یا آگهی‌های جدید را ببینید.</p>
            <a href="{{ url('/ads') }}" class="btn btn-brand">مشاهده آگهی ها</a>
        </div>
    </section>

    @include('userPanel.partials.pay-modal')
    @include('userPanel.partials.transfer-modal')
@endsection
