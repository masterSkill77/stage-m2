<?php

namespace App\Services;

use App\Jobs\ProcessChargeJob;
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
      $job = new ProcessChargeJob($auction, $this->stripe, $config);

      (new AuctionService)->pay($auction);
      dispatch($job);
      return $auction;
    } else {
      throw new NotFoundHttpException('config not found');
    }
  }
}
