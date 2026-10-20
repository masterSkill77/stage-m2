<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Nft;
use App\Models\User;
use App\Models\UserConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    // Petit PNG valide (1x1) : évite d'exiger l'extension GD dans le conteneur
    private function png(string $name): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_avatar_is_stored_and_referenced_in_the_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/settings/avatar', ['avatar' => $this->png('me.png')])
            ->assertOk();

        $config = UserConfig::where('user_id', $user->id)->first();
        $images = json_decode($config->profile_image, true);
        $this->assertStringContainsString('/storage/avatars/', $images[0]['content']);
        $this->assertCount(1, Storage::disk('public')->files('avatars'));
    }

    public function test_avatar_refuses_a_non_image_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/settings/avatar', ['avatar' => UploadedFile::fake()->create('cv.pdf', 10)])
            ->assertStatus(422);
    }

    public function test_nft_image_upload_returns_a_public_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/upload/nft-image', ['image' => $this->png('art.png')])
            ->assertOk()
            ->assertJsonStructure(['url']);
    }

    public function test_notification_summary_counts_friend_requests_and_unpaid_wins(): void
    {
        $me = User::factory()->create();
        $seller = User::factory()->create();
        $requester = User::factory()->create();

        Contact::create(['user_id' => $requester->id, 'friend_id' => $me->id]);

        $nft = Nft::factory()->create(['owner_id' => $seller->id, 'category_id' => Category::factory()->create()->id]);
        $won = Auction::create([
            'owner_id' => $seller->id,
            'nft_id' => $nft->id,
            'auction_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'start_date' => now()->subDays(2),
            'end_date' => now()->subHour(),
            'start_price' => 1,
            'current_bid' => 5,
            'payment' => 'ETH',
            'is_paid' => 0,
        ]);
        Bid::create(['bidder_id' => $me->id, 'auction_id' => $won->id, 'bid_amount' => 5]);

        $this->actingAs($me, 'sanctum')
            ->getJson('/api/notifications/summary')
            ->assertOk()
            ->assertJson(['friend_requests' => 1, 'won_unpaid' => 1]);
    }
}
