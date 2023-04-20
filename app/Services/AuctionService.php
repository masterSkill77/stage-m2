<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Nft;

class AuctionService
{
    public function store(array $data)
    {
        $auction = new Auction($data);
        $auction->save();

        return $auction;
    }
    public function lists(int | null $perPage = 1)
    {
        return Auction::with(['nft', 'owner', 'bids'])->where('status', 0)->paginate($perPage);
    }
    public function getAuction(int $auctionId): Auction | null
    {
        return Auction::with(['nft', 'owner', 'bids', 'bids.bidder'])->where('id', $auctionId)->first();
    }

    public function changeCurrentBid(Auction $auction, float $bid)
    {
        $auction->current_bid = $bid;
        $auction->save();
        return $auction;
    }
}
