<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta content="" name="description"/>
    <meta content="H.A.J" name="author"/>
    <meta name="keywords"
          content="H.A.J"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="google" content="notranslate">
    <meta name="msapplication-TileColor" content="#212121"/>
    <meta name="theme-color" content="#CC0A0A"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="#212121"/>

    <link rel="shortcut icon" href="{{asset('favicon.ico')}}" type="image/x-icon">
    <link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon">

    {{--    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('apple-touch-icon.png')}}">--}}
    {{--    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicon-32x32.png')}}">--}}
    {{--    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicon-16x16.png')}}">--}}

    <meta name="token" content="">
    <title>@yield('title') | حاج اکبر جوجه </title>

    @yield('metaTags')

    <link rel="icon" type="image/x-icon" href="">
    <!-- Styles -->

    <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/line-awesome.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/notyf.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/sweetalert2.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/main.css') }}" type="text/css">

    @stack('style')

</head>

<body class="panel">
@include('panel.__include.sideBar')
<div class="content">

    <main class="main">
        @yield('content')
    </main>

</div>

</body>

<!-- Scripts -->

<script src="{{ asset('website/js/jquery-3.6.4.min.js') }}"></script>
<script src="{{ asset('website/js/bootstrap.bundle.js') }}"></script>
<script src="{{ asset('website/js/notyf.min.js') }}"></script>

@stack('script')
<script src="{{ asset('website/js/sweetalert2.js') }}"></script>
<script src="{{ asset('website/js/main.js') }}"></script>
<script>
    $('.sideBarClick').click(function () {
        $(this).toggleClass('close');
        $('.sideBar').toggleClass('close');
        $('.content').toggleClass('open');
    });
    $('.logOut').click(function () {
        Swal.fire({
            title: "آیا مطمئن به خروج از حساب هستید؟",
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#FEBB15",
            cancelButtonColor: "#d33",
            confirmButtonText: "بله!",
            cancelButtonText: "خیر!"
        }).then((result) => {

        });
    })

</script>
</html>
