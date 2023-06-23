<?php

namespace App\Services;

use App\Models\User;

class PackForUserService
{
    public function isAllowedToBid(User $user)
    {
        return $user->current_allow_bid != 0;
    }
    public function isAllowedToCreateAuction(User $user)
    {
        return $user->current_allow_auction != 0;
    }
    public function isAllowedToCreateNft(User $user)
    {
        return $user->current_allow_nft != 0;
    }

    public function bided(User $user)
    {
        $user->current_allow_bid--;
        $user->save();
        return $user->current_allow_bid;
    }

    public function auctioned(User $user)
    {
        $user->current_allow_auction--;
        $user->save();
        return $user->current_allow_auction;
    }
    public function nfted(User $user)
    {
        $user->current_allow_nft--;
        $user->save();
        return $user->current_allow_nft;
    }
}
