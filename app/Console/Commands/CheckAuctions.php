<?php

namespace App\Console\Commands;

use App\Events\AuctionDone;
use App\Mail\AuctionWinner;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Nft;
use App\Models\User;
use App\Services\BlockchainService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


    public function __construct(public BlockchainService $blockchainService)
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $endedAuctions = Auction::with(['nft'])->where('end_date', '<=', now())
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
                    $owner = User::find($auction->owner_id);
                    $nft = Nft::find($auction->nft_id);
                    Nft::where('id', $auction->nft_id)->update(['owner_id' => $winner->id]);
                    Auction::where('id', $auction->id)->update(['winner_id' => $winner->id]);
                    $this->blockchainService->transfertNftOnBlockchain($owner, $winner, $nft);
                    Mail::to($winner->email)->send(new AuctionWinner($auction, $winner));
                }
                try {
                    event(new AuctionDone('auction done'));
                } catch (Exception $e) {
                    Log::debug($e);
                }
            }
            DB::commit();
            $this->info(count($endedAuctions) . ' auctions marked as ended.');
        } catch (Exception $e) {
            Log::debug($e);
            DB::rollBack();
        };
    }
}
