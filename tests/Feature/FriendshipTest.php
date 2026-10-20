<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_stays_pending_until_accepted(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $this->actingAs($alice, 'sanctum')
            ->postJson('/api/users/friends', ['friendId' => $bob->id])
            ->assertCreated()
            ->assertJson(['status' => 'pending']);

        $this->actingAs($bob, 'sanctum')
            ->getJson('/api/users/friends')
            ->assertOk()
            ->assertJsonCount(0);

        $this->actingAs($bob, 'sanctum')
            ->getJson('/api/users/info/' . $alice->id)
            ->assertJson(['friendship' => 'incoming']);

        $this->actingAs($bob, 'sanctum')
            ->postJson('/api/users/friends/' . $alice->id . '/accept')
            ->assertOk()
            ->assertJson(['status' => 'accepted']);

        $this->actingAs($alice, 'sanctum')
            ->getJson('/api/users/info/' . $bob->id)
            ->assertJson(['friendship' => 'friends']);
    }

    public function test_mutual_request_is_accepted_directly(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $this->actingAs($alice, 'sanctum')->postJson('/api/users/friends', ['friendId' => $bob->id]);

        $this->actingAs($bob, 'sanctum')
            ->postJson('/api/users/friends', ['friendId' => $alice->id])
            ->assertJson(['status' => 'accepted']);

        $this->assertSame(1, Contact::count());
    }

    public function test_internal_friendship_check_requires_the_internal_token(): void
    {
        config(['services.internal.token' => 'secret-test-token']);
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Contact::create(['user_id' => $alice->id, 'friend_id' => $bob->id, 'accepted_at' => now()]);

        $this->getJson("/api/internal/friendship/{$alice->id}/{$bob->id}")->assertStatus(403);
        $this->getJson("/api/internal/friendship/{$alice->id}/{$bob->id}", ['X-Internal-Token' => 'wrong'])->assertStatus(403);
        $this->getJson("/api/internal/friendship/{$alice->id}/{$bob->id}", ['X-Internal-Token' => 'secret-test-token'])
            ->assertOk()
            ->assertJson(['friends' => true]);
    }
}
