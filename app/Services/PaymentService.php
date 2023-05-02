<?php

namespace App\Services;

use App\Jobs\ProcessChargeJob;
use App\Models\Auction;
use App\Models\Nft;
use App\Models\User;
use App\Models\UserConfig;
use Exception;
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
    $fromUser = User::find($auction->owner_id)->first();
    $nft = Nft::where('id', $auction->nft_id)->first();


    if ($config) {
      try {
        $job = new ProcessChargeJob($auction, $this->stripe, $config);

        (new AuctionService)->pay($auction);
        dispatch($job);
        (new BlockchainService)->transfertNftOnBlockchain($fromUser, $user, $nft);
        (new NftService(new BlockchainService))->affectNft($user->id, $fromUser->id, $auction->nft_id);
        return $auction;
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    } else {
      throw new NotFoundHttpException('CONFIG_NOT_FOUND');
    }
  }
}
