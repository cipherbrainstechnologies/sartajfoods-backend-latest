<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\DeliveryHistory;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliverymanController extends Controller
{
    public function __construct(
        private BusinessSetting $business_setting,
        private DeliveryHistory $delivery_history,
        private DeliveryMan $delivery_man,
        private Order $order,
        private User $user
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_profile(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        return response()->json($dm, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_current_orders(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        $orders = $this->order->with(['customer'])->whereIn('order_status', ['pending', 'confirmed', 'processing', 'out_for_delivery'])
            ->where(['delivery_man_id' => $dm['id']])->get();
        return response()->json($orders, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function record_location_data(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        DB::table('delivery_histories')->insert([
            'order_id' => $request['order_id'],
            'deliveryman_id' => $dm['id'],
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'time' => now(),
            'location' => $request['location'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['message' => 'location recorded'], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_order_history(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        $history = $this->delivery_history->where(['order_id' => $request['order_id'], 'deliveryman_id' => $dm['id']])->get();
        return response()->json($history, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update_order_status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        $this->order->where(['id' => $request['order_id'], 'delivery_man_id' => $dm['id']])->update([
            'order_status' => $request['status']
        ]);

        $order=$this->order->find($request['order_id']);
        $fcm_token=$order->customer->cm_firebase_token;

        if ($request['status']=='out_for_delivery'){
            $value=Helpers::order_status_update_message('ord_start');
        }elseif ($request['status']=='delivered'){
            $value=Helpers::order_status_update_message('delivery_boy_delivered');

            if($order->user_id) CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');

            $user = $this->user->find($order->user_id);
            $is_first_order = $this->order->where('user_id', $user->id)->count('id');
            $referred_by_user = $this->user->find($user->referred_by);

            if ($is_first_order < 2 && isset($user->referred_by) && isset($referred_by_user)){
                if($this->business_setting->where('key','ref_earning_status')->first()->value == 1) {
                    CustomerLogic::referral_earning_wallet_transaction($order->user_id, 'referral_order_place', $referred_by_user->id);
                }
            }
        }

        try {
            if ($value){
                $data=[
                    'title'=>'Order',
                    'description'=>$value,
                    'order_id'=>$order['id'],
                    'image'=>'',
                    'type' => 'order'
                ];
                Helpers::send_push_notif_to_device($fcm_token,$data);
            }
        } catch (\Exception $e) {

        }

        return response()->json(['message' => 'Status updated'], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_order_details(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        $order = $this->order->with(['details'])->where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->first();
        $details = $order->details;
        foreach ($details as $det) {
            $det['add_on_ids'] = json_decode($det['add_on_ids']);
            $det['add_on_qtys'] = json_decode($det['add_on_qtys']);
            $det['variation'] = json_decode($det['variation']);
            $det['product_details'] = Helpers::product_data_formatting(json_decode($det['product_details'], true));
        }
        return response()->json($details, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_all_orders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        $orders = $this->order->with(['delivery_address','customer'])->where(['delivery_man_id' => $dm['id']])->get();
        return response()->json($orders, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_last_location(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $last_data = $this->delivery_history->where(['order_id' => $request['order_id']])->latest()->first();
        return response()->json($last_data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function order_payment_status_update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        if ($this->order->where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->first()) {
            $this->order->where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->update([
                'payment_status' => $request['status']
            ]);
            return response()->json(['message' => 'Payment status updated'], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => 'not found!']
            ]
        ], 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update_fcm_token(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = $this->delivery_man->where(['auth_token' => $request['token']])->first();
        if (!isset($dm)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        $this->delivery_man->where(['id' => $dm['id']])->update([
            'fcm_token' => $request['fcm_token']
        ]);

        return response()->json(['message'=>'successfully updated!'], 200);
    }
}
