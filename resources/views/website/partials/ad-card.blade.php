<article class="ad-card">
    <div class="ad-top">
        <img src="{{asset('website/img/bank/'.$a['image'].'.png')}}" style="height: 60px; "/>
        <div class="d-flex flex-column align-items-start">
            <div class="d-flex align-items-end">
                وام
                <div class="ad-amount"> <span class="mx-2">

                    {{ $fa($a['amount']) }} <small>تومان</small>
 </span>
                    {{ ($a['bank_name']) }}
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div><span
                        class="ad-pill"><span>اقساط {{ $fa($a['months']) }} ماه</span><i></i><span>سود {{ $fa($a['rate']) }} درصد</span></span>
                </div>
            </div>
        </div>


    </div>
    <div class="ad-bar"><span style="font-size: 0.88rem">مبلغ خرید:</span><span>{{ $fa($a['buy']) }} تومان</span>
    </div>
    <div class="ad-foot"><span>{{ $fa($a['offers']) }} پیشنهاد ثبت شده</span><a class="d-flex align-items-center"
            href="{{ url('/ads/' . $a['id']) }}">اطلاعات بیشتر

        <i class="la la-arrow-left ms-2"></i>
        </a></div>
</article>
