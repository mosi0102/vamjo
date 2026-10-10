@extends('userPanel.layouts.layout')

@section('title', 'ویرایش آگهی')
@section('page_title', 'ویرایش آگهی')
@section('body_class', 'pn-edit')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('ad', 'banks')
    // state: review | rejected → قابل ویرایش | بقیه حالت‌ها فقط‌خواندنی
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $banks = ['بانک جاویدان', 'بانک رسالت', 'بانک صادرات', 'بانک شهر', 'بانک کشاورزی', 'بانک قرض الحسنه مهر ایران', 'بانک تجارت', 'بانک ملت', 'بانک ملی', 'بانک سپه'];
    $placeholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="#e4ebe6"/><rect x="50" y="30" width="100" height="140" rx="10" fill="#fff" stroke="#b9cbc0" stroke-width="4"/><path d="M70 70h60M70 95h60M70 120h40" stroke="#9fb8ab" stroke-width="8" stroke-linecap="round"/></svg>');
    $ad = [
      'id' => 1235899, 'state' => 'rejected', 'note' => 'تصویر موجودی/امتیاز وام خوانا نیست؛ لطفاً تصویر واضح‌تری بارگذاری کنید.',
      'bank' => 'بانک جاویدان', 'amount' => 300000000, 'months' => 60, 'rate' => '4', 'price' => 30000000,
      'sheba' => 'IR062960000000100324200001', 'description' => 'ضامن کارمند رسمی است. معامله در سبزوار انجام می‌شود.',
      'doc_balance' => ['url' => $placeholder, 'name' => 'balance.jpg', 'must_replace' => true],
      'doc_id' => ['url' => $placeholder, 'name' => 'national-card.jpg', 'must_replace' => false],
    ];
    $editable = in_array($ad['state'], ['review', 'rejected']);
    $stateText = ['review' => 'در انتظار ثبت', 'rejected' => 'رد شده', 'transfer' => 'در انتظار انتقال امتیاز', 'completed' => 'تکمیل شده'][$ad['state']] ?? '';
    $title = 'وام ' . $fa($ad['amount']) . ' تومان';
@endphp

@section('main')

    <a href="{{ url('/userPanel/ads') }}"
       class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به آگهی های من</a>

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'edit'])</span>
        <div>
            <h2>ویرایش آگهی</h2>
            <p>اطلاعات آگهی خود را ویرایش کنید؛ آگهی پس از ذخیره دوباره برای تایید اپراتور ارسال می‌شود.</p>
        </div>
        <div class="ed-meta">
            <span class="pn-id">شناسه وام <b>#{{ $faId($ad['id']) }}</b></span>
            <span class="pn-pill {{ $ad['state'] === 'rejected' ? 'rejected' : 'pending' }}">{{ $stateText }}</span>
        </div>
    </div>

    @if (! $editable)
        <section class="pn-panel pn-reveal ed-locked">
            <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'clock'])</div>
            <h3>این آگهی قابل ویرایش نیست</h3>
            <p>آگهی در وضعیت «{{ $stateText }}» قرار دارد و تغییر اطلاعات آن ممکن نیست. برای هرگونه تغییر با پشتیبانی
                تماس بگیرید.</p>
            <a href="{{ url('/panel/ads') }}" class="btn btn-brand">بازگشت به آگهی ها</a>
        </section>
    @else
        <div class="ed-wrap mx-auto">

            @if ($ad['state'] === 'rejected')
                <div class="ed-reject pn-reveal" role="alert">
                    <span class="ed-reject-ic">@include('userPanel.partials.icon', ['name' => 'alert'])</span>
                    <div><b>دلیل رد شدن آگهی</b>
                        <p>{{ $ad['note'] }}</p></div>
                </div>
            @endif

            <form id="adEditForm" action="{{ url('/panel/ads/' . $ad['id']) }}" method="POST"
                  enctype="multipart/form-data" novalidate>
                @csrf @method('PUT')

                {{-- اطلاعات وام --}}
                <section class="f-card pn-reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path
                                    d="M4 21V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v16M15 9h4a1 1 0 0 1 1 1v11M2 21h20M8 8h3M8 12h3M8 16h3"/></svg></span>
                        <h2>اطلاعات وام</h2></header>
                    <div class="f-grid">
                        <div class="f-field" data-field="bank">
                            <label for="edBank">بانک یا صندوق <span class="req">*</span></label>
                            <select id="edBank" name="bank" class="f-input f-select">
                                <option value="" disabled>انتخاب کنید...</option>
                                @foreach ($banks as $b)
                                    <option value="{{ $b }}" @selected($b === $ad['bank'])>{{ $b }}</option>
                                @endforeach
                            </select>
                            <p class="f-err">بانک یا صندوق را انتخاب کنید.</p>
                        </div>
                        <div class="f-field" data-field="amount">
                            <label for="edAmount">مبلغ وام (تومان) <span class="req">*</span></label>
                            <input type="text" id="edAmount" name="amount" class="f-input" data-money
                                   inputmode="numeric" autocomplete="off" value="{{ $fa($ad['amount']) }}"
                                   placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                            <p class="f-err">مبلغ وام را وارد کنید.</p>
                        </div>
                        <div class="f-field" data-field="months">
                            <label for="edMonths">تعداد اقساط (ماه) <span class="req">*</span></label>
                            <input type="text" id="edMonths" name="months" class="f-input" data-int inputmode="numeric"
                                   autocomplete="off" value="{{ $faId($ad['months']) }}" placeholder="مثلا: ۶۰">
                            <p class="f-err">تعداد اقساط را بین ۱ تا ۱۲۰ ماه وارد کنید.</p>
                        </div>
                        <div class="f-field" data-field="rate">
                            <label for="edRate">نرخ سود بانکی (درصد) <span class="req">*</span></label>
                            <input type="text" id="edRate" name="rate" class="f-input" data-decimal inputmode="decimal"
                                   autocomplete="off" value="{{ $faId($ad['rate']) }}" placeholder="مثلا: ۴">
                            <p class="f-err">نرخ سود را بین ۰ تا ۱۰۰ وارد کنید.</p>
                        </div>
                    </div>
                </section>

                {{-- قیمت‌گذاری --}}
                <section class="f-card pn-reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path
                                    d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="8" cy="8"
                                                                                  r="1.5"/></svg></span>
                        <h2>قیمت گذاری</h2></header>
                    <div class="f-grid">
                        <div class="f-field" data-field="price">
                            <label for="edPrice">قیمت پیشنهادی فروش (تومان) <span class="req">*</span></label>
                            <input type="text" id="edPrice" name="price" class="f-input" data-money inputmode="numeric"
                                   autocomplete="off" value="{{ $fa($ad['price']) }}" placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                            <p class="f-err">قیمت پیشنهادی فروش را وارد کنید.</p>
                            <div class="reco-box" id="edReco" aria-live="polite">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/>
                                </svg>
                                <span>توصیه سیستم: <b id="edRecoText">** تا ** میلیون تومان</b></span>
                            </div>
                        </div>
                        <div class="f-field" data-field="sheba">
                            <label for="edSheba">شماره شبا <span class="req">*</span></label>
                            <input type="text" id="edSheba" name="sheba" class="f-input ltr" dir="ltr"
                                   inputmode="numeric" maxlength="26" autocomplete="off" value="{{ $ad['sheba'] }}"
                                   placeholder="IR------------------------">
                            <p class="f-err">شماره شبا باید ۲۴ رقم (بعد از IR) باشد.</p>
                            <p class="f-hint">برای واریز وجه پس از تکمیل معامله</p>
                        </div>
                    </div>
                </section>

                {{-- توضیحات --}}
                <section class="f-card pn-reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path
                                    d="M4 20h4L19 9a2.1 2.1 0 0 0-4-4L4 16zM14 6l4 4M4 6h6M4 10h3"/></svg></span>
                        <h2>توضیحات تکمیلی</h2></header>
                    <div class="f-field" data-field="note">
                        <label for="edNote">شرایط ضامن و توضیحات <span class="req">*</span></label>
                        <textarea id="edNote" name="note" class="f-input" rows="4" maxlength="500"
                                  placeholder="توضیحات مربوط به شرایط ضامن، شهر و نحوه انتقال را بنویسید...">{{ $ad['description'] }}</textarea>
                        <div class="ed-count"><span id="edNoteCount">۰</span> / ۵۰۰</div>
                        <p class="f-err">توضیحات را بنویسید (حداقل ۱۰ کاراکتر).</p>
                    </div>
                </section>

                {{-- مدارک --}}
                <section class="f-card pn-reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path
                                    d="M12 15V4M8 8l4-4 4 4M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg></span>
                        <h2>مدارک</h2></header>
                    <div class="f-grid">
                        @foreach ([['doc_balance', 'تصویر موجودی/امتیاز وام', 'JPG, PNG (حداکثر ۵MB)'], ['doc_id', 'تصویر کارت ملی', 'جهت تطبیق با حساب بانکی']] as [$key, $label, $sub])
                            @php $d = $ad[$key]; $domId = 'ed_' . $key; @endphp
                            <div class="f-field" data-field="{{ $key }}">
                                <label
                                    class="dz ed-dz {{ $d ? 'has-file' : '' }} {{ ! empty($d['must_replace']) ? 'must-replace' : '' }}"
                                    for="{{ $domId }}" tabindex="0"
                                    data-title="{{ $label }}" data-existing-src="{{ $d['url'] ?? '' }}"
                                    data-existing-name="{{ $d['name'] ?? '' }}"
                                    data-must="{{ ! empty($d['must_replace']) ? 1 : 0 }}">
                                    <input type="file" id="{{ $domId }}" name="{{ $key }}" accept="image/png,image/jpeg"
                                           hidden>
                                    <span class="dz-ic">@include('userPanel.partials.icon', ['name' => 'doc'])</span>
                                    <img class="dz-thumb" alt="" src="{{ $d['url'] ?? '' }}" {{ $d ? '' : 'hidden' }}>
                                    <b class="dz-title">{{ $d ? 'تصویر فعلی' : $label }}</b>
                                    <small
                                        class="dz-sub">{{ ! empty($d['must_replace']) ? 'این مدرک رد شده؛ تصویر جدید بارگذاری کنید' : ($d ? 'برای تغییر کلیک کنید یا فایل را بکشید' : $sub) }}</small>
                                    <button type="button" class="dz-remove" aria-label="بازگردانی تصویر قبلی" hidden>×
                                    </button>
                                </label>
                                <p class="f-err">{{ $label }} را بارگذاری کنید.</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- منطقه خطر --}}
                <section class="ed-danger pn-reveal">
                    <div><b>حذف آگهی</b>
                        <p>با حذف آگهی، تمام اطلاعات آن پاک می‌شود و قابل بازگشت نیست.</p></div>
                    <button type="button" class="btn btn-ghost of-reject" data-bs-toggle="modal"
                            data-bs-target="#confirmModal"
                            data-confirm-title="حذف آگهی"
                            data-confirm-text="آیا از حذف «{{ $title }}» مطمئن هستید؟ این کار قابل بازگشت نیست."
                            data-confirm-action="{{ url('/panel/ads/' . $ad['id']) }}" data-confirm-method="DELETE"
                            data-confirm-label="بله، حذف شود" data-confirm-tone="danger">
                        @include('userPanel.partials.icon', ['name' => 'trash']) حذف آگهی
                    </button>
                </section>

                {{-- نوار ذخیره (فقط وقتی تغییری وجود دارد) --}}
                <div class="ed-bar" id="edBar" role="region" aria-label="ذخیره تغییرات" aria-live="polite">
                    <span class="ed-bar-text"><i class="ed-dot" aria-hidden="true"></i><span id="edChanged">تغییرات ذخیره نشده</span></span>
                    <div class="ed-bar-act">
                        <button type="button" class="btn btn-ghost"
                                id="edReset">@include('userPanel.partials.icon', ['name' => 'undo']) بازگردانی
                        </button>
                        <button type="submit" class="btn btn-brand" id="edSave"><span class="btn-label">ذخیره و ارسال برای تایید</span><span
                                class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- مودال خروج بدون ذخیره --}}
    <div class="modal fade app-modal" id="leaveModal" tabindex="-1" aria-labelledby="leaveTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sheet">
            <div class="modal-content">
                <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
                <div class="modal-body text-center">
                    <div class="m-illus round warn"
                         aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'alert'])</div>
                    <h2 class="m-title" id="leaveTitle">تغییرات ذخیره نشده</h2>
                    <p class="m-text">اگر از این صفحه خارج شوید، تغییرات شما از بین می‌رود. می‌خواهید خارج شوید؟</p>
                    <div class="m-actions">
                        <button type="button" class="btn btn-brand btn-lg flex-grow-1" data-bs-dismiss="modal">ماندن در
                            صفحه
                        </button>
                        <a href="#" class="btn btn-outline-danger-soft btn-lg" id="leaveGo" data-no-loader>خروج بدون
                            ذخیره</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('userPanel.partials.confirm-modal')
@endsection
