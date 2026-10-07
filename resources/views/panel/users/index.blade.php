@extends('panel.layouts.layout')
@section('title', 'کاربران')
@section('description')
@endsection
@push('style')

@endpush

@section('content')
    <div class="topContent">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="pageTitle">
                        <img src="{{asset('website/img/s3.svg')}}"/>
                        کاربران
                    </div>
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="#" class="btn btn-success excel ">دریافت خروجی اکسل</a>
                </div>
            </div>
        </div>
    </div>
    <div class="bottomContent">
        @if(count($phones)==0)
            <div class="empty">
                <img src="{{asset('website/img/empty.png')}}">
                <span>
       شماره ای تعریف نشده است
        </span>
            </div>
        @endif
        @if(count($phones)>0)
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th scope="col">ردیف</th>
                    <th scope="col">شماره</th>
                    <th scope="col">تاریخ ثبت</th>
                    <th scope="col">ساعت ثبت</th>
                </tr>
                </thead>
                <tbody>
                @foreach($phones as $phone)
                    <tr>
                        <th scope="row">{{$loop->iteration}}</th>
                        <td>{{$phone->phone}}</td>
                        <td>{{$phone->date}}</td>
                        <td>{{$phone->time}}</td>
                    </tr>
                @endforeach
            </table>
        @endif

    </div>

@endsection

@push('script')

    <script>
        var body = $('body');
        body.on('click', '.excel', function () {
            let timerInterval;
            Swal.fire({
                title: "خروجی اکسل",
                html: "زمان باقیمانده تا دریافت خروجی <b></b>",
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    const timer = Swal.getPopup().querySelector("b");
                    timerInterval = setInterval(() => {
                        timer.textContent = `${Swal.getTimerLeft()}`;
                    }, 100);
                },
                willClose: () => {
                    window.location.href = '/customers/export';
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                /* Read more about handling dismissals below */
                if (result.dismiss === Swal.DismissReason.timer) {
                    console.log("I was closed by the timer");
                }
            });
        });

    </script>
@endpush
