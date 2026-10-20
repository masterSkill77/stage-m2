<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Nft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Seuls les refus sont testés : un paiement réussi déplacerait de l'argent et un NFT sur la chaîne.
class PaymentRulesTest extends TestCase
{
    use RefreshDatabase;

    private function endedAuctionWonBy(User $winner, User $seller, array $overrides = []): Auction
    {
        $nft = Nft::factory()->create([
            'owner_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
        ]);
        $auction = Auction::create(array_merge([
            'owner_id' => $seller->id,
            'nft_id' => $nft->id,
            'auction_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'start_date' => now()->subDays(3),
            'end_date' => now()->subHour(),
            'start_price' => 1,
            'current_bid' => 5,
            'payment' => 'ETH',
            'is_paid' => 0,
        ], $overrides));
        Bid::create(['bidder_id' => $winner->id, 'auction_id' => $auction->id, 'bid_amount' => 5]);

        return $auction;
    }

    public function test_only_the_winner_can_pay(): void
    {
        $seller = User::factory()->create();
        $winner = User::factory()->create();
        $other = User::factory()->create();
        $auction = $this->endedAuctionWonBy($winner, $seller);

        $this->actingAs($other, 'sanctum')
            ->postJson('/api/paiement', ['auction_id' => $auction->id])
            ->assertStatus(403)
            ->assertJson(['error' => 'PAYMENT_NOT_ALLOWED']);
    }

    public function test_cannot_pay_before_auction_ends(): void
    {
        $seller = User::factory()->create();
        $winner = User::factory()->create();
        $auction = $this->endedAuctionWonBy($winner, $seller, ['end_date' => now()->addDay()]);

        $this->actingAs($winner, 'sanctum')
            ->postJson('/api/paiement', ['auction_id' => $auction->id])
            ->assertStatus(403);
    }

    public function test_cannot_pay_twice(): void
    {
        $seller = User::factory()->create();
        $winner = User::factory()->create();
        $auction = $this->endedAuctionWonBy($winner, $seller, ['is_paid' => 1]);

        $this->actingAs($winner, 'sanctum')
            ->postJson('/api/paiement', ['auction_id' => $auction->id])
            ->assertStatus(403);
    }
}
