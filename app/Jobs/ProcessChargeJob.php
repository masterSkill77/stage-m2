<?php

namespace App\Jobs;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessChargeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $auction;
    protected $stripe;
    protected $config;

    /**
     * Create a new job instance.
     *
     * @param  Auction  $auction
     * @return void
     */
    public function __construct(Auction $auction, $stripe, $config)
    {
        $this->auction = $auction;
        $this->stripe = $stripe;
        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $maxAmountAllowedStripe = 999999;
        $amountToCharge = $this->auction->current_bid;

        while ($amountToCharge > 0) {
            $token = $this->stripe->tokens->create([
                'card' => [
                    'number' => $this->config->card_number,
                    'exp_month' => $this->config->card_expires_month,
                    'exp_year' => $this->config->card_expires_year,
                    'cvc' => $this->config->cvc,
                ]
            ]);

            $amount = min($amountToCharge, $maxAmountAllowedStripe);

            $charge = $this->stripe->charges->create([
                'amount' => $amount * 100,
                'currency' => 'cad',
                'source' => $token->id,
                'description' => 'Paiement pour l\'enchère #' . $this->auction->id,
                'transfer_group' => 'ORDER' . $this->auction->id,
                'transfer_data' => [
                    'amount' => $amount * 100,
                    'destination' => 'acct_1N2cL7HC6nuER2HS'
                ],
            ]);

            $amountToCharge -= $amount;

            return $this->auction;
        }
    }
}
