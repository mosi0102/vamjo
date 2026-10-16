@extends('userPanel.layouts.layout')

@section('title', 'تیکت های پشتیبانی')
@section('page_title', 'تیکت ها')
@section('body_class', 'pn-tickets')

@php
    // status: pending (در انتظار پاسخ) | answered (پاسخ داده شده) | closed (بسته شده)
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $statusMap = ['pending' => ['pending', 'در انتظار پاسخ'], 'answered' => ['approved', 'پاسخ داده شده'], 'closed' => ['expired', 'بسته شده']];
    $prioMap = ['low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
    $tabs = ['all' => 'همه', 'pending' => 'در انتظار پاسخ', 'answered' => 'پاسخ داده شده', 'closed' => 'بسته شده'];
    $counts = collect($tickets)->groupBy('status')->map->count(); $counts['all'] = count($tickets);
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'chat'])</span>
        <div>
            <h2>تیکت های پشتیبانی</h2>
            <p>پیام ها و تیکت های خود را به پشتیبانی وامجو ارسال نمایید و با پشتیبانی گفت و گو داشته باشید.</p>
        </div>
        <a href="{{ route('user.tickets.create') }}" class="btn btn-brand pn-head-act">@include('userPanel.partials.icon', ['name' => 'plus']) تیکت جدید</a>
    </div>

    <section class="pn-panel pn-reveal" style="--d:.08s">

        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $key => $label)
                    <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-group="{{ $key }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}<span class="rq-count">{{ $faId($counts[$key] ?? 0) }}</span></button>
                @endforeach
            </div>
            <label class="rq-search">
                @include('userPanel.partials.icon', ['name' => 'search'])
                <input type="search" id="rqSearch" placeholder="جستجو موضوع یا شناسه تیکت..." autocomplete="off" aria-label="جستجو در تیکت‌ها">
            </label>
        </div>

        <div class="rq-list" id="rqList">
            @forelse ($tickets as $t)
                @php [$pc, $pt] = $statusMap[$t['status']]; @endphp
                <a href="{{ route('user.tickets.show', $t['id']) }}" class="rq-card tk-card tk-{{ $t['status'] }} pn-reveal" data-group="{{ $t['status'] }}" data-search="{{ $t['id'] }} {{ $t['code'] ?? $t['id'] }} {{ $t['title'] }} {{ $t['dept'] }}" style="--d:{{ .12 + $loop->index * .07 }}s">
                    <div class="tk-main">
                        <div class="tk-info">
                            <h3>{{ $t['title'] }}
                                <span class="pn-pill {{ $pc }}">{{ $pt }}</span>
                                @if ($t['unread'])<span class="rd-new">پاسخ جدید</span>@endif
                            </h3>
                            <div class="rq-meta">
                                <span class="tk-id">#{{ $faId($t['code'] ?? $t['id']) }}</span>
                                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $t['date'] }}</span>
                                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'chat']){{ $faId($t['msgs']) }} پیام</span>
                                <span class="tk-tag">{{ $t['dept'] }}</span>
                                <span class="tk-prio {{ $t['priority'] }}">{{ $prioMap[$t['priority']] }}</span>
                            </div>
                        </div>
                        <span class="tk-go">@include('userPanel.partials.icon', ['name' => 'eye']) مشاهده تیکت</span>
                    </div>
                </a>
            @empty
                <div class="rq-empty">
                    <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'chat'])</div>
                    <h3>هنوز تیکتی ثبت نکرده‌اید</h3>
                    <p>برای ارتباط با پشتیبانی، یک تیکت جدید بسازید.</p>
                    <a href="{{ route('user.tickets.create') }}" class="btn btn-brand">تیکت جدید</a>
                </div>
            @endforelse
        </div>

        <div class="rq-empty" id="rqEmpty" hidden>
            <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'chat'])</div>
            <h3>تیکتی پیدا نشد</h3>
            <p>فیلتر یا عبارت جستجو را تغییر دهید.</p>
        </div>
    </section>
@endsection
