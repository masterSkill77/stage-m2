<?php

namespace App\Services;

use App\Models\Nft;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class BlockchainService
{
    private $url;
    public function __construct()
    {
        $this->url  = config('services.blockchain.url') . '/nft';
    }

    private function http()
    {
        return Http::withHeaders([
            'X-Internal-Token' => config('services.internal.token'),
        ]);
    }
    public function createNftOnBlockchain(array $data, User $user)
    {
        $response = $this->http()->post($this->url, [
            'tokenName' => $data['title'],
            'tokenDescription' => $data['description'],
            'tokenURI' => $data['image_uri'],
            'owner' => $user->etherum_adress
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

    public  function transfertNftOnBlockchain(User $from, User $to, Nft $nft)
    {
        $response = $this->http()->post($this->url . "/transfer", [
            'from' => $from->etherum_adress,
            'to' => $to->etherum_adress,
            'tokenId' => $nft->token_id
        ]);

        if ($response->status() == 200) {
            return $response->json();
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la transfert du NFT.'
            ]);
        }
    }

    public function transfertEthOnBlockchain(User $from, User $to, float $amount)
    {
        $response = $this->http()->post($this->url . "/pay-with-eth", [
            'from' => $from->etherum_adress,
            'to' => $to->etherum_adress,
            'amount' => $amount . ""
        ]);

        if ($response->status() == 200) {
            return $response->json();
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la transfert du NFT.'
            ]);
        }
    }
}
