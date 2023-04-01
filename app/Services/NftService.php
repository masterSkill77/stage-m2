<?php

namespace App\Services;

use App\Models\Nft;

class NftService
{
    public function lists(int | null $perPage = 1)
    {
        return Nft::with(['category', 'owner'])->paginate($perPage);
    }
}
