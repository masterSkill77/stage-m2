<?php

namespace App\Services;

use App\Models\Nft;

class NftService
{
    public function lists(int | null $perPage = 1)
    {
        return Nft::with(['category', 'owner'])->paginate($perPage);
    }
    public function store(array $data, int $userId)
    {
        $data['owner_id'] = $userId;
        $nft = new Nft($data);
        $nft->save();
        return $nft;
    }
    public function myNfts(int $userId, int | null $perPage = 1)
    {
        return Nft::with(['category', 'owner'])->where('owner_id', $userId)->paginate($perPage);
    }
}
