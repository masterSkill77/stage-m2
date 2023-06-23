<?php

namespace App\Services;

use App\Events\BidPlaced;
use App\Exceptions\BidTooLowException;
use App\Models\Bid;
use App\Models\User;
use App\Notifications\NewBidNotification;
use Exception;
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
                $auction = $this->auctionService->getAuction($data['auction_id']);
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
}
