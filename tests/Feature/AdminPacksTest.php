<?php

namespace Tests\Feature;

use App\Models\Pack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminPacksTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'pack_name' => 'Test Pack',
            'pack_price' => 30,
            'pack_max_nft_creation' => 3,
            'pack_max_auction_creation' => 2,
            'pack_max_bid' => 10,
            'is_active' => true,
        ], $overrides);
    }

    public function test_non_admin_cannot_manage_packs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/admin/packs')->assertStatus(403)->assertJson(['error' => 'ADMIN_ONLY']);
        $this->actingAs($user, 'sanctum')->postJson('/api/admin/packs', $this->payload())->assertStatus(403);
        $this->assertSame(0, Pack::count());
    }

    public function test_anonymous_cannot_manage_packs(): void
    {
        $this->postJson('/api/admin/packs', $this->payload())->assertStatus(401);
    }

    public function test_admin_creates_and_updates_a_pack(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/packs', $this->payload())
            ->assertCreated();

        $pack = Pack::where('pack_name', 'Test Pack')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/admin/packs/' . $pack->id, $this->payload(['pack_price' => 35]))
            ->assertOk();

        $this->assertEquals(35, $pack->fresh()->pack_price);
    }

    public function test_admin_rejects_invalid_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/packs', $this->payload(['pack_price' => 0, 'pack_max_bid' => -1]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pack_price', 'pack_max_bid']);
    }

    public function test_deactivated_pack_is_hidden_from_the_public_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $pack = Pack::create($this->payload(['pack_name' => 'Old pack', 'is_active' => false]));
        Pack::create($this->payload(['pack_name' => 'Live pack', 'pack_price' => 40]));

        $names = collect($this->getJson('/api/pack')->assertOk()->json())->pluck('pack_name');

        $this->assertTrue($names->contains('Live pack'));
        $this->assertFalse($names->contains('Old pack'));
        $this->assertNotNull($pack->id);
        $this->assertNotNull($admin->id);
    }

    public function test_admin_role_cannot_be_granted_at_registration(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        $email = 'mallory' . uniqid() . '@example.test';
        $this->postJson('/api/register', [
            'name' => 'Mallory',
            'lastname' => 'Attacker',
            'username' => 'mallory_' . uniqid(),
            'email' => $email,
            'password' => 'Passw0rd!',
            'etherum_adress' => '0x0000000000000000000000000000000000000001',
            'is_admin' => true,
        ])->assertOk();

        $this->assertDatabaseHas('users', ['email' => $email, 'is_admin' => 0]);
    }

    public function test_make_admin_command_grants_and_revokes_the_role(): void
    {
        $user = User::factory()->create();

        Artisan::call('user:make-admin', ['email' => $user->email]);
        $this->assertTrue((bool) $user->fresh()->is_admin);

        Artisan::call('user:make-admin', ['email' => $user->email, '--revoke' => true]);
        $this->assertFalse((bool) $user->fresh()->is_admin);
    }
}
