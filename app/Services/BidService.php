<?php

namespace App\Services;

use App\Events\BidPlaced;
use App\Exceptions\AuctionNotOpenException;
use App\Exceptions\BidTooLowException;
use App\Exceptions\CannotBidOwnAuctionException;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Notifications\NewBidNotification;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BidService
{
    public function __construct(public AuctionService $auctionService, public PackForUserService $packForUserService)
    {
    }
    public function makeBid(array $data)
    {

        return DB::transaction(function () use ($data) {
            try {
                // Verrou sur l'enchère : deux offres simultanées ne peuvent pas se valider sur le même prix
                $auction = Auction::lockForUpdate()->where('auction_uuid', $data['auction_id'])->firstOrFail();
                if ((int) $auction->owner_id === (int) $data['bidder_id'])
                    throw new CannotBidOwnAuctionException();
                $now = now();
                if (($auction->start_date && $now->lt(Carbon::parse($auction->start_date)))
                    || ($auction->end_date && $now->gt(Carbon::parse($auction->end_date))))
                    throw new AuctionNotOpenException();
                if ($auction->current_bid >= $data['bid_amount'] || $auction->start_price >= $data['bid_amount'])
                    throw new BidTooLowException();
                $data['auction_id'] = $auction->id;
                $bid = new Bid($data);
                $bid->save();
                $this->auctionService->changeCurrentBid($auction, $data['bid_amount']);
                $toNotify = $this->getAllBidderExceptMaker($auction->id, $data['bidder_id']);

                $user = User::find($data['bidder_id']);
                $this->packForUserService->bided($user);
                foreach ($toNotify as $user)
                    $user->bidder->notify(new NewBidNotification($auction, $user->bidder));
                DB::commit();
                event(new BidPlaced());
                return $bid;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    /**
     * Retourne la liste de ceux qui ont fait un bid excepté celui qui a fait le dernier bid
     *
     */
    public function getAllBidderExceptMaker($auctionId, $exceptBidderId)
    {
        return Bid::with('bidder')->where(
            'auction_id',
            $auctionId
        )->whereNot('bidder_id', $exceptBidderId)->groupBy('bidder_id')->get();
    }

    public function getAllBidForUser(int $userId)
    {
        return Bid::with('auction')->where('bidder_id', $userId)->get();
    }
}
