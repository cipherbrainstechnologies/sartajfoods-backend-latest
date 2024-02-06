@extends('layouts.admin.app')

@section('title', translate('Update Attribute'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/attribute.png')}}" class="w--24" alt="">
                </span>
                <span>
                    {{translate('attribute')}} {{translate('update')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body pt-2">
                <form action="{{route('admin.attribute.update',[$attribute['id']])}}" method="post">
                    @csrf
                    @php($data = Helpers::get_business_settings('language'))
                    @php($default_lang = Helpers::get_default_language())

                    @if($data && array_key_exists('code', $data[0]))
                        
                        <ul class="nav nav-tabs mb-4 d-inline-flex">
                            @foreach($data as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link {{$lang['default'] == true ? 'active':''}}" href="#" id="{{$lang['code']}}-link">
                                        {{ Helpers::get_language_name($lang['code']).'('.strtoupper($lang['code']).')' }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="row">
                            <div class="col-12">
                                @foreach($data as $lang)
                                    <?php
                                        if(count($attribute['translations'])){
                                            $translate = [];
                                            foreach($attribute['translations'] as $t)
                                            {
                                                if($t->locale == $lang['code'] && $t->key=="name"){
                                                    $translate[$lang['code']]['name'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="form-group lang_form {{$lang['default'] == false ? 'd-none':''}}" id="{{$lang['code']}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} ({{strtoupper($lang['code'])}})</label>
                                        <input type="text" name="name[]" class="form-control"
                                               placeholder="{{translate('New attribute')}}"
                                               value="{{$lang['code'] == 'en' ? $attribute['name'] : ($translate[$lang['code']]['name']??'')}}"
                                               {{$lang['status'] == true ? 'required':''}} maxlength="255"
                                               @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group lang_form" id="{{$default_lang}}-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} ({{strtoupper($default_lang)}})</label>
                                    <input type="text" name="name[]" class="form-control" value="{{ $attribute['name'] }}" placeholder="{{translate('New attribute')}}" maxlength="255">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$default_lang}}">
                            </div>
                        </div>
                    @endif
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.split("-")[0];
            console.log(lang);
            $("#"+lang+"-form").removeClass('d-none');
            if(lang == '{{$default_lang}}')
            {
                $(".from_part_2").removeClass('d-none');
            }
            else
            {
                $(".from_part_2").addClass('d-none');
            }
        });
    </script>
@endpush
