<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Le throttling est vérifié à part : il ne doit pas faire échouer les autres tests
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ada',
            'lastname' => 'Lovelace',
            'username' => 'ada_' . uniqid(),
            'email' => 'ada' . uniqid() . '@example.test',
            'password' => 'Passw0rd!',
            'etherum_adress' => '0x0000000000000000000000000000000000000001',
        ], $overrides);
    }

    public function test_registration_refuses_an_existing_email(): void
    {
        $existing = User::factory()->create();

        $this->postJson('/api/register', $this->registrationPayload(['email' => $existing->email]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_requires_a_password_of_eight_characters(): void
    {
        $this->postJson('/api/register', $this->registrationPayload(['password' => 'short']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_verification_link_must_be_signed(): void
    {
        $user = User::factory()->unverified()->create();

        $this->getJson('/api/verify/' . $user->id)->assertStatus(403);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_signed_verification_link_verifies_the_account(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verify.email', now()->addHour(), ['userId' => $user->id]);

        $this->get($url)->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_public_profile_hides_email_and_quotas(): void
    {
        $viewer = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($viewer, 'sanctum')->getJson('/api/users/info/' . $target->id);

        $response->assertOk()->assertJsonMissing(['email' => $target->email]);
        $this->assertArrayNotHasKey('email', $response->json());
        $this->assertArrayNotHasKey('current_allow_bid', $response->json());
    }

    public function test_own_profile_shows_email_and_quotas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJson(['email' => $user->email])
            ->assertJsonStructure(['current_allow_bid', 'current_allow_nft', 'current_allow_auction']);
    }
}
