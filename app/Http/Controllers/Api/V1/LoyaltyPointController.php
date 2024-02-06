<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\LoyaltyTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyPointController extends Controller
{
    public function __construct(
        private LoyaltyTransaction $loyalty_transaction
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function point_transactions(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $paginator = $this->loyalty_transaction->where('user_id', $request->user()->id)
            ->when(isset($request->type) && ($request->type == 'converted'), function ($query) {
                return $query->where(['transaction_type' => 'loyalty_point_to_wallet']);
            })
            ->when(isset($request->type) && ($request->type == 'earning'), function ($query) {
                return $query->where('transaction_type', '!=', 'loyalty_point_to_wallet');
            })
            ->latest()
            ->paginate($request->limit, ['*'], 'page', $request->offset);

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request->limit,
            'offset' => $request->offset,
            'data' => $paginator->items()
        ];
        return response()->json($data, 200);
    }
}
