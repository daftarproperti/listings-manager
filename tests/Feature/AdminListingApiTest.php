<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminAttention;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminListingApiTest extends TestCase
{
    private int $fakeUserId = 12345;

    private Admin $admin;

    private function addListing(string $address, int $userId, array $fields = []): Listing
    {
        return Listing::factory()->create([
            'user' => [
                'userId' => $userId,
                'source' => 'app',
            ],
            'address' => $address,
        ] + $fields);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.google.bucket_name', 'some-bucket');

        // Ensure each test case starts with empty database.
        Listing::truncate();
        Admin::truncate();
        User::truncate();
        AdminAttention::truncate();

        // Create fake admin
        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'google_id' => 'some-google-id',
        ]);
    }

    public function test_without_auth()
    {
        $response = $this->get(route('listing.index'));

        // User without auth will be redirected to admin login page
        $response->assertRedirect('/admin');
    }

    public function test_with_auth()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('listing.index'));

        $response->assertOk();
    }

    public function test_admin_can_remove_attention_from_listing()
    {
        $listing = $this->addListing("Jl. Rumah Baru", $this->fakeUserId);
        $listing->adminAttentions()->create(['listingId' => $listing->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('listing.removeAttention', $listing->id));

        $response->assertRedirect();
        $this->assertCount(0, $listing->adminAttentions);
    }

    public function test_remove_attention_does_not_trigger_listing_updated()
    {
        $listing = $this->addListing("Jl. Lama", $this->fakeUserId);
        $listing->adminAttentions()->create(['listingId' => $listing->id]);

        Event::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('listing.removeAttention', $listing->id));

        $response->assertRedirect();
        // Assert that the updated observer was not triggered
        Event::assertDispatchedTimes('eloquent.updated: ' . Listing::class, 0);
    }
}
