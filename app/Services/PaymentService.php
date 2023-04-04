<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentService
{
    protected $provider;
    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->getAccessToken();
    }
    public function test()
    {
        $data = json_decode('{
            "intent": "CAPTURE",
            "purchase_units": [
              {
                "amount": {
                  "currency_code": "USD",
                  "value": "100.00"
                }
              }
            ]
        }', true);

        $order = $this->provider->createOrder($data);
        // $order = $this->provider->authorizePaymentOrder($order['id']);

        return $this->provider->showProfileInfo();
        return $order;
    }
}
