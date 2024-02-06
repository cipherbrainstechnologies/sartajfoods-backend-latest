@extends('layouts.admin.app')

@section('title', translate('Add new banner'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/banner.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('banner setup')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.banner.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            @if(Request::is('admin/banner/home*')) 
                                <input type="hidden" id="banner_type" name="banner_type" value="home_banner">
                                <input type="hidden" id="is_home_banner" name="is_home_banner" value="1">
                            @else 
                                <input type="hidden" id="is_home_banner" name="is_home_banner" value="0">    
                            @endif
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('English')}} {{translate('title')}}</label>
                                        <input type="text" name="title" value="{{old('title')}}" class="form-control" placeholder="{{translate('English')}} {{translate('title')}}" maxlength="255" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Japanese')}} {{translate('title')}}</label>
                                        <input type="text" name="title_ja" value="{{old('title_ja')}}" class="form-control" placeholder="{{translate('Japanese')}} {{translate('title')}}" maxlength="255" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="link">{{translate('banner_link')}}</label>
                                        <input type="text" name="link" value="{{old('link')}}" class="form-control" placeholder="{{ translate('banner_link') }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="description">{{translate('English')}} {{translate('Description')}}</label>
                                        <!-- <input type="text" name="link" value="{{old('link')}}" class="form-control" placeholder="{{ translate('banner_link') }}"> -->
                                        <textarea class="form-control" name="description" placeholder="{{__('Description')}}">{{old('description')}}</textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="description">{{translate('Japanese')}} {{translate('Description')}}</label>
                                        <!-- <input type="text" name="link" value="{{old('link')}}" class="form-control" placeholder="{{ translate('banner_link') }}"> -->
                                        <textarea class="form-control" name="description_ja" placeholder="{{translate('Japanese')}} {{__('Description')}}">{{old('description_ja')}}</textarea>
                                    </div>
                                </div>
                                @if(Request::is('admin/banner/other*'))
                                <div class="col-12" >
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('ad')}} {{translate('banners')}}</label>
                                        <select name="ad_section" id="ad_section" class="form-control">
                                            <option value="slider_ad_banner">{{translate('slider ad banner')}}</option>
                                            <option value="best_seller_ad">{{translate('best seller ad')}}</option>
                                            <option value="left_section_ad">{{translate('left ad banner')}}</option>
                                            <option value="right_section_ad">{{translate('right ad banner')}}</option>
                                        </select>
                                    </div>
                                </div>
                                @endif
                                @if(Request::is('admin/banner/home*'))
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="order">{{translate('banner_order')}}</label>
                                        <input type="text" name="order" value="{{old('order')}}" class="form-control" placeholder="{{ translate('banner_order') }}">
                                    </div>
                                </div>
                                @endif
                             {{--   @if(!Request::is('admin/banner/home*'))
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label class="input-label" for="exampleFormControlSelect1">{{translate('item')}} {{translate('type')}}<span
                                                    class="input-label-secondary">*</span></label>
                                            <select name="item_type" class="form-control" onchange="show_item(this.value)">
                                                <option value="product">{{translate('product')}}</option>
                                                <option value="category">{{translate('category')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-0" id="type-product">
                                            <label class="input-label" for="exampleFormControlSelect1">{{translate('product')}} <span
                                                    class="input-label-secondary">*</span></label>
                                            <select name="product_id" class="form-control js-select2-custom">
                                                @foreach($products as $product)
                                                    <option value="{{$product['id']}}">{{$product['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-0" id="type-category" style="display: none">
                                            <label class="input-label" for="exampleFormControlSelect1">{{translate('category')}} <span
                                                    class="input-label-secondary">*</span></label>
                                            <select name="category_id" class="form-control js-select2-custom">
                                                @foreach($categories as $category)
                                                    <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif--}} 
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column justify-content-center h-30">
                                <h5 class="text-center mb-3 text--title text-capitalize">
                                    {{translate('banner')}} {{translate('image')}}
                                    <small class="text-danger">* ( {{translate('ratio')}} 3:1 )</small>
                                </h5>
                                <label class="upload--vertical">
                                    <input type="file" name="image" id="customFileEg1" class="" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                    <img id="viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" alt="banner image"/>
                                </label>
                            </div>
                            @if(Request::is('admin/banner/other*'))
                                <div class="d-flex flex-column justify-content-center h-60">
                                    <h5 class="text-center mb-3 text--title text-capitalize">
                                        {{translate('banner')}} {{translate('Logo')}}
                                        <small class="text-danger">* ( {{translate('ratio')}} 3:1 )</small>
                                    </h5>
                                    <label class="upload--vertical">
                                        <input type="file" name="banner_logo" id="banner_logo" class="" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img id="banner-logo-viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" alt="banner image"/>
                                    </label>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <!-- Header -->
            <div class="card-header border-0">
                <div class="card--header justify-content-between max--sm-grow">
                    <h5 class="card-title">{{translate('Banner List')}} <span class="badge badge-soft-secondary">{{ $banners->total() }}</span></h5>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input type="search" name="search" class="form-control"
                                   placeholder="{{translate('Search_by_ID_or_name')}}" aria-label="Search"
                                   value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text">
                                    {{translate('search')}}
                                </button>
                            </div>
                            <div class="col-sm-6 col-md-12 col-lg-4 __btn-row">
                                <a href="{{route('admin.banner.add-new')}}" id="" class="btn w-100 btn--reset min-h-45px">{{translate('clear')}}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0">{{translate('#')}}</th>
                        <th class="border-0">{{translate('banner image')}}</th>
                        @if(Request::is('admin/banner/other*'))
                        <th class="border-0">{{translate('banner logo')}}</th>
                        @endif
                        <th class="border-0">{{translate('English')}} {{translate('title')}}</th>
                        <th class="border-0">{{translate('Japanese')}} {{translate('title')}}</th>
                        <th class="border-0">{{translate('English')}} {{translate('Description')}}</th>
                        <th class="border-0">{{translate('Japanese')}} {{translate('Description')}}</th>
                        <th class="text-center border-0">{{translate('status')}}</th>
                        <th class="text-center border-0">{{translate('action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($banners as $key=>$banner)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                <div>
                                    <img class="img-vertical-150" src="{{asset('storage/banner')}}/{{$banner['image']}}" onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'">
                                </div>
                            </td>
                            @if($banner['type'] != "home_banner")
                            <td>
                                <div>
                                    <img class="img-vertical-150" src="{{asset('storage/banner/logo')}}/{{$banner['banner_logo']}}" onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'">
                                </div>
                            </td>
                            @endif
                            <td>
                                <span class="d-block font-size-sm text-body text-trim-25">
                                    {{$banner['title']}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body text-trim-25">
                                    {{$banner['title_ja']}}
                                </span>
                            </td>
                            <td>
                               {{-- @if($banner['product_id'])
                                    {{ translate('Product') }} : {{$banner->product?$banner->product->name:''}}
                                @elseif($banner['category_id'])
                                    {{ translate('Category') }} : {{$banner->category?$banner->category->name:''}}
                                @endif --}}
                                {{$banner->description}}
                            </td>
                            <td>
                               {{-- @if($banner['product_id'])
                                    {{ translate('Product') }} : {{$banner->product?$banner->product->name:''}}
                                @elseif($banner['category_id'])
                                    {{ translate('Category') }} : {{$banner->category?$banner->category->name:''}}
                                @endif --}}
                                {{$banner->description_ja}}
                            </td>
                            <td>
                                <label class="toggle-switch my-0">
                                    <input type="checkbox"
                                           onclick="status_change_alert('{{ route('admin.banner.status', [$banner->id, $banner->status ? 0 : 1]) }}', '{{ $banner->status? translate('you_want_to_disable_this_banner'): translate('you_want_to_active_this_banner') }}', event)"
                                           class="toggle-switch-input" id="stocksCheckbox{{ $banner->id }}"
                                        {{ $banner->status ? 'checked' : '' }}>
                                    <span class="toggle-switch-label mx-auto text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <!-- Dropdown -->
                                <div class="btn--container justify-content-center">
                                    <a class="action-btn"
                                       href="{{route('admin.banner.edit',[$banner['id']])}}">
                                        <i class="tio-edit"></i></a>
                                    <a class="action-btn btn--danger btn-outline-danger" href="javascript:"
                                       onclick="form_alert('banner-{{$banner['id']}}','{{ translate("Want to delete this") }}')">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('admin.banner.delete',[$banner['id']])}}"
                                      method="post" id="banner-{{$banner['id']}}">
                                    @csrf @method('delete')
                                </form>
                                <!-- End Dropdown -->
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <table>
                    <tfoot>
                    {!! $banners->links() !!}
                    </tfoot>
                </table>

            </div>
            @if(count($banners) == 0)
                <div class="text-center p-4">
                    <img class="w-120px mb-3" src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">
                    <p class="mb-0">{{translate('No_data_to_show')}}</p>
                </div>
            @endif
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function readBannerLogoURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#banner-logo-viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }


        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $("#banner_logo").change(function () {
            readBannerLogoURL(this);
        });

        function show_item(type) {
            if (type === 'product') {
                $("#type-product").show();
                $("#type-category").hide();
            } else {
                $("#type-product").hide();
                $("#type-category").show();
            }
        }

        $(document).ready(function() {
            $('form').on('reset', function(e) {
                $("#type-product").show();
                $("#type-category").hide();
            });
        });
    </script>

    <script>
        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#107980',
                cancelButtonText: 'No',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>

@endpush
