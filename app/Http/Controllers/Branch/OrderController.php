<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\User;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CentralLogics\translate;

class OrderController extends Controller
{
    public function __construct(
        private BusinessSetting $business_setting,
        private Order $order,
        private User $user,
        private DeliveryMan $delivery_man
    ){}

    /**
     * @param Request $request
     * @param $status
     * @return Factory|View|Application
     */
    public function list(Request $request, $status): View|Factory|Application
    {
        $query_param = [];
        $search = $request['search'];
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];

        $this->order->where(['checked' => 0, 'branch_id' => auth('branch')->id()])->update(['checked' => 1]);

        if ($status != 'all') {
            $orders = $this->order->with(['customer'])->where(['order_status' => $status, 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    //return $query->whereBetween('created_at', [$start_date, $end_date]);
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                });

        } else {
            $orders = $this->order->with(['customer'])->where(['branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                });
        }
        $query_param = ['start_date' => $start_date,'end_date' => $end_date ];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders=$this->order->where(['branch_id'=>auth('branch')->id()])->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('payment_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                });
            $query_param = ['search' => $request['search']];
        }
        $orders = $orders->notPos()->orderBy('id','desc')->paginate(Helpers::getPagination())->appends($query_param);

        $count_data = [
            'pending' => $this->order->notPos()->where(['order_status'=>'pending', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'confirmed' => $this->order->notPos()->where(['order_status'=>'confirmed', 'branch_id' => auth('branch')->id()])
               ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                   return $query->whereDate('created_at', '>=', $start_date)
                       ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'processing' => $this->order->notPos()->where(['order_status'=>'processing', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'out_for_delivery' => $this->order->notPos()->where(['order_status'=>'out_for_delivery', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'delivered' => $this->order->notPos()->where(['order_status'=>'delivered', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'canceled' => $this->order->notPos()->where(['order_status'=>'canceled', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'returned' => $this->order->notPos()->where(['order_status'=>'returned', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),

            'failed' => $this->order->notPos()->where(['order_status'=>'failed', 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })->count(),
        ];

        return view('branch-views.order.list', compact('orders', 'status','search', 'count_data', 'start_date', 'end_date'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $key = explode(' ', $request['search']);
        $orders=$this->order->where(['branch_id'=>auth('branch')->id()])->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('order_status', 'like', "%{$value}%")
                    ->orWhere('transaction_reference', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view'=>view('branch-views.order.partials._table',compact('orders'))->render()
        ]);
    }

    /**
     * @param $id
     * @return View|Factory|RedirectResponse|Application
     */
    public function details($id): Factory|View|Application|RedirectResponse
    {
        $order = $this->order->with('details')->where(['id' => $id, 'branch_id' => auth('branch')->id()])->first();
        $delivery_man = $this->delivery_man->where(['is_active'=>1])
            ->where(function($query) use ($order) {
                $query->where('branch_id', auth('branch')->id())
                    ->orWhere('branch_id', 0);
            })
            ->get();
        if (isset($order)) {
            return view('branch-views.order.order-view', compact('order', 'delivery_man'));
        } else {
            Toastr::info(translate('No more orders!'));
            return back();
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): \Illuminate\Http\RedirectResponse
    {
        $order = $this->order->where(['id' => $request->id, 'branch_id' => auth('branch')->id()])->first();

        if (in_array($order->order_status, ['delivered', 'failed'])) {
            Toastr::warning(translate('you_can_not_change_the_status_of_a_completed_order'));
            return back();
        }

        if ($request->order_status == 'delivered' && $order['transaction_reference'] == null && !in_array($order['payment_method'],['cash_on_delivery','wallet'])) {
            Toastr::warning(translate('add_your_payment_reference_first'));
            return back();
        }

        if ( $request->order_status == 'out_for_delivery' && $order['delivery_man_id'] == null && $order['order_type'] != 'self_pickup') {
            Toastr::warning(translate('Please assign delivery man first!'));
            return back();
        }

        if ($request->order_status == 'out_for_delivery' && $order['delivery_man_id'] == null && $order['order_type'] != 'self_pickup') {
            Toastr::warning(translate('Please assign delivery man first!'));
            return back();
        }

        if ($request->order_status == 'delivered') {
            if($order->user_id) {
                CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');
            }

            $user = $this->user->find($order->user_id);
            $is_first_order = $this->order->where('user_id', $user->id)->count('id');
            $referred_by_user = $this->user->find($user->referred_by);

            if ($is_first_order < 2 && isset($user->referred_by) && isset($referred_by_user)){
                if($this->business_setting->where('key','ref_earning_status')->first()->value == 1) {
                    CustomerLogic::referral_earning_wallet_transaction($order->user_id, 'referral_order_place', $referred_by_user->id);
                }
            }
        }

        $order->order_status = $request->order_status;
        $order->save();
        $fcm_token = isset($order->customer) ? $order->customer->cm_firebase_token : null;
        $value = Helpers::order_status_update_message($request->order_status);
        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'image' => '',
                    'order_id' => $order->id,
                    'type' => 'order'
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
        } catch (\Exception $e) {
            Toastr::warning(translate('Push notification failed for Customer!'));
        }

        //delivery man notification
        if ($request->order_status == 'processing' && $order->delivery_man != null) {
            $fcm_token = $order->delivery_man->fcm_token;
            $value = translate('One of your order is in processing');
            try {
                if ($value) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order'
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification failed for DeliveryMan!'));
            }
        }

        Toastr::success(translate('Order status updated!'));
        return back();
    }

    /**
     * @param $order_id
     * @param $delivery_man_id
     * @return JsonResponse
     */
    public function add_delivery_man($order_id, $delivery_man_id): \Illuminate\Http\JsonResponse
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }
        $order = $this->order->where(['id' => $order_id, 'branch_id' => auth('branch')->id()])->first();

        if ($order->order_status == 'pending' || $order->order_status == 'confirmed' || $order->order_status == 'delivered' || $order->order_status == 'returned' || $order->order_status == 'failed' || $order->order_status == 'canceled') {
            return response()->json(['status' => false], 200);
        }

        $order->delivery_man_id = $delivery_man_id;
        $order->save();

        $fcm_token = $order->delivery_man->fcm_token;
        $customer_fcm_token = $order->customer->cm_firebase_token;
        $value = Helpers::order_status_update_message('del_assign');
        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order'
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
                $cs_notify_message = Helpers::order_status_update_message('customer_notify_message');
                if($cs_notify_message) {
                    $data['description'] = $cs_notify_message;
                    Helpers::send_push_notif_to_device($customer_fcm_token, $data);
                }
            }
        } catch (\Exception $e) {
            Toastr::warning(\App\CentralLogics\translate('Push notification failed for DeliveryMan!'));
        }

        Toastr::success(translate('Deliveryman successfully assigned/changed'));
        return response()->json(['status' => true], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function payment_status(Request $request): \Illuminate\Http\RedirectResponse
    {
        $order = $this->order->where(['id' => $request->id, 'branch_id' => auth('branch')->id()])->first();
        if ($request->payment_status == 'paid' && $order['transaction_reference'] == null && $order['payment_method'] != 'cash_on_delivery') {
            Toastr::warning(translate('Add your payment reference code first!'));
            return back();
        }
        $order->payment_status = $request->payment_status;
        $order->save();
        Toastr::success(translate('Payment status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update_shipping(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required'
        ]);

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'road' => $request->road,
            'house' => $request->house,
            'floor' => $request->floor,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('customer_addresses')->where('id', $id)->update($address);
        Toastr::success(translate('Payment status updated!'));
        return back();
    }

    /**
     * @param $id
     * @return Factory|View|Application
     */
    public function generate_invoice($id): View|Factory|Application
    {
        $order = $this->order->where(['id' => $id, 'branch_id' => auth('branch')->id()])->first();
        return view('branch-views.order.invoice', compact('order'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function add_payment_ref_code(Request $request, $id): RedirectResponse
    {
        $this->order->where(['id' => $id, 'branch_id' => auth('branch')->id()])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success(translate('Payment reference code is added!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function update_time_slot(Request $request)
    {
        if ($request->ajax()) {
            $order = $this->order->find($request->id);
            $order->time_slot_id = $request->timeSlot;
            $order->save();
            $data = $request->timeSlot;

            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function update_deliveryDate(Request $request)
    {
        if ($request->ajax()) {
            $order = $this->order->find($request->id);
            $order->delivery_date = $request->deliveryDate;
            $order->save();
            $data = $request->deliveryDate;
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @param $status
     * @return StreamedResponse|string
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function export_orders(Request $request, $status): StreamedResponse|string
    {
        $query_param = [];
        $search = $request['search'];
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];

        if ($status != 'all') {
            $orders = $this->order->with(['customer'])->where(['order_status' => $status, 'branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                });
        } else {
            $orders = $this->order->with(['customer'])->where(['branch_id' => auth('branch')->id()])
                ->when((!is_null($start_date) && !is_null($end_date)), function ($query) use ($start_date, $end_date) {
                    return $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                });
        }

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders=$this->order->where(['branch_id'=>auth('branch')->id()])->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('payment_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }

        $orders = $orders->notPos()->orderBy('id','desc')->get();

        $storage = [];

        foreach($orders as $order){
            $branch = $order->branch ? $order->branch->name : '';
            $customer = $order->customer ? $order->customer->f_name .' '. $order->customer->l_name : '';
            //$delivery_address = $order->delivery_address ? $order->delivery_address->address : '';;
            $delivery_man = $order->delivery_man ? $order->delivery_man->f_name .' '. $order->delivery_man->l_name : '';
            $timeslot = $order->time_slot ? $order->time_slot->start_time .' - '. $order->time_slot->end_time : '';

            $storage[] = [
                'order_id' => $order['id'],
                'customer' => $customer,
                'order_amount' => $order['order_amount'],
                'coupon_discount_amount' => $order['coupon_discount_amount'],
                'payment_status' => $order['payment_status'],
                'order_status' => $order['order_status'],
                'total_tax_amount'=>$order['total_tax_amount'],
                'payment_method' => $order['payment_method'],
                'transaction_reference' => $order['transaction_reference'],
                'delivery_man' => $delivery_man,
                'delivery_charge' => $order['delivery_charge'],
                'coupon_code' => $order['coupon_code'],
                'order_type' => $order['order_type'],
                'branch'=>  $branch,
                'time_slot_id' => $timeslot,
                'date' => $order['date'],
                'delivery_date' => $order['delivery_date'],
                'extra_discount' => $order['extra_discount'],
            ];
        }
        return (new FastExcel($storage))->download('orders.xlsx');
    }
}
