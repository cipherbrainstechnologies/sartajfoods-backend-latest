<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

trait ActivationClass
{
    public function dmvf($request)
    {
        if (!self::is_local()) {
            $remove = array("http://", "https://", "www.");
            $url = str_replace($remove, "", url('/'));

            $post = [
                base64_decode('dXNlcm5hbWU=') => $request[base64_decode('dXNlcm5hbWU=')],//un
                base64_decode('cHVyY2hhc2Vfa2V5') => $request[base64_decode('cHVyY2hhc2Vfa2V5')],//pk
                base64_decode('c29mdHdhcmVfaWQ=') => base64_decode(env(base64_decode('U09GVFdBUkVfSUQ='))),//sid
                base64_decode('ZG9tYWlu') => $url
            ];

            try {
                $response = Http::post(base64_decode('aHR0cHM6Ly9jaGVjay42YW10ZWNoLmNvbS9hcGkvdjEvZG9tYWluLWNoZWNr'), $post)->json();
                $status = $response['active'] ?? base64_encode(1);
                if (base64_decode($status)) {
                    Session::put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
                    Session::put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
                    return base64_decode('c3RlcDM=');//s3
                } else {
                    $activation_url = base64_decode('aHR0cHM6Ly9hY3RpdmF0aW9uLjZhbXRlY2guY29t');
                    $activation_url .= '?username=' . $request['username'];
                    $activation_url .= '&purchase_code=' . $request['purchase_key'];
                    $activation_url .= '&domain=' . substr(\Request::root(), 7) .'&';

                    return $activation_url;
                }
            } catch (\Exception $exception) {info($exception);}
        }

        Session::put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
        Session::put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
        return base64_decode('c3RlcDM=');//s3
    }

    public function actch()
    {
        if (!self::is_local()) {
            $remove = array("http://", "https://", "www.");
            $url = str_replace($remove, "", url('/'));

            $post = [
                base64_decode('dXNlcm5hbWU=') => env(base64_decode('QlVZRVJfVVNFUk5BTUU=')),//un
                base64_decode('cHVyY2hhc2Vfa2V5') => env(base64_decode('UFVSQ0hBU0VfQ09ERQ==')),//pk
                base64_decode('c29mdHdhcmVfaWQ=') => base64_decode(env(base64_decode('U09GVFdBUkVfSUQ='))),//sid
                base64_decode('ZG9tYWlu') => $url,
            ];
            try {
                $response = Http::post(base64_decode('aHR0cHM6Ly9jaGVjay42YW10ZWNoLmNvbS9hcGkvdjEvYWN0aXZhdGlvbi1jaGVjaw=='), $post)->json();
                $status = $response['active'] ?? base64_encode(1);
                return (int)base64_decode($status);
            } catch (\Exception $exception) {info($exception);}
        }
        return true;
    }

    public function is_local(): bool
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (!in_array(request()->ip(), $whitelist)) {
            return false;
        }

        return true;
    }
}
