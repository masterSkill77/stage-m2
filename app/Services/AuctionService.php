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
}
