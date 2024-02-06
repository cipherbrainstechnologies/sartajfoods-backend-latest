@extends('layouts.admin.app')

@section('title', translate('Order Details'))

@section('content')

    <div class="content container-fluid">


        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/order.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('order details')}}
                </span>
            </h1>
            <div class="d-flex justify-content-end d-print-none">
                <a class="btn btn-icon btn-sm btn-soft-info rounded-circle mr-1"
                    href="{{route('admin.orders.details',[$order['id']-1])}}"
                    data-toggle="tooltip" data-placement="top" title="Previous order">
                    <i class="tio-arrow-backward"></i>
                </a>
                <a class="btn btn-icon btn-sm btn-soft-info rounded-circle"
                    href="{{route('admin.orders.details',[$order['id']+1])}}" data-toggle="tooltip"
                    data-placement="top" title="Next order">
                    <i class="tio-arrow-forward"></i>
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-8 order-print-area-left">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header flex-wrap align-items-start border-0">
                        <div class="order-invoice-left">
                            <h1 class="page-header-title">
                                <span class="mr-3">{{translate('order ID')}} #{{$order['id']}}</span>
                                <!-- <span class="badge badge-soft-info py-2 px-3">{{$order->branch ? $order->branch->name: 'Branch deleted!'}}</span> -->
                                <span class="badge badge-soft-info py-2 px-3">{{$order->branch ? $order->branch->name: ''}}</span>
                            </h1>
                            <span><i class="tio-date-range"></i>
                                {{date('d M Y',strtotime($order['created_at']))}} {{ date(config('time_format'), strtotime($order['created_at'])) }}</span>
                        </div>
                        <div class="order-invoice-right mt-3 mt-sm-0">
                            <div class="btn--container ml-auto align-items-center justify-content-end">
                                @if($order['order_type']!='self_pickup' && $order['order_type'] != 'pos')
                                    @if($order['order_status']=='out_for_delivery')
                                        @php($origin=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->first())
                                        @php($current=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->latest()->first())
                                        @if(isset($origin))
                                            <a class="btn btn-outline-info font-semibold" target="_blank"
                                            title="Delivery Boy Last Location" data-toggle="tooltip" data-placement="top"
                                            href="https://www.google.com/maps/dir/?api=1&origin={{$origin['latitude']}},{{$origin['longitude']}}&destination={{$current['latitude']}},{{$current['longitude']}}">
                                                <i class="tio-map"></i>
                                                {{translate('Show Location in Map')}}
                                            </a>
                                        @else
                                            <a class="btn btn-outline-info font-semibold" href="javascript:" data-toggle="tooltip"
                                            data-placement="top" title="Waiting for location...">
                                                <i class="tio-map"></i>
                                                {{translate('Show Location in Map')}}
                                            </a>
                                        @endif
                                    @else
                                        <a class="btn btn-outline-info font-semibold" href="javascript:" onclick="last_location_view()"
                                        data-toggle="tooltip" data-placement="top"
                                        title="Only available when order is out for delivery!">
                                            <i class="tio-map"></i>
                                            {{translate('Show Location in Map')}}
                                        </a>
                                    @endif
                                @endif
                                <a class="btn btn--info print--btn" href="javascript:void(0);" onclick="openPrintPreview('{{ route('admin.orders.generate-invoice', ['id' => $order['id'], 'language' => 'en']) }}')">
                                    <i class="tio-print mr-1"></i> {{translate('print')}} {{translate('invoice')}}
                                </a>
                                <a class="btn btn--info print--btn" href="javascript:void(0);" onclick="openPrintPreview('{{ route('admin.orders.generate-invoice', ['id' => $order['id'], 'language' => 'ja']) }}')">
                                    <i class="tio-print mr-1"></i> {{translate('print')}} {{translate('invoice')}}
                                </a>
                                 <a class="btn btn--info print--btn" target="_blank" href="{{route('admin.orders.shpping_list',[$order['id']])}}">
                                    <i class="tio-invisible mr-1"></i> {{translate('shipping_list')}}
                                </a>
                            </div>
                            <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                <h6>
                                    {{translate('Status')}} :
                                    @if($order['order_status']=='pending')
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                        {{translate('pending')}}
                                        </span>
                                    @elseif($order['order_status']=='confirmed')
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                        {{translate('confirmed')}}
                                        </span>
                                    @elseif($order['order_status']=='processing')
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                        {{translate('packaging')}}
                                        </span>
                                    @elseif($order['order_status']=='out_for_delivery')
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                        {{translate('out_for_delivery')}}
                                        </span>
                                    @elseif($order['order_status']=='delivered')
                                        <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                        {{translate('delivered')}}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                        {{str_replace('_',' ',$order['order_status'])}}
                                        </span>
                                    @endif
                                </h6>
                                <h6 class="text-capitalize">
                                    <span class="text-body mr-2">{{translate('payment')}} {{translate('method')}}
                                    :</span> <span class="text--title font-bold">{{ translate(str_replace('_',' ',$order['payment_method']))}}</span>
                                </h6>
                                {{--  <h6 class="text-capitalize">
                                    @if($order['transaction_reference']==null && $order['order_type']!='pos')
                                        <span class="text-body mr-2"> {{translate('reference')}} {{translate('code')}}
                                        :</span>
                                        <button class="btn btn-outline-primary py-1 btn-sm" data-toggle="modal"
                                                data-target=".bd-example-modal-sm">
                                            {{translate('add')}}
                                        </button>
                                    @elseif($order['order_type']!='pos')
                                        <span class="text-body mr-2">{{translate('reference')}} {{translate('code')}}
                                        :</span> <span class="text--title font-bold"> {{$order['transaction_reference']}}</span>
                                    @endif
                                </h6>  --}}
                                <h6>
                                    <span class="text-body mr-2">{{ translate('payment') }} {{ translate('status') }} : </span>

                                    @if($order['payment_status']=='paid')
                                        <span class="badge badge-soft-success ml-sm-3">
                                            {{translate('paid')}}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            {{translate('unpaid')}}
                                        </span>
                                    @endif
                                </h6>
                                <h6 class="text-capitalize">
                                    <span class="text-body">{{translate('order')}} {{translate('type')}}</span>
                                    :<label class="badge badge-soft-primary ml-3">{{ translate(str_replace('_',' ',$order['order_type'])) }}</label>
                                </h6>
                                @if($order['payment_method']=='offline_payment')
                                    <h6 class="text-capitalize">
                                        <span class="text-body mr-2">{{translate('payment')}} {{translate('by')}}
                                        :</span> <span class="text--title font-bold"> {{$order['payment_by']}}</span>
                                    </h6>
                                    <h6 class="text-capitalize">
                                        <span class="text-body mr-2">{{translate('payment')}} {{translate('note')}}
                                        :</span> <span class="text--title font-bold"> {{$order['payment_note']}}</span>
                                    </h6>
                                @endif
                            </div>
                        </div>
                        @if($order['order_type'] != 'pos')
                        <div class="w-100">
                            <h6>
                                <strong>{{translate('order')}} {{translate('note')}}</strong>
                                : <span class="text-body"> {{$order['order_note']}} </span>
                            </h6>
                        </div>
                        @endif
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                    @php($sub_total=0)
                    @php($amount=0)
                    @php($total_tax=0)
                    @php($total_dis_on_pro=0)
                    @php($total_item_discount=0)
                    @php($price_after_discount=0)
                    @php($updated_total_tax=0)
                    @php($vat_status = '')
                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{translate('SL')}}</th>
                                    <th class="border-0">{{translate('Item details')}}</th>
                                    <th class="border-0 text-right">{{translate('Price Per pcs')}}</th>
                                    <th class="border-0 text-right">{{translate('Quantity')}}</th>
                                    <th class="border-0 text-right">{{translate('Discount Per pcs')}}</th>
                                    <th class="text-right border-0">{{translate('Total Price')}}</th>
                                </tr>
                            </thead>
                            @foreach($order->details as $detail)
                               
                                @if($detail->product_details !=null)
                                    @php($product = json_decode($detail->product_details, true))
                                    
                                    <!-- Media -->
                                    <tr>
                                        <td>
                                            {{$loop->iteration}}
                                        </td>
                                        <td>
                                            <div class="media media--sm">
                                                <div class="avatar avatar-xl mr-3">
                                                   {{-- @if($detail->product && $detail->product['image'] != null )--}}
                                                   @if(!empty($product))
                                                  
                                                    {{-- <img class="img-fluid rounded aspect-ratio-1"
                                                         src="{{asset('storage/product')}}/{{json_decode($detail->product['image'],true)[0]?? ''}}"
                                                        onerror="this.src='{{asset('public/assets/admin/img/160x160/2.png')}}'"
                                                        alt="Image Description"> --}}
                                                        <img class="img-fluid rounded aspect-ratio-1"
                                                         src="{{$product['image'][0]?? ''}}"
                                                        onerror="this.src='{{asset('public/assets/admin/img/160x160/2.png')}}'"
                                                        alt="Image Description"> 
                                                    @else
                                                        <img
                                                        src="{{asset('public/assets/admin/img/160x160/2.png')}}"
                                                        class="img-fluid rounded aspect-ratio-1"
                                                        >
                                                    @endif
                                                </div>
                                              {{--  <div class="media-body">
                                                    <h5 class="line--limit-1">{{$product['name']}}</h5>
                                                    @if(count(json_decode($detail['variation'],true)) > 0)
                                                        @foreach(json_decode($detail['variation'],true)[0]??json_decode($detail['variation'],true) as $key1 =>$variation)
                                                            <div class="font-size-sm text-body text-capitalize">
                                                                @if($variation != null)
                                                                <span>{{$key1}} :  </span>
                                                                @endif
                                                                <span class="font-weight-bold">{{$variation}}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <h5 class="mt-1"><span class="text-body">{{translate('Unit Price')}}</span> : {{$detail['price']}} </h5>
                                                    <h5 class="mt-1"><span class="text-body">{{translate('Unit')}}</span> : {{$detail['unit']}} </h5>
                                                    
                                                    <h5 class="mt-1"><span class="text-body">{{translate('QTY')}}</span> : {{$detail['quantity']}} </h5>
                                                </div> --}}
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <h6>{{ Helpers::set_symbol($detail['price'] ) }}</h6>
                                        </td>
                                        <td class="text-right">
                                            <h6>{{  $detail['quantity'] }}</h6>
                                        </td>
                                       
                                       
                                       
                                        <td class="text-right">
                                            <h6>{{ Helpers::set_symbol($detail['discount_on_product']) }}</h6>
                                        </td>
                                        <td class="text-right">
                                            {{--@php($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity'])--}}
                                            @php($amount+=$detail['price']*$detail['quantity'])
                                            @php($total_tax+=$detail['tax_amount']*$detail['quantity'])
                                            @php($updated_total_tax+= $detail['vat_status'] === 'included' ? 0 : $detail['tax_amount']*$detail['quantity'])
                                            @php($vat_status = $detail['vat_status'])
                                            @php($total_item_discount += ($detail['discount_on_product'] * $detail['quantity']))
                                            @php($price_after_discount+=$amount-$total_item_discount)
                                            @php($sub_total+=$price_after_discount)
                                            <!-- <h5>{{ Helpers::set_symbol(($detail['price'] * $detail['quantity']) - ($detail['discount_on_product'] * $detail['quantity'])) }}</h5> -->
                                            <h5>{{ Helpers::set_symbol(($detail['price'] * $detail['quantity'])) }}</h5>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    <tr>
                                        <td colspan="12" class="td-p-0">
                                            <hr class="m-0" >
                                        </td>
                                    </tr>
                            </table>
                        </div>

                        <div class="row justify-content-md-end mb-3 mt-4">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-right justify-content-end">
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('items')}} {{translate('price')}} :
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                            {{--{{ Helpers::set_symbol($sub_total) }}--}}
                                            {{ Helpers::set_symbol($amount) }}
                                    </dd>
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('Item Discount')}} :
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        - {{ Helpers::set_symbol($total_item_discount) }}
                                    </dd>
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('Sub Total')}} :
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        {{--{{ Helpers::set_symbol($price_after_discount) }}--}}
                                        {{ Helpers::set_symbol($total = $amount-$total_item_discount) }}
                                    </dd>
                                    <!-- <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('TAX')}} / {{translate('VAT')}} {{ $vat_status == 'included' ? translate('(included)') : '' }}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        {{ Helpers::set_symbol($total_tax) }}
                                    </dd> -->
                                    @if(!empty($EightPercentTax))          
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('8% Consumption Tax.')}} :
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        {{ Helpers::set_symbol($EightPercentTax) }}
                                    </dd>
                                    @endif
                                    @if(!empty($TenPercentTax))           
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('10% Consumption Tax.')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        {{ Helpers::set_symbol($TenPercentTax) }}
                                    </dd>
                                    @endif
                                    @if($order['order_type'] != 'pos')
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('coupon')}} {{translate('discount')}} :
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        - {{ Helpers::set_symbol($order['coupon_discount_amount']) }}
                                    </dd>
                                    @endif
                                    @if($order['order_type'] == 'pos')
                                        <dt class="col-6 text-left">
                                            <div class="ml-auto max-w-130px">
                                                {{translate('extra Discount')}} :
                                            </div>
                                        </dt>
                                        <dd class="col-6 col-xl-5 pr-5">
                                            - {{ Helpers::set_symbol($order['extra_discount']) }}
                                        </dd>
                                    @endif
                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('delivery')}} {{translate('fee')}} :
                                        </div>
                                    </dt>
                                    <dd class="col-6 col-xl-5 pr-5">
                                        @if($order['order_type']=='self_pickup')
                                            @php($del_c=0)
                                        @else
                                            @php($del_c=$order['delivery_charge'])
                                        @endif
                                        {{ Helpers::set_symbol($del_c) }}
                                        <hr>
                                    </dd>

                                    <dt class="col-6 text-left">
                                        <div class="ml-auto max-w-130px">
                                            {{translate('total')}}:
                                        </div>
                                        </dt>
                                    
                                    <dd class="col-6 col-xl-5 pr-5">{{ Helpers::set_symbol($total+$del_c+$TenPercentTax+$EightPercentTax-$order['coupon_discount_amount']-$order['extra_discount']) }}</dd>
                                </dl>
                                <!-- End Row -->
                            </div>
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->

                 <!-- Card -->
                <div class="card mt-4 mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0">
                        <div class="card--header justify-content-between max--sm-grow">
                            <h5 class="card-title">{{translate('Order History')}}</h5>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{translate('date added')}}</th>
                                    <th class="border-0">{{translate('comment')}}</th>
                                    <th class="border-0">{{translate('status')}}</th>
                                    <th class="text-center border-0">{{translate('customer notified')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($order->history as $history)
                                <tr>
                                    <td>{{ date('d M Y',strtotime($history['created_at'])) }}</td>
                                    {{-- <td>{{ ($history['status'] == "pending") ? date('d M Y',strtotime($history['created_at'])) : $order['delivery_date'] }}</td> --}}
                                    <td>{{ $history['comment'] }}</td>
                                    <td>{{ str_ireplace( array('_'), ' ', $history['status']) }}</td>
                                    <td class="text-center">{{ ($history['is_customer_notify']) ? 'Yes' : 'No' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div class="card mt-4 mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0">
                        <div class="card--header justify-content-between max--sm-grow">
                            <h5 class="card-title">{{translate('Browser History')}}</h5>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{translate('Ip Address')}}</th>
                                    <th class="border-0">{{translate('Forwarded Ip')}}</th>
                                    <th class="border-0">{{translate('User Agent')}}</th>
                                    <th class="text-center border-0">{{translate('Accept Language')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                               
                                <tr>
                                    <td>{{ $order->browser_history["ip_address"] ?? "" }}</td>
                                    <td>{{ $order->browser_history["forwarded_ip"] ?? "" }}</td>
                                    <td>{{ $order->browser_history["user_agent"] ?? ""}}</td>
                                    <td>{{ $order->browser_history["accept_language"] ?? ""}}</td>
                                </tr>
                                
                            </tbody>
                        </table>

                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 order-print-area-right">
                <!-- Card -->
                @if($order['order_type'] != 'pos')
                <div class="card">

                    <div class="card-header border-0 pb-0 justify-content-center">
                        <h4 class="card-title">{{translate('Order Setup')}}</h4>
                    </div>

                    <div class="card-body">
                        @if(($order['order_type'] !='self_pickup') && ($order['order_type'] !='pos'))
                                {{-- @if (!$order->delivery_man)
                                    <div class="mt-3">
                                        <button class="btn btn--primary w-100" type="button" data-target="#assign_delivey_man_modal" data-toggle="modal">{{ translate('assign delivery man manually') }}</button>
                                    </div>
                                @endif --}}
                                @if ($order->delivery_man)
                                    <div class="card mt-2">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3 d-flex flex-wrap align-items-center">
                                    <span class="card-header-icon">
                                        <i class="tio-user"></i>
                                    </span>
                                                <span>{{ translate('deliveryman') }}</span>
                                                @if ($order->order_status != 'delivered')
                                                    <a type="button" href="#assign_delivey_man_modal" class="text--base cursor-pointer ml-auto text-sm"
                                                       data-toggle="modal" data-target="#assign_delivey_man_modal">
                                                        {{ translate('change') }}
                                                    </a>
                                                @endif
                                            </h5>
                                            <div class="media align-items-center deco-none customer--information-single">

                                                <div class="avatar avatar-circle">
                                                    <img class="avatar-img"
                                                         onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
                                                         src="{{ asset('storage/delivery-man/' . $order->delivery_man->image) }}"
                                                         alt="Image Description">
                                                </div>
                                                <div class="media-body">
                                                    <a href="{{ route('admin.delivery-man.preview', [$order->delivery_man['id']]) }}">
                                                        <span class="text-body d-block text-hover-primary mb-1">{{ $order->delivery_man['f_name'] . ' ' . $order->delivery_man['l_name'] }}</span>
                                                    </a>

                                                    <span class="text--title font-semibold d-flex align-items-center">
                                                    <i class="tio-shopping-basket-outlined mr-2"></i>
                                                    {{\App\Model\Order::where('delivery_man_id',$order['delivery_man_id'])->count()}} {{ translate('orders_delivered') }}
                                                    </span>
                                                    <span class="text--title font-semibold d-flex align-items-center">
                                                       <i class="tio-call-talking-quiet mr-2"></i>
                                                        <a href="Tel:{{ $order->delivery_man['phone'] }}">{{ $order->delivery_man['phone'] }}</a>
                                                    </span>
                                                    <span class="text--title font-semibold d-flex align-items-center">
                                                        <i class="tio-email-outlined mr-2"></i>
                                                        <a href="mailto:{{$order->delivery_man['email']}}">{{$order->delivery_man['email']}}</a>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                        @endif


                        @if($order['order_type']!='self_pickup')
                            @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                            @if(isset($address))
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">
                                    <span class="card-header-icon">
                                        <i class="tio-user"></i>
                                    </span>
                                    <span>{{translate('delivery information')}}</span>
                                </h5>
                                @if(isset($address))
                                    <a class="link" data-toggle="modal" data-target="#shipping-address-modal"
                                    href="javascript:"><i class="tio-edit"></i></a>
                                @endif
                            </div>
                            @endif

                            @if(isset($address))
                            <div class="delivery--information-single flex-column mt-3">
                                <div class="d-flex">
                                    <span class="name">
                                        {{translate('name')}}
                                    </span>
                                    <span class="info">{{ $address['contact_person_name']}}</span>
                                </div>
                                <div class="d-flex">
                                    <span class="name">{{translate('phone')}}</span>
                                    <span class="info">{{ $address['contact_person_number']}}</span>
                                </div>
                                <div class="d-flex">
                                    <span class="name">{{translate('road')}}</span>
                                    <span class="info">#{{ $address['road']}}</span>
                                </div>
                                <div class="d-flex">
                                    <span class="name">{{translate('house')}}</span>
                                    <span class="info">#{{ $address['house']}}</span>
                                </div>
                                <div class="d-flex">
                                    <span class="name">{{translate('floor')}}</span>
                                    <span class="info">#{{ $address['floor']}}</span>
                                </div>
                                <hr class="w-100">
                                <div>
                                    <a target="_blank"
                                    href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude']}}+{{$address['longitude']}}">
                                        <i class="tio-poi"></i> {{ $address['address']}}
                                    </a>
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>

                @endif

                    <div class="card mt-2">
                        <div class="card-body">
                            <h5 class="form-label mb-3">
                            <span class="card-header-icon">
                            <i class="tio-user"></i>
                            </span>
                                <span>{{translate('Customer information')}}</span>
                            </h5>
                            @if($order->user_id == null)
                                <div class="media align-items-center deco-none customer--information-single">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img" src="{{asset('public/assets/admin/img/admin.jpg')}}" alt="Image Description">
                                    </div>
                                    <div class="media-body">
                                    <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                        {{translate('Walking Customer')}}
                                    </span>
                                    </div>
                                </div>
                            @endif
                                    @if($order->user_id != null && !isset($order->customer) )
                                        <div class="media align-items-center deco-none customer--information-single">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="{{asset('public/assets/admin/img/admin.jpg')}}" alt="Image Description">
                                            </div>
                                            <div class="media-body">
                                            <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                                {{translate('Customer_not_available')}}
                                            </span>
                                            </div>
                                        </div>
                                    @endif
                            @if(isset($order->customer) )
                                <div class="media align-items-center deco-none customer--information-single">
                                <div class="avatar avatar-circle">
                                    <img class="avatar-img" onerror="this.src='{{asset('public/assets/admin/img/admin.jpg')}}'" src="{{asset('storage/profile/'.$order->customer->image)}}" alt="Image Description">
                                </div>
                                <div class="media-body">
                                    <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                        <a href="{{route('admin.customer.view',[$order['user_id']])}}">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</a>
                                    </span>
                                    <span>{{\App\Model\Order::where('user_id',$order['user_id'])->count()}} {{translate("orders")}}</span>
                                    <span class="text--title font-semibold d-block">
                                <i class="tio-call-talking-quiet mr-2"></i>
                                <a href="Tel:{{$order->customer['phone']}}">{{$order->customer['phone']}}</a>
                            </span>
                                    <span class="text--title">
                                <i class="tio-email mr-2"></i>
                                <a href="mailto:{{$order->customer['email']}}">{{$order->customer['email']}}</a>
                            </span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <!-- End Body -->
                    </div>

                     <!-- <div class="card mt-2"> -->
                        <!-- <div class="card-body"> -->
                            <!-- @if($order['order_type'] != 'pos') -->
                                <!-- <div class="hs-unfold w-100 mt-3">
                                    <span class="d-block form-label font-bold mb-2">{{translate('Payment Status')}}:</span>
                                    <div class="dropdown">
                                        <button class="form-control h--45px dropdown-toggle d-flex justify-content-between align-items-center w-100" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            {{translate($order['payment_status'])}}
                                         </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item"
                                            onclick="route_alert('{{route('admin.orders.payment-status',['id'=>$order['id'],'payment_status'=>'paid'])}}','{{ translate("Change status to paid ?") }}')"
                                            href="javascript:">{{translate('paid')}}</a>
                                            <a class="dropdown-item"
                                            onclick="route_alert('{{route('admin.orders.payment-status',['id'=>$order['id'],'payment_status'=>'unpaid'])}}','{{ translate("Change status to unpaid ?") }}')"
                                            href="javascript:">{{translate('unpaid')}}</a>
                                        </div>
                                    </div>
                                </div> -->
                                <!-- End Unfold -->

                                <!-- <div class="mt-3">
                                    <span class="d-block form-label mb-2 font-bold">{{translate('Delivery Date & Time')}}:</span>
                                    <div class="d-flex flex-wrap g-2">
                                        <div class="hs-unfold w-0 flex-grow min-w-160px">
                                            <label class="input-date">
                                                <input class="js-flatpickr form-control flatpickr-custom min-h-45px" type="text" value="{{ !empty($order['delivery_date']) ? date('d M Y',strtotime($order['delivery_date'])) : date('d M Y',strtotime(now())) }}"
                                                name="deliveryDate" id="from_date" data-id="{{ $order['id'] }}" class="form-control" required>
                                            </label>
                                        </div>
                                        <div class="hs-unfold w-0 flex-grow min-w-160px">
                                            <select class="custom-select custom-select time_slote" name="timeSlot" data-id="{{$order['id']}}">
                                                <option disabled selected>{{translate('select')}} {{translate('Time Slot')}}</option>
                                                @foreach(\App\Model\TimeSlot::all() as $timeSlot)
                                                    <option value="{{$timeSlot['id']}}" {{$timeSlot->id == $order->time_slot_id ?'selected':''}}>{{date(config('time_format'), strtotime($timeSlot['start_time']))}}
                                                        - {{date(config('time_format'), strtotime($timeSlot['end_time']))}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif -->
                        <!-- </div> -->
                    <!-- </div>    -->

                    <div class="card mt-3">

                        <div class="card-header border-0 pb-0 justify-content-center">
                            <h4 class="card-title">{{translate('Order History')}}</h4>
                        </div>

                        <div class="card-body">
                            <div class="hs-unfold w-100">
                                <span class="d-block form-label font-bold mb-2">{{translate('Change Order Status')}}:</span>
                                <div class="hs-unfold w-0 flex-grow w-100">
                                    <select class="custom-select custom-select" name="order_status" id="order_status" data-id="{{$order['id']}}">
                                        <option value="pending" {{ ($order->order_status === 'pending') ? 'selected' : '' }}>{{translate('pending')}}</option>
                                        <option value="confirmed" {{ ($order->order_status === 'confirmed') ? 'selected' : '' }}>{{translate('confirmed')}}</option>
                                        <option value="processing" {{ ($order->order_status === 'processing') ? 'selected' : '' }}>{{translate('processing')}}</option>
                                        <option value="out_for_delivery" {{ ($order->order_status === 'out_for_delivery') ? 'selected' : '' }}>{{translate('out_for_delivery')}}</option>
                                        <option value="delivered" {{ ($order->order_status === 'delivered') ? 'selected' : '' }}>{{translate('delivered')}}</option>
                                        <option value="returned" {{ ($order->order_status === 'returned') ? 'selected' : '' }}>{{translate('returned')}}</option>
                                        <option value="failed" {{ ($order->order_status === 'failed') ? 'selected' : '' }}>{{translate('failed')}}</option>
                                        <option value="canceled" {{ ($order->order_status === 'canceled') ? 'selected' : '' }}>{{translate('canceled')}}</option>
                                    </select>
                                </div>    
                                <div class="mt-3">
                                    <span class="d-block form-label font-bold mb-2">{{translate('Notify Customer')}}</span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <span class="mr-2 switch--custom-label-text text-primary on text-uppercase">on</span>
                                        <span class="mr-2 switch--custom-label-text off text-uppercase">off</span>
                                        <input type="checkbox" name="notify_customer" id="notify_customer" class="toggle-switch-input">
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="mt-3"> 
                                    <span class="d-block form-label font-bold mb-2">{{translate('Comment')}}</span>
                                    <textarea name="comment" id="comment" class="form-control w-100"></textarea>
                                </div>
                                <div class="mt-3">
                                        <button class="btn btn--primary w-100" id="save_order_history" name="save_order_history" type="button" data-id="{{ $order['id'] }}">{{ translate('Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>    

                <!-- End Card -->

                <!-- Branch information -->
                {{-- <div class="card mt-2">
                    <div class="card-body">
                    <h5 class="form-label mb-3">
                        <span class="card-header-icon">
                        <i class="tio-shop-outlined"></i>
                        </span>
                        <span>{{translate('Branch information')}}</span>
                    </h5>
                    
                    <div class="media align-items-center deco-none resturant--information-single">
                            <div class="avatar avatar-circle">
                                <img class="avatar-img w-75px" onerror="this.src='{{asset("public/assets/admin/img/100x100/1.png")}}'" src="{{asset('storage/branch/'.$order->branch->image)}}" alt="Image Description">
                            </div>
                        <div class="media-body">
                            <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                {{$order->branch->name}}
                            </span>
                            <span>{{\App\Model\Order::where('branch_id',$order['branch_id'])->count()}} {{translate('Orders')}}</span>
                            <span class="text--title font-semibold d-block">
                                <i class="tio-call-talking-quiet mr-2"></i>
                                <a href="Tel:{{$order->branch->phone}}">{{$order->branch->phone}}</a>
                            </span>
                            <span class="text--title" >
                                <i class="tio-email mr-2"></i>
                                <a href="mailto:{{$order->branch->email}}">{{$order->branch->email}}</a>
                            </span>
                        </div>
                    </div> 
                    <hr>
                    <span class="d-block">
                        <a target="_blank"
                           href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $order->branch['latitude']}}+{{$order->branch['longitude'] }}">
                            <i class="tio-poi"></i> {{ $order->branch['address']}}
                        </a>
                    </span>
                    </div>

                </div> --}}
                <!-- End Branch information -->
            </div>

        </div>

        <!-- End Row -->
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4"
                        id="mySmallModalLabel">{{translate('reference')}} {{translate('code')}} {{translate('add')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{route('admin.orders.add-payment-ref-code',[$order['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Input Group -->
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="EX : Code123" required>
                        </div>
                        <!-- End Input Group -->
                        <div class="btn--container justify-content-end">
                            <button type="button" class="btn btn-white" data-dismiss="modal">{{translate('close')}}</button>
                            <button class="btn btn--primary" type="submit">{{translate('submit')}}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- Modal -->
    <div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-top-cover bg-dark text-center">
                    <figure class="position-absolute right-0 bottom-0 left-0" style="margin-bottom: -1px;">
                        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                             viewBox="0 0 1920 100.1">
                            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z"/>
                        </svg>
                    </figure>

                    <div class="modal-close">
                        <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal"
                                aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor"
                                      d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- End Header -->

                <div class="modal-top-cover-icon">
                    <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
                      <i class="tio-location-search"></i>
                    </span>
                </div>

                @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                @if(isset($address))
                    <form action="{{route('admin.order.update-shipping',[$order['delivery_address_id']])}}"
                          method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('type')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address_type"
                                           value="{{$address['address_type']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('contact')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_number"
                                           value="{{$address['contact_person_number']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('name')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_name"
                                           value="{{$address['contact_person_name']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('address')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address" value="{{$address['address']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('road')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="road" value="{{$address['road']}}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('house')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="house" value="{{$address['house']}}">
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('floor')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="floor" value="{{$address['floor']}}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('latitude')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="latitude"
                                           value="{{$address['latitude']}}"
                                           required>
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('longitude')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="longitude"
                                           value="{{$address['longitude']}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white"
                                    data-dismiss="modal">{{translate('close')}}</button>
                            <button type="submit"
                                    class="btn btn-primary">{{translate('save')}} {{translate('changes')}}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>


    <div class="modal fade" id="assign_delivey_man_modal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{translate('Assign Delivery Man')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 my-2">
                            <ul class="list-group overflow-auto initial--23">
                                @foreach ($delivery_man as $dm)
                                    <li class="list-group-item">
                                        <span class="dm_list" role='button' data-id="{{ $dm['id'] }}">
                                            <img class="avatar avatar-sm avatar-circle mr-1"
                                                 onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
                                                 src="{{ asset('storage/delivery-man') }}/{{ $dm['image'] }}"
                                                 alt="{{ $dm['f_name'] }}">
                                            {{ $dm['f_name'] }} {{ $dm['l_name'] }}
                                        </span>

                                        <a class="btn btn-primary btn-xs float-right"
                                           onclick="addDeliveryMan({{ $dm['id'] }})">{{ translate('assign') }}</a>
                                    </li>
                                @endforeach

                            </ul>
                        </div>
<!--                        <div class="col-md-7 modal_body_map">
                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->
@endsection

@push('script_2')
    <script>
        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/admin/orders/add-delivery-man/{{$order['id']}}/' + id,
                data: $('#product_form').serialize(),
                success: function (data) {
                    //console.log(data);
                    location.reload();
                    if(data.status == true) {
                        toastr.success('{{ translate("Deliveryman successfully assigned/changed") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else{
                        toastr.error('{{ translate("Deliveryman man can not assign/change in that status") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                },
                error: function () {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function last_location_view() {
            toastr.warning('{{ translate("Only available when order is out for delivery!") }}', {
                CloseButton: true,
                ProgressBar: true
            });
        }
    </script>
    <script>
        $(document).on('change', '#from_date', function () {
            var id = $(this).attr("data-id");
            console.log(id);
            var value = $(this).val();
            console.log(value);
            Swal.fire({
                title: '{{ translate("Are you sure Change this Delivery date?") }}',
                text: "{{ translate("You won't be able to revert this!") }}",
                showCancelButton: true,
                confirmButtonColor: '#01684b',
                cancelButtonColor: 'secondary',
                confirmButtonText: '{{ translate("Yes, Change it!") }}'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.post({
                        url: "{{route('admin.order.update-deliveryDate')}}",

                        data: {
                            "id": id,
                            "deliveryDate": value
                        },

                        success: function (data) {
                            console.log(data);
                            toastr.success('Delivery Date Change successfully');
                            location.reload();
                        }
                    });
                }
            })
        });
        $(document).on('change', '.time_slote', function () {
            var id = $(this).attr("data-id");
            var value = $(this).val();
            Swal.fire({
                title: '{{ translate("Are you sure Change this?") }}',
                text: "{{ translate("You won't be able to revert this!") }}",
                showCancelButton: true,
                confirmButtonColor: '#01684b',
                cancelButtonColor: 'secondary',
                confirmButtonText: 'Yes, Change it!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.post({
                        url: "{{route('admin.order.update-timeSlot')}}",

                        data: {
                            "id": id,
                            "timeSlot": value
                        },

                        success: function (data) {
                            if(data) {
                              toastr.success('{{ translate("Order status changed successfully") }}');
                            }
                        }
                    });
                }
            })
        });
    </script>

    <script>
        function openPrintPreview(url) {
            var printWindow = window.open(url, '_blank');
            
            // Wait for the new window to be loaded before triggering print
            printWindow.onload = function() {
                printWindow.print();
            };
        }
       

        $(document).on('ready', function () {
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF FLATPICKR
            // =======================================================
            $('.js-flatpickr').each(function () {
                $.HSCore.components.HSFlatpickr.init($(this));
            });
        });

    </script>
    <script>
        $("#save_order_history").on('click', function() {
            var id = $(this).attr("data-id");
            var order_status = $('#order_status').val();
            var notify_customer = $('#notify_customer').is(':checked');
            var comment = $('#comment').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: "{{route('admin.order.order_history')}}",
                
                data: {
                    "id": id,
                    "order_status": order_status,
                    "notify_customer": notify_customer,
                    "comment": comment,
                },

                success: function (data) {
                    console.log(data);
                    toastr.success('{{ translate("Oreder history saved successfully") }}');
                    location.reload();
                }
            });     
        });
    </script>

@endpush
