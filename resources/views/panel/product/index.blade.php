@extends('panel.layouts.layout')
@section('title', 'محصولات')
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
                        <img src="{{asset('website/img/s2.svg')}}"/>
                        محصولات
                    </div>
                </div>
                <div class="col-6">
                    <button class="btn btn-warning openModal ms-auto d-flex">افزودن محصول</button>
                </div>
            </div>
        </div>
    </div>
    <div class="bottomContent">
        @if(count($fs)==0)
            <div class="empty">
                <img src="{{asset('website/img/empty.png')}}">
                <span>
            محصولی تعریف نشده است
        </span>
                <button class="btn btn-warning openModal">افزودن محصول</button>
            </div>
        @endif
        @if(count($fs)>0)
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                @foreach($fgs as $fg)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->iteration==1) active @endif" id="home-tab{{$fg->id}}"
                                data-bs-toggle="tab" data-bs-target="#home-tab-pane{{$fg->id}}" type="button" role="tab"
                                aria-controls="home-tab-pane{{$fg->id}}" aria-selected="true">
                            {{$fg->Title}}</button>
                    </li>
                @endforeach

            </ul>
            <div class="tab-content pt-4" id="myTabContent">
                @foreach($fgs as $fg)
                    <div class="tab-pane fade show  @if($loop->iteration==1) active @endif"
                         id="home-tab-pane{{$fg->id}}" role="tabpanel" aria-labelledby="home-tab{{$fg->id}}"
                         tabindex="{{$loop->index}}">
                        <div class="row">
                            @foreach($fg->foods as $food)
                                <div class="col-lg-4 col-md-6">
                                    <div class="card">
                                        <img src="{{asset('website/img/'.$food->Image)}}" class="card-img-top"
                                             alt="...">
                                        <div class="card-body">
                                            <h5 class="card-title">{{$food->Title}}</h5>
                                            <p class="card-text">{{$food->Description}}</p>
                                            <div class="price">
                                                {{number_format($food->Price,0)}}
                                                <span>تومان</span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end">
                                                <button class="btn btn-light delete" data-id="{{$food->id}}">
                                                    <img src="{{asset('website/img/trash.svg')}}"/>
                                                </button>
                                                <button class="btn btn-light ms-2 edite" data-price="{{$food->Price}}"
                                                        data-category="{{$food->Group_id}}" data-id="{{$food->id}}">
                                                    <img src="{{asset('website/img/edite.svg')}}"/>
                                                </button>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        @endif

    </div>


    <div class="modal" tabindex="-1" id="categoryModal">

        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن محصول</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="product" class="form-label">نام محصول</label>
                                <input type="text" class="form-control" id="product"
                                       placeholder="نام محصول را وارد نمایید">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">قیمت محصول</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control priceInput"
                                           placeholder="قیمت محصول را وارد نمایید"
                                           aria-label="price" aria-describedby="price" id="price">
                                    <span class="input-group-text">تومان</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">دسته محصول</label>
                                <select class="form-select" aria-label="category" id="category">
                                    <option selected>انتخاب دسته محصول</option>
                                    @foreach($fgs as $fg)
                                        <option value="{{$fg->id}}">{{$fg->Title}}</option>
                                    @endforeach
                                </select>


                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="icon" class="form-label">تصویر محصول</label>
                                <input type="file" class="form-control" id="icon">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="text" class="form-label">توضحات محصول</label>
                                <textarea class="form-control" id="text"
                                          placeholder="توضحات محصول را وارد نمایید"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-warning btn-save">ثبت و تایید</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')

    <script>
        var body = $('body');
        body.on('click', '.delete', function () {
            id = $(this).data('id');
            Swal.fire({
                title: "آیا مطمئن به حذف محصول هستید؟",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#FEBB15",
                cancelButtonColor: "#d33",
                confirmButtonText: "بله حذف شود",
                cancelButtonText: "خیر"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/panel/food/remove', {"_token": "{{csrf_token()}}", "id": id}, function () {
                        Swal.fire({
                            title: "حذف شد!",
                            text: "محصول با موفقیت حذف شد",
                            confirmButtonText: "تایید",
                            confirmButtonColor: "#FEBB15",
                            icon: "success"
                        });
                    });
                    $(this).parents('.col-lg-4').remove();
                }
            });
        });
        body.on('click', '.openModal', function () {
            id = 0;
            $('#categoryModal').modal('show');
        });
        body.on('click', '.edite', function () {
            id = $(this).data('id');
            var name = $(this).parents('.card').find('.card-title').text().trim();
            var description = $(this).parents('.card').find('.card-text').text().trim();
            var price = $(this).data('price');
            var Categ = $(this).data('category');
            // var img = $(this).parents('.card').find('.card-title').text().trim();
            $('#categoryModal #product').val(name);
            $('#categoryModal .priceInput').val(price);
            $('#categoryModal #text').val(description);
            $('#categoryModal #category').val(Categ).trigger('change');
            $('#categoryModal').modal('show');
        });
        var id = 0;
        $('.btn-save').click(function () {
            var formData = new FormData();
            formData.append('file', $('#icon')[0].files[0]);
            formData.append('title', $('#product').val());
            formData.append('price', $('#price').val());
            formData.append('text', $('#text').val());
            formData.append('category', $('#category').val());
            formData.append('id', id);
            formData.append('_token', "{{csrf_token()}}");

            $.ajax({
                url: '/panel/food/add',
                type: 'POST',
                data: formData,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                success: function (data) {
                    window.location.reload();
                }
            });

        });
    </script>
@endpush
