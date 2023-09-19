<?php

namespace App\Services;

use App\Dto\TransactionDto;
use App\Jobs\ProcessChargeJob;
use App\Models\Auction;
use App\Models\Nft;
use App\Models\Pack;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserConfig;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService
{
    protected $stripe;
    public function __construct(public TransactionService $transactionService)
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
                if ($auction->payment == Auction::USDT_PAYMENT) {
                    $job = new ProcessChargeJob($auction, $this->stripe, $config);
                    dispatch($job);
                } else {
                    (new BlockchainService)->transfertEthOnBlockchain($fromUser, $user, $auction->current_bid);
                }
                (new AuctionService)->pay($auction);
                (new BlockchainService)->transfertNftOnBlockchain($fromUser, $user, $nft);
                (new NftService(new BlockchainService, new PackForUserService))->affectNft($user->id, $fromUser->id, $auction->nft_id);
                return $auction;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new NotFoundHttpException('CONFIG_NOT_FOUND');
        }
    }
    public function upgradePack(User $user, $packId)
    {
        $config = UserConfig::where('user_id', $user->id)->first();
        $pack = Pack::where("id", $packId)->first();
        if ($config && $pack) {
            try {
                $token = $this->stripe->tokens->create([
                    'card' => [
                        'number' => $config->card_number,
                        'exp_month' => $config->card_expires_month,
                        'exp_year' => $config->card_expires_year,
                        'cvc' => $config->cvc,
                    ]
                ]);
                $charge = $this->stripe->charges->create([
                    'amount' => $pack->pack_price * 100,
                    'currency' => 'cad',
                    'source' => $token->id,
                    'description' => 'Paiement pour le pack #' . $pack->id,
                    'transfer_group' => 'ORDER' . $pack->id,
                    'transfer_data' => [
                        'amount' => $pack->pack_price * 100,
                        'destination' => 'acct_1N2cL7HC6nuER2HS'
                    ],
                ]);
                $user->pack_id = $pack->id;
                $user->current_allow_bid += $pack->pack_max_bid;
                $user->current_allow_auction += $pack->pack_max_auction_creation;
                $user->current_allow_nft += $pack->pack_max_nft_creation;
                $user->save();

                $transaction = new TransactionDto(Transaction::AUCTION_PAYMENT, $this->auction->id, 'ORDER' . $this->auction->id, $this->auction->id, $this->config->user_id);
                $this->transactionService->store($transaction);

                return $charge;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new NotFoundHttpException('CONFIG_NOT_FOUND');
        }
    }
}
