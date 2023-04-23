<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentService
{
  protected $stripe;
  public function __construct()
  {
    $this->stripe = new \Stripe\StripeClient(
      env('STRIPE_SECRET')
    );
  }
  public function paiement()
  {
    $token = $this->stripe->tokens->create([
      'card' => [
        'number' => '4242424242424242',
        'exp_month' => 12,
        'exp_year' => 2025,
        'cvc' => '123',
      ],
    ]);
    $charge = $this->stripe->charges->create([
      'amount' => 500 * 100,
      'currency' => 'usd',
      'source' => $token->id,
      'description' => 'Paiement pour l\'enchère #123',
    ]);

    return $charge;
  }
}
