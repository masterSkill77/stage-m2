<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Nft;
use Illuminate\Support\Str;

class AuctionService
{
    public function store(array $data)
    {
        $data['auction_uuid'] = Str::uuid();
        $auction = new Auction($data);
        $auction->save();

        return $auction;
    }
    public function lists(int | null $perPage = 1)
    {
        return Auction::with(['nft', 'owner', 'bids'])->where('status', 0)->orderBy('created_at', 'DESC')->paginate($perPage);
    }
    public function getAuction(string $auctionId): Auction | null
    {
        return Auction::with(['nft', 'owner', 'bids', 'bids.bidder'])->where('auction_uuid', $auctionId)->first();
    }

    public function changeCurrentBid(Auction $auction, float $bid)
    {
        $auction->current_bid = $bid;
        $auction->save();
        return $auction;
    }
    public function myAuctions($userId)
    {
        $ownAuctions =  Auction::with(['nft', 'owner', 'bids', 'bids.bidder'])->where('owner_id', $userId)->orderBy('created_at', 'DESC')->get();
        $winAuctions =  Auction::with(['nft', 'owner', 'bids', 'bids.bidder'])->where('winner_id', $userId)->orderBy('created_at', 'DESC')->get();

        return ['win_auctions' => $winAuctions, 'own_auctions' => $ownAuctions];
    }
}
