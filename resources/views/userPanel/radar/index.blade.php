@extends('userPanel.layouts.layout')

@section('title', 'رادار وام')
@section('page_title', 'رادار وام')
@section('body_class', 'pn-radar')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('radars', 'matches', 'banks')
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $banks = ['بانک جاویدان', 'بانک رسالت', 'بانک صادرات', 'بانک شهر', 'بانک کشاورزی', 'بانک قرض الحسنه مهر ایران', 'بانک تجارت'];

    $radars = [
      ['id' => 1, 'name' => 'رادار رسالت', 'active' => true,  'min' => 200000000, 'max' => 300000000, 'months' => 24, 'rate' => 23, 'bank' => 'بانک رسالت',   'city' => 'سبزوار', 'sms' => true, 'panel' => true],
      ['id' => 2, 'name' => 'وام های جاویدان', 'active' => true,  'min' => null, 'max' => 500000000, 'months' => null, 'rate' => null, 'bank' => 'بانک جاویدان', 'city' => '', 'sms' => true, 'panel' => true],
      ['id' => 3, 'name' => 'وام ارزان مشهد', 'active' => false, 'min' => null, 'max' => null, 'months' => null, 'rate' => 20, 'bank' => '', 'city' => 'مشهد', 'sms' => false, 'panel' => true],
    ];
    $matches = [
      ['id' => 1235896, 'radar' => 1, 'bank' => 'رسالت','image'=>'8',   'l' => 'ر', 'c' => '#1f8a9e', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'months' => 24, 'rate' => 23, 'found' => '۲ ساعت پیش', 'is_new' => true],
      ['id' => 1235897, 'radar' => 2, 'bank' => 'جاویدان','image'=>'4', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'months' => 24, 'rate' => 23, 'found' => 'دیروز', 'is_new' => false],
    ];
    $radarName = collect($radars)->pluck('name', 'id');
    $countOf = fn ($id) => collect($matches)->where('radar', $id)->count();
    $newOf = fn ($id) => collect($matches)->where('radar', $id)->where('is_new', true)->count();
    $mil = fn ($n) => $faId(rtrim(rtrim(number_format($n / 1e6, 1), '0'), '.'));
    $summary = function ($r) use ($mil, $faId) {
      $p = [];
      if ($r['min'] || $r['max']) $p[] = 'مبلغ ' . ($r['min'] ? $mil($r['min']) : '۰') . ($r['max'] ? ' تا ' . $mil($r['max']) : '+') . ' میلیون';
      if ($r['months']) $p[] = 'حداقل ' . $faId($r['months']) . ' ماه';
      if ($r['rate']) $p[] = 'سود تا ' . $faId($r['rate']) . '٪';
      if ($r['bank']) $p[] = $r['bank'];
      if ($r['city']) $p[] = $r['city'];
      return $p;
    };
    $newTotal = collect($matches)->where('is_new', true)->count();
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'radar'])</span>
        <div>
            <h2>رادار وام</h2>
            <p>در این قسمت شرایط وام مورد نظر خودتان را وارد نمایید و به محض پیدا شدن به شما اطلاع داده میشود.</p>
        </div>
        <button type="button" class="btn btn-brand pn-head-act" data-bs-toggle="modal"
                data-bs-target="#radarModal">@include('userPanel.partials.icon', ['name' => 'plus']) رادار جدید
        </button>
    </div>

    <section class="pn-panel pn-reveal" style="--d:.08s">

        @if (count($radars))
            {{-- رادارهای من --}}
            <div class="rd-sec-head"><h3>رادارهای من <span class="rq-count">{{ $faId(count($radars)) }}</span></h3>
                <small>روی یک رادار بزنید تا فقط نتایج آن را ببینید</small></div>
            <div class="rd-radars" id="rdRadars" role="tablist" aria-label="رادارهای من">
                <button type="button" class="rd-radar rd-all active" data-radar="all" role="tab" aria-selected="true">
                    <span class="rd-radar-ic">@include('userPanel.partials.icon', ['name' => 'radar'])</span>
                    <b>همه رادارها</b>
                    <small>{{ $faId(count($matches)) }} وام پیدا شده</small>
                    @if ($newTotal)
                        <span class="rd-new-dot">{{ $faId($newTotal) }} جدید</span>
                    @endif
                </button>

                @foreach ($radars as $r)
                    @php $parts = $summary($r); @endphp
                    <div class="rd-radar {{ $r['active'] ? '' : 'off' }}" data-radar="{{ $r['id'] }}" role="tab"
                         tabindex="0" aria-selected="false">
                        <div class="rd-radar-top">
                            <span class="rd-radar-ic">@include('userPanel.partials.icon', ['name' => 'radar'])</span>
                            <b>{{ $r['name'] }}</b>
                            <label class="rd-switch" title="{{ $r['active'] ? 'غیرفعال کردن' : 'فعال کردن' }}"
                                   data-name="{{ $r['name'] }}"
                                   data-url="{{ url('/panel/radar/' . $r['id'] . '/toggle') }}">
                                <input type="checkbox"
                                       {{ $r['active'] ? 'checked' : '' }} aria-label="فعال بودن {{ $r['name'] }}"><i></i>
                            </label>
                        </div>
                        <div class="rd-tags">@foreach ($parts as $t)
                                <span>{{ $t }}</span>
                            @endforeach</div>
                        <div class="rd-radar-foot">
                            <small>{{ $faId($countOf($r['id'])) }} وام @if ($newOf($r['id']))
                                    <em class="rd-new-dot">{{ $faId($newOf($r['id'])) }} جدید</em>
                                @endif</small>
                            <span class="rd-acts">
                <button type="button" class="rd-ic-btn" data-bs-toggle="modal" data-bs-target="#radarModal"
                        data-radar='@json($r)' aria-label="ویرایش {{ $r['name'] }}"
                        title="ویرایش">@include('userPanel.partials.icon', ['name' => 'edit'])</button>
                <button type="button" class="rd-ic-btn danger" data-bs-toggle="modal" data-bs-target="#confirmModal"
                        data-confirm-title="حذف رادار"
                        data-confirm-text="رادار «{{ $r['name'] }}» حذف شود؟ دیگر برای وام‌های جدید اطلاع‌رسانی نمی‌شود."
                        data-confirm-action="{{ url('/panel/radar/' . $r['id']) }}" data-confirm-method="DELETE"
                        data-confirm-label="بله، حذف شود" data-confirm-tone="danger"
                        aria-label="حذف {{ $r['name'] }}"
                        title="حذف">@include('userPanel.partials.icon', ['name' => 'trash'])</button>
              </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- نتایج --}}
            <div class="rd-sec-head mt-4">
                <h3>وام های پیدا شده <span class="rq-count" id="rdCount">{{ $faId(count($matches)) }}</span></h3>
                <label class="rd-only-new"><input type="checkbox" id="rdOnlyNew"><span class="box"></span> فقط
                    جدیدها</label>
            </div>

            <div class="rq-list" id="rdList">
                @foreach ($matches as $m)
                    @php $title = 'وام ' . $fa($m['amount']) . ' تومان بانک ' . $m['bank']; @endphp
                    <article class="rq-card ad-card-my rd-loan pn-reveal" data-radar="{{ $m['radar'] }}"
                             data-new="{{ $m['is_new'] ? 1 : 0 }}" style="--d:{{ .12 + $loop->index * .07 }}s">
                        <div class="rq-main">
                            <div class="rq-head">
                                <img src="{{asset('website/img/bank/'.$m['image'].'.png')}}" alt="" height="90">
                                <div class="rq-info">
                                    <h3>{{ $title }} @if ($m['is_new'])
                                            <span class="rd-new">جدید</span>
                                        @endif</h3>
                                    <div class="rq-meta">
                                        <button type="button" class="pn-id rq-id" data-copy="{{ $m['id'] }}"
                                                title="کپی شناسه وام">شناسه وام
                                            <b>#{{ $faId($m['id']) }}</b>@include('userPanel.partials.icon', ['name' => 'copy'])
                                        </button>
                                        <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $m['ago'] }}</span>
                                        <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'pin']){{ $m['city'] }}</span>
                                        <span class="ad-pill rd-pill"><span>اقساط {{ $faId($m['months']) }} ماه</span><i></i><span>سود {{ $faId($m['rate']) }} درصد</span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="rq-side">
                                <div class="rq-price"><b>{{ $fa($m['price']) }}</b> <small>تومان</small></div>
                                <small class="rd-found">@include('userPanel.partials.icon', ['name' => 'bell']) توسط
                                    «{{ $radarName[$m['radar']] }}» · {{ $m['found'] }}</small>
                            </div>
                        </div>
                        <div class="ad-actions">
                            <a href="{{ url('/ads/' . $m['id']) }}"
                               class="ad-act">@include('userPanel.partials.icon', ['name' => 'eye']) مشاهده آگهی</a>
                            <button type="button" class="ad-act danger" data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-confirm-title="حذف از رادار"
                                    data-confirm-text="«{{ $title }}» از نتایج رادار حذف شود؟"
                                    data-confirm-action="{{ url('/panel/radar/matches/' . $m['id']) }}"
                                    data-confirm-method="DELETE" data-confirm-label="حذف از رادار"
                                    data-confirm-tone="danger">
                                @include('userPanel.partials.icon', ['name' => 'trash']) حذف از رادار
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="rq-empty" id="rdEmpty" hidden>
                <div class="rq-empty-ic"
                     aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'radar'])</div>
                <h3 id="rdEmptyTitle">هنوز وامی پیدا نشده</h3>
                <p>به محض انتشار وامی با شرایط رادار شما، از طریق پیامک و اعلان پنل به شما اطلاع می‌دهیم.</p>
            </div>
        @else
            <div class="rq-empty">
                <div class="rq-empty-ic"
                     aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'radar'])</div>
                <h3>هنوز راداری نساخته‌اید</h3>
                <p>شرایط وام مورد نظرتان را ثبت کنید تا به محض پیدا شدن، به شما اطلاع دهیم.</p>
                <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#radarModal">ساخت
                    اولین رادار
                </button>
            </div>
        @endif
    </section>

    @include('userPanel.partials.radar-modal')
    @include('userPanel.partials.confirm-modal')
@endsection
