<?php

namespace App\Services;

use App\Dto\TransactionDto;
use App\Exceptions\PaymentNotAllowedException;
use App\Jobs\ProcessChargeJob;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Nft;
use App\Models\Pack;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserConfig;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService
{
    protected ?\Stripe\StripeClient $stripeClient = null;

    public function __construct(public TransactionService $transactionService)
    {
    }

    // Client Stripe créé à la demande : le paiement en ETH ne doit pas dépendre de la configuration Stripe
    protected function stripe(): \Stripe\StripeClient
    {
        return $this->stripeClient ??= new \Stripe\StripeClient(env('STRIPE_SECRET'));
    }
    /**
     * Paiement d'une enchère terminée : seul le gagnant (plus haute offre) peut payer,
     * une seule fois. L'argent part de l'acheteur vers le vendeur, le NFT du vendeur vers l'acheteur.
     */
    public function paiement(User $user, Auction $auction)
    {
        return DB::transaction(function () use ($user, $auction) {
            // Verrou sur l'enchère : deux paiements simultanés ne peuvent pas passer
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if ($auction->is_paid || !$auction->end_date || now()->lt(Carbon::parse($auction->end_date)))
                throw new PaymentNotAllowedException();

            $winningBid = Bid::where('auction_id', $auction->id)->orderByDesc('bid_amount')->first();
            if (!$winningBid || (int) $winningBid->bidder_id !== (int) $user->id)
                throw new PaymentNotAllowedException();

            $seller = User::findOrFail($auction->owner_id);
            $nft = Nft::findOrFail($auction->nft_id);
            $blockchain = new BlockchainService;

            if ($auction->payment == Auction::USDT_PAYMENT) {
                // Seul le paiement par carte exige une configuration de carte
                $config = UserConfig::where('user_id', $user->id)->first();
                if (!$config) {
                    throw new NotFoundHttpException('CONFIG_NOT_FOUND');
                }
                // Exécuté tout de suite : si le débit échoue, le NFT ne bouge pas
                dispatch_sync(new ProcessChargeJob($auction, $this->stripe(), $config));
            } else {
                $blockchain->transfertEthOnBlockchain($user, $seller, $auction->current_bid);
            }

            $blockchain->transfertNftOnBlockchain($seller, $user, $nft);
            (new AuctionService)->pay($auction);
            (new NftService(new BlockchainService, new PackForUserService))->affectNft($user->id, $seller->id, $auction->nft_id);

            return $auction;
        });
    }
    public function upgradePack(User $user, $packId)
    {
        $config = UserConfig::where('user_id', $user->id)->first();
        $pack = Pack::where("id", $packId)->first();
        if ($config && $pack) {
            try {
                $token = $this->stripe()->tokens->create([
                    'card' => [
                        'number' => $config->card_number,
                        'exp_month' => $config->card_expires_month,
                        'exp_year' => $config->card_expires_year,
                        'cvc' => $config->cvc,
                    ]
                ]);
                $charge = $this->stripe()->charges->create([
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

                $transaction = new TransactionDto(Transaction::PACK_PAYMENT, $pack->id, 'ORDER' . $pack->id, $pack->id, $user->id);
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
