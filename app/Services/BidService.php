<?php

namespace App\Services;

use App\Exceptions\BidTooLowException;
use App\Models\Bid;
use Exception;
use Illuminate\Support\Facades\DB;

class BidService
{
    public function __construct(public AuctionService $auctionService)
    {
    }
    public function makeBid(array $data)
    {

        return DB::transaction(function () use ($data) {
            try {
                $auction = $this->auctionService->getAuction($data['auction_id']);
                if ($auction->current_bid >= $data['bid_amount'])
                    throw new BidTooLowException();
                $bid = new Bid($data);
                $bid->save();
                $this->auctionService->changeCurrentBid($auction, $data['bid_amount']);
                $toNotify = $this->getAllBidderExceptMaker($auction->id, $data['bidder_id']);
                // foreach ($toNotify as $user)
                //     return $user->bidder->notify();

                return $bid;
                DB::commit();
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
        )->whereNot('bidder_id', $exceptBidderId)->get();
    }
}
