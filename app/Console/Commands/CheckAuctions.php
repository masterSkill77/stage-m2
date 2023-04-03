<?php

namespace App\Console\Commands;

use App\Mail\AuctionWinner;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auctions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check auctions that have ended and notify winners.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $endedAuctions = Auction::where('end_date', '<=', now())
                ->where('status', 0)
                ->get();

            foreach ($endedAuctions as $auction) {
                $highestBid = Bid::where('auction_id', $auction->id)
                    ->orderBy('bid_amount', 'desc')
                    ->first();
                $auction->status = 1;
                $auction->save();

                if ($highestBid) {
                    $winner = User::find($highestBid->bidder_id);
                    Auction::where('id', $auction->id)->update(['winner_id' => $winner->id]);
                    Mail::to($winner->email)->send(new AuctionWinner($auction));
                }
            }
            DB::commit();
            $this->info(count($endedAuctions) . ' auctions marked as ended.');
        } catch (Exception $e) {
            DB::rollBack();
        };
    }
}
