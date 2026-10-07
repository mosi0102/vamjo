@extends('panel.layouts.layout')
@section('title', 'دسته بندی')
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
                        <img src="{{asset('website/img/s1.svg')}}"/>
                        دسته بندی محصولات
                    </div>
                </div>
                <div class="col-6">
                    <button class="btn btn-warning openModal ms-auto d-flex">افزودن دسته</button>
                </div>
            </div>
        </div>
    </div>
    <div class="bottomContent">
        @if(count($fgs)==0)
        <div class="empty">
            <img src="{{asset('website/img/empty.png')}}">
            <span>
            دسته بندی تعریف نشده است
        </span>
            <button class="btn btn-warning openModal">افزودن دسته</button>
        </div>
        @endif
        <div class="row">
            @foreach($fgs as $fg)
            <div class="col-lg-4 col-md-6">
                <div class="categoryItem">
                    <div class="details">
                        <div class="icon">
                            <img src="{{asset('website/img/'.$fg->Image)}}"/>
                        </div>
                        <div class="name">
                            {{$fg->Title}}
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-light delete" data-id="{{$fg->id}}">
                            <img src="{{asset('website/img/trash.svg')}}"/>
                        </button>
                        <button class="btn btn-light ms-2 edite" data-id="{{$fg->id}}">
                            <img src="{{asset('website/img/edite.svg')}}"/>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach


        </div>
    </div>




    <div class="modal" tabindex="-1" id="categoryModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن دسته</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">نام دسته</label>
                                <input type="text" class="form-control" id="category"
                                       placeholder="نام دسته را وارد نمایید">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="icon" class="form-label">آیکن دسته</label>
                                <input type="file" class="form-control" id="icon">
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
            id=$(this).data("id");
            Swal.fire({
                title: "آیا مطمئن به حذف هستید؟",
                text: "با حذف دسته بندی، محصولات دسته هم حذف خواهند شد.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#FEBB15",
                cancelButtonColor: "#d33",
                confirmButtonText: "بله حذف شود",
                cancelButtonText: "خیر"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/panel/category/remove',{"_token":"{{csrf_token()}}","id":id},function(){
                        Swal.fire({
                            title: "حذف شد!",
                            text: "دسته بندی با موفقیت حذف شد",
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
            $('#categoryModal').modal('show');
            id=0;
        });
        var id=0;
        body.on('click', '.edite', function () {
            id=$(this).data('id');
            var name = $(this).parents('.categoryItem').find('.name').text().trim();
            $('#categoryModal #category').val(name);
            $('#categoryModal').modal('show');
        });
        $('.btn-save').click(function(){
            var formData = new FormData();
            formData.append('file', $('#icon')[0].files[0]);
            formData.append('title', $('#category').val());
            formData.append('id', id);
            formData.append('_token', "{{csrf_token()}}");

            $.ajax({
                url : '/panel/category/add',
                type : 'POST',
                data : formData,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                success : function(data) {
                    window.location.reload();
                }
            });

        });
    </script>
@endpush
