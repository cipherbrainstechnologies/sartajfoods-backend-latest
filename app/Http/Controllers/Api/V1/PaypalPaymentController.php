<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\CentralLogics\Helpers;
use App\Model\Currency;
use App\Model\Order;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Rest\ApiContext;
use App\User;

class PaypalPaymentController extends Controller
{
    public function __construct()
    {
        //configuration initialization
        $mode = env('APP_MODE');
        $paypal = Helpers::get_business_settings('paypal');
        if ($paypal) {
            if ($mode == 'live') {
                $paypal_mode="live";
            }else{
                $paypal_mode="sandbox";
            }

            $config = array(
                'client_id' => $paypal['paypal_client_id'], // values : (local | production)
                'secret' => $paypal['paypal_secret'],
                'settings' => array(
                    'mode' => env('PAYPAL_MODE', $paypal_mode), //live||sandbox
                    'http.ConnectionTimeOut' => 30,
                    'log.LogEnabled' => true,
                    'log.FileName' => storage_path() . '/logs/paypal.log',
                    'log.LogLevel' => 'ERROR'
                ),
            );
            Config::set('paypal', $config);
        }

        //
        $paypal_conf = Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function payWithpaypal(Request $request,$orderId)
    {
        $order_amount = $request['order_amount'];
        $userId = (auth()->user()->id) ? auth()->user()->id : $request['customer_id'];
        $customer = User::find($userId);
        $callback = $request['callback'];

        $tr_ref = Str::random(6) . '-' . rand(1, 1000);

        Session::put('order_id', $orderId);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $items_array = [];
        $item = new Item();
        $item->setName($customer['f_name'])
            ->setCurrency(Helpers::currency_code())
            ->setQuantity(1)
            ->setPrice($order_amount);
        array_push($items_array, $item);
        $item_list = new ItemList();
        $item_list->setItems($items_array);

        $amount = new Amount();
        $amount->setCurrency(Helpers::currency_code())
            ->setTotal($order_amount);

        \session()->put('transaction_reference', $tr_ref);
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription($tr_ref);
        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('api.V1.paypal-status', ['callback' => $callback, 'transaction_reference' => $tr_ref,'order_id' => $orderId]))
            ->setCancelUrl(URL::route('api.V1.payment-fail', ['callback' => $callback, 'transaction_reference' => $tr_ref,'order_id' => $orderId]));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
        try {
            $payment->create($this->_api_context);

            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    $redirect_url = $link->getHref();
                    break;
                }
            }

            Session::put('paypal_payment_id', $payment->getId());
            if (isset($redirect_url)) {
                // return Redirect::away($redirect_url);
                return $redirect_url;
            }

        } catch (\Exception $ex) {
            Toastr::error(translate('Your currency is not supported by PAYPAL.'));
            return back()->withErrors(['error' => 'Failed']);
        }

        Session::put('error', 'Configure your paypal account.');
        return back()->withErrors(['error' => 'Failed']);
    }

    public function getPaymentStatus(Request $request)
    {
        // Get all query string parameters
        $queryParams = $request->query();

        // Alternatively, you can get individual parameters
        $callback = $request->query('callback');
        $transactionReference = $request->query('transaction_reference');
        $payment_id = $request->query('paymentId');
        $token = $request->query('token');
        $payerId = $request->query('PayerID');
        $orderId = $request->query('order_id');

        // $callback = $request['callback'];
        // $transaction_reference = $request['transaction_reference'];

        // $payment_id = Session::get('paypal_payment_id');
        // if (empty($request['PayerID']) || empty($request['token'])) {
        if(empty($payerId) || empty($token)){
            Session::put('error', 'Payment failed');
            return Redirect::back();
        }

        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request['PayerID']);

        /**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);

        //token string generate
        $transaction_reference = $payment_id;
        $token_string = 'payment_method=paypal&&transaction_reference=' . $transaction_reference;

        $order = Order::find($orderId);

        if ($result->getState() == 'approved') {
            $transactionReference = Session::get('transaction_reference');
            $order->payment_status = "paid";
            $order->transaction_reference = $transaction_reference;

            $order->save();

            //success
            if ($callback != null) {
                return redirect($callback . '/success' . '?token=' . base64_encode($token_string));
            } else {
                return \redirect()->route('api.V1.payment-success', ['token' => base64_encode($token_string)]);
            }
        }
        
        //fail
        if ($callback != null) {
            $order->transaction_reference = $transaction_reference;
            $order->payment_status = "fail";
            $order->save();
            return redirect($callback . '/fail' . '?token=' . base64_encode($token_string));
        } else {
            return \redirect()->route('api.V1.payment-fail', ['token' => base64_encode($token_string)]);
        }
    }
}