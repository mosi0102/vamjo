<div class="sideBar">
    <div class="top">
        <img src="{{asset('website/img/logo2.png')}}" />
        <span>
            پنل مدیریت منو
        </span>
        <div class="sideBarClick">
            <i class="la la-angle-right"></i>
        </div>
    </div>
    <ul class="sideBar-items">
        <li class="sideBar-item">
            <a  class="sideBar-link @if (strpos(Route::currentRouteName(), 'category') !== false) active @endif" href="{{route('category')}}">
                <img src="{{asset('website/img/s1.svg')}}" />
                دسته بندی محصولات
            </a>
        </li>
        <li class="sideBar-item">
            <a  class="sideBar-link @if (strpos(Route::currentRouteName(), 'products') !== false) active @endif" href="{{route('products')}}">
                <img src="{{asset('website/img/s2.svg')}}" />
                محصولات
            </a>
        </li>
        <li class="sideBar-item">
            <a  class="sideBar-link @if (strpos(Route::currentRouteName(), 'customers') !== false) active @endif" href="{{route('customers')}}">
                <img src="{{asset('website/img/s2.svg')}}" />
                کابران
            </a>
        </li>
        <li class="sideBar-item">
            <a  class="sideBar-link logOut " href="/signout">
                <img src="{{asset('website/img/s4.svg')}}" />
                خروج از حساب
            </a>
        </li>
    </ul>
</div>
