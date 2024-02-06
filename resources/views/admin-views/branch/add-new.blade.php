@extends('layouts.admin.app')

@section('title', translate('Add new branch'))

@push('css_or_js')
    <style>
        #location_map_div #pac-input{
            height: 40px;
            border: 1px solid #fbc1c1;
            outline: none;
            box-shadow: none;
            top: 7px !important;
            transform: translateX(7px);
            padding-left: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/add_branch.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('add New Branch')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-sm-12">
                <form action="{{route('admin.branch.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="tio-user"></i>
                                        {{translate('branch information')}}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label">{{translate('branch_name')}}</label>
                                                        <input type="text" name="name" class="form-control" placeholder="{{ translate('Ex: xyz branch') }}" value="{{ old('name') }}" maxlength="255" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label" for="">{{translate('address')}}</label>
                                                        {{--<input type="text" name="address" class="form-control" placeholder="{{translate('Ex: 666/668 DOHS Mirpur, Dhaka, Bangladesh')}}" value="{{ old('address') }}" required>--}}
                                                        <textarea type="text" name="address" class="form-control h--90px" placeholder="{{translate('Ex: 666/668 DOHS Mirpur, Dhaka, Bangladesh')}}" value="{{ old('address') }}" required>{{ old('address') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="d-flex flex-column justify-content-center h-100">
                                                <div class="text-center mb-3 text--title">
                                                    {{translate('Branch Image')}}
                                                    <small class="text-danger">* ( {{translate('ratio')}} 1:1 )</small>
                                                </div>
                                                <label class="upload--squire">
                                                    <input type="file" name="image" id="customFileEg1" class="" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required hidden>
                                                    <img id="viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" alt="banner image"/>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mt-4">
                                            <div class="row g-3">
                                                <div class="col-sm-6 col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label">{{translate('phone')}}</label>
                                                        <input type="phone" name="phone" class="form-control" value="{{ old('phone') }}"
                                                               maxlength="255" placeholder="{{ translate('EX : +09853834') }}"
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label">{{translate('email')}}</label>
                                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                                               maxlength="255" placeholder="{{ translate('EX : example@example.com') }}"
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label">{{translate('password')}}</label>
                                                        <div class="position-relative">
                                                            <input type="password" name="password" class="form-control" placeholder="{{ translate('Ex: 5+ Character') }}" maxlength="255" value="{{ old('password') }}" required>
                                                            <div class="__right-eye">
                                                                <i class="tio-hidden-outlined"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="tio-poi"></i>
                                        {{translate('branch location')}}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label text-capitalize" for="latitude">{{ translate('latitude') }}
                                                            <i class="tio-info-outined"
                                                               data-toggle="tooltip"
                                                               data-placement="top"
                                                               title="{{ translate('click_on_the_map_select_your_default_location') }}">
                                                            </i>
                                                        </label>
                                                        <input type="text" id="latitude" name="latitude" class="form-control"
                                                               placeholder="{{ translate('Ex:') }} 23.8118428"
                                                               value="{{ old('latitude') }}" required readonly>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label text-capitalize" for="longitude">{{ translate('longitude') }}
                                                            <i class="tio-info-outined"
                                                               data-toggle="tooltip"
                                                               data-placement="top"
                                                               title="{{ translate('click_on_the_map_select_your_default_location') }}">
                                                            </i>
                                                        </label>
                                                        <input type="text" step="0.1" name="longitude" class="form-control"
                                                               placeholder="{{ translate('Ex:') }} 90.356331" id="longitude"
                                                               value="{{ old('longitude') }}" required readonly>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label">
                                                            {{translate('coverage (km)')}}
                                                            <i class="tio-info-outined"
                                                               data-toggle="tooltip"
                                                               data-placement="top"
                                                               title="{{ translate('This value is the radius from your branch location, and customer can order inside  the circle calculated by this radius. The coverage area value must be less or equal than 1000.') }}">
                                                            </i>
                                                        </label>
                                                        <input type="number" name="coverage" min="1" max="1000" class="form-control" placeholder="{{ translate('Ex : 3') }}" value="{{ old('coverage') }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="location_map_div">
                                            <input id="pac-input" class="controls rounded" data-toggle="tooltip"
                                                   data-placement="right"
                                                   data-original-title="{{ translate('search_your_location_here') }}"
                                                   type="text" placeholder="{{ translate('search_here') }}" />
                                            <div id="location_map_canvas" class="overflow-hidden rounded" style="height: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

@endsection

@push('script_2')

    <script src="https://maps.googleapis.com/maps/api/js?key={{ \App\Model\BusinessSetting::where('key', 'map_api_client_key')->first()?->value }}&libraries=places&v=3.45.8"></script>

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

        $("#customFileEg1").change(function () {
            readURL(this);
        });


        $( document ).ready(function() {
            function initAutocomplete() {
                var myLatLng = {

                    lat: 23.811842872190343,
                    lng: 90.356331
                };
                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: {
                        lat: 23.811842872190343,
                        lng: 90.356331
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function(mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    var coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];


                    geocoder.geocode({
                        'latLng': latlng
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('address').innerHtml = results[1].formatted_address;
                            }
                        }
                    });
                });
                // Create the search box and link it to the UI element.
                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                // Bias the SearchBox results towards current map's viewport.
                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });
                let markers = [];
                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place.
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }
                    // Clear out the old markers.
                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];
                    // For each place, get the icon, name and location.
                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var mrkr = new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });
                        google.maps.event.addListener(mrkr, "click", function(event) {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position.lng();
                        });

                        markers.push(mrkr);

                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete();
        });


        $('.__right-eye').on('click', function(){
            if($(this).hasClass('active')) {
                $(this).removeClass('active')
                $(this).find('i').removeClass('tio-invisible')
                $(this).find('i').addClass('tio-hidden-outlined')
                $(this).siblings('input').attr('type', 'password')
            }else {
                $(this).addClass('active')
                $(this).siblings('input').attr('type', 'text')


                $(this).find('i').addClass('tio-invisible')
                $(this).find('i').removeClass('tio-hidden-outlined')
            }
        })
    </script>

@endpush
