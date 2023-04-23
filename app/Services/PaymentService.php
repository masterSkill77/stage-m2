<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\User;
use App\Models\UserConfig;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService
{
  protected $stripe;
  public function __construct()
  {
    $this->stripe = new \Stripe\StripeClient(
      env('STRIPE_SECRET')
    );
  }
  public function paiement(User $user, Auction $auction)
  {
    $config = UserConfig::where('user_id', $user->id)->first();
    if ($config) {

      $token = $this->stripe->tokens->create([
        'card' => [
          'number' => $config->card_number,
          'exp_month' => $config->card_expires_month,
          'exp_year' => $config->card_expires_year,
          'cvc' => $config->cvc,
        ],
      ]);
      $charge = $this->stripe->charges->create([
        'amount' => $auction->current_bid * 100,
        'currency' => 'usd',
        'source' => $token->id,
        'description' => 'Paiement pour l\'enchère #123',
      ]);

      return $charge;
    } else {
      throw new NotFoundHttpException('config not found');
    }
  }
}
