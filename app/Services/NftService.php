<?php

namespace App\Services;

use App\Models\Nft;
use App\Models\User;

class NftService
{
    public function __construct(public BlockchainService $blockchainService)
    {
    }
    public function lists(int | null $perPage = 1)
    {
        return Nft::with(['category', 'owner'])->paginate($perPage);
    }
    public function store(array $data, int $userId)
    {
        $data['owner_id'] = $userId;
        $nft = new Nft($data);
        $blockchainNft = $this->blockchainService->createNftOnBlockchain($data);
        $nft->token_id = ($blockchainNft['tokenId']);
        $nft->save();
        return $nft;
    }
    public function myNfts(int $userId, int | null $perPage = 1)
    {
        return Nft::with(['category', 'owner'])->where('owner_id', $userId)->paginate($perPage);
    }
    public function update(array $data, Nft $nft)
    {
        $nft->title = $data['title'];
        $nft->description = $data['description'];
        $nft->image_uri = $data['image_uri'];
        $nft->save();
        return $nft;
    }

    public function affectNft(int $newOwner, int $oldOwner, int $nft)
    {
        $newOwner = User::findOrFail($newOwner);
        $oldOwner = User::findOrFail($oldOwner);
        $nft = Nft::findOrFail($nft);
    }

    public function getNft($idNft)
    {
        return Nft::with(['category', 'owner'])->where('id', $idNft)->first();
    }

    public function getbyCategory(int $idCategory)
    {
        return Nft::with(['category'])->where('category_id', $idCategory)->get();
    }
}
