<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Nft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionRulesTest extends TestCase
{
    use RefreshDatabase;

    private function auctionOf(User $owner, array $overrides = []): Auction
    {
        $nft = Nft::factory()->create([
            'owner_id' => $owner->id,
            'category_id' => Category::factory()->create()->id,
        ]);

        return Auction::create(array_merge([
            'owner_id' => $owner->id,
            'nft_id' => $nft->id,
            'auction_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'start_price' => 1,
            'current_bid' => 0,
            'payment' => 'ETH',
            'is_paid' => 0,
        ], $overrides));
    }

    public function test_owner_cannot_bid_on_own_auction(): void
    {
        $owner = User::factory()->create();
        $auction = $this->auctionOf($owner);

        $this->actingAs($owner, 'sanctum')
            ->postJson('/api/bid', ['auction_id' => $auction->auction_uuid, 'bid_amount' => 5])
            ->assertStatus(400)
            ->assertJson(['error' => 'CANNOT_BID_OWN_AUCTION']);

        $this->assertSame(0, Bid::count());
    }

    public function test_bidder_is_always_the_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $bidder = User::factory()->create();
        $auction = $this->auctionOf($owner);

        $this->actingAs($bidder, 'sanctum')
            ->postJson('/api/bid', [
                'auction_id' => $auction->auction_uuid,
                'bid_amount' => 5,
                'bidder_id' => $owner->id, // tentative d'usurpation
            ])
            ->assertOk();

        $this->assertDatabaseHas('bids', ['bidder_id' => $bidder->id, 'bid_amount' => 5]);
        $this->assertDatabaseMissing('bids', ['bidder_id' => $owner->id]);
    }

    public function test_bid_refused_after_end_date(): void
    {
        $owner = User::factory()->create();
        $bidder = User::factory()->create();
        $auction = $this->auctionOf($owner, ['start_date' => now()->subDays(2), 'end_date' => now()->subHour()]);

        $this->actingAs($bidder, 'sanctum')
            ->postJson('/api/bid', ['auction_id' => $auction->auction_uuid, 'bid_amount' => 5])
            ->assertStatus(400)
            ->assertJson(['error' => 'AUCTION_NOT_OPEN']);
    }

    public function test_client_cannot_set_payment_status_or_current_bid_on_creation(): void
    {
        $seller = User::factory()->create();
        $nft = Nft::factory()->create([
            'owner_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->actingAs($seller, 'sanctum')
            ->postJson('/api/auction', [
                'nft_id' => $nft->id,
                'start_date' => now()->addHour()->toIso8601String(),
                'end_date' => now()->addDays(2)->toIso8601String(),
                'start_price' => 3,
                'payment' => 'ETH',
                'is_paid' => 1,
                'current_bid' => 999,
            ])
            ->assertOk();

        $this->assertDatabaseHas('auctions', ['nft_id' => $nft->id, 'is_paid' => 0, 'current_bid' => 0]);
    }

    public function test_auction_end_must_be_after_start(): void
    {
        $seller = User::factory()->create();
        $nft = Nft::factory()->create([
            'owner_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->actingAs($seller, 'sanctum')
            ->postJson('/api/auction', [
                'nft_id' => $nft->id,
                'start_date' => now()->addDays(3)->toIso8601String(),
                'end_date' => now()->addDay()->toIso8601String(),
                'start_price' => 3,
                'payment' => 'ETH',
            ])
            ->assertStatus(422);
    }
}
