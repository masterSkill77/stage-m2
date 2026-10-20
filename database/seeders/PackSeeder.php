<?php

namespace Database\Seeders;

use App\Models\Pack;
use Illuminate\Database\Seeder;

class PackSeeder extends Seeder
{
    /**
     * Packs de test. Les quotas du pack s'ajoutent aux quotas actuels de l'utilisateur lors d'un upgrade.
     */
    public function run(): void
    {
        $packs = [
            [
                'pack_name' => 'Starter',
                'pack_price' => 10,
                'pack_max_nft_creation' => 5,
                'pack_max_auction_creation' => 5,
                'pack_max_bid' => 20,
            ],
            [
                'pack_name' => 'Pro',
                'pack_price' => 25,
                'pack_max_nft_creation' => 20,
                'pack_max_auction_creation' => 15,
                'pack_max_bid' => 60,
            ],
            [
                'pack_name' => 'Expert',
                'pack_price' => 50,
                'pack_max_nft_creation' => 50,
                'pack_max_auction_creation' => 40,
                'pack_max_bid' => 150,
            ],
        ];

        foreach ($packs as $pack) {
            Pack::updateOrCreate(['pack_name' => $pack['pack_name']], $pack);
        }
    }
}
