<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BlockchainService
{
    private $url;
    public function __construct()
    {
        $this->url  = env('APP_NFT_URL', 'http://localhost:3000/nft');
    }
    public function createNftOnBlockchain(array $data)
    {
        $response = Http::post($this->url, [
            'tokenName' => $data['title'],
            'tokenDescription' => $data['description'],
            'tokenURI' => $data['image_uri'],
            'owner' => '0x503Ea162B818f0c044f1Bf4303883b3E458aB444'
        ]);

        if ($response->status() == 200) {
            return $response->json();
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la création du NFT.'
            ]);
        }
    }
}
