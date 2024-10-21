<?php

namespace Tests\Unit;

use App\Models\Listing;
use App\Models\Enums\VerifyStatus;
use App\Models\ListingHistory;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;


class ListingObserverTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Listing::truncate();
        User::truncate();

        $this->user = User::factory()->create([
            'user_id' => 12345,
            'name' => 'John Smith',
            'phoneNumber' => '081239129321',
            'password' => null,
            'city' => 'Some City',
            'company' => 'The Company',
            'description' => 'About the user.',
            'picture' => 'some_picture.jpg',
        ]);
    }

    public function test_empty_listing_creation(): void
    {
        $listing = new Listing();
        $listing->address = '';
        $listing->price = '';
        $listing->description = '';

        $listing->save();

        $this->assertNull($listing->id);
    }

    public function test_filled_listing_creation(): void
    {
        $listing = Listing::factory()->create([
            'address' => 'Jln. Baru',
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $this->assertNotNull($listing->id);
    }

    public function test_listing_creation_max_listings_per_user(): void
    {
        Auth::shouldReceive('user')->andReturn($this->user);
        Config::set('services.max_listings_per_user', '1');

        Listing::factory()->create([
            'user' => [
                'userId' => $this->user->user_id,
                'source' => 'app',
            ],
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Untuk sementara batas maksimum listing setiap user adalah 1.");

        Listing::factory()->create([
            'user' => [
                'userId' => $this->user->user_id,
                'source' => 'app',
            ],
            'description' => 'Rumah kedua yang bagus'
        ]);
    }

    public function test_listing_creation_sets_verify_status_and_listing_id(): void
    {
        $listing = Listing::factory()->make([
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $listing->save();

        $this->assertEquals(VerifyStatus::ON_REVIEW, $listing->verifyStatus);
        $this->assertNotNull($listing->listingId);
        $this->assertGreaterThan(0, $listing->listingId);
    }

    public function test_listing_creation_sets_revision(): void
    {
        $listing = Listing::factory()->make([
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $listing->save();

        $this->assertEquals(0, $listing->revision);
        $this->assertNotNull($listing->listingId);
        $this->assertGreaterThan(0, $listing->listingId);
    }

    public function test_listing_creation_with_history(): void
    {
        $listing = Listing::factory()->make([
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $listing->save();
        $rawListingHistory = ListingHistory::where('listingId', $listing->id)->first();

        $this->assertEquals($listing->id, $rawListingHistory->listingId);

    }

    public function test_listing_update_with_history(): void
    {
        $listing = Listing::factory()->make([
            'description' => 'Rumah Luas dan sangat bagus',
            'price' => 1000000000
        ]);

        $listing->save();

        $listing->description = 'Rumah Modern dengan desain minimalis';
        $listing->price = 1200000000;
        $listing->save();

        $rawListingHistory = ListingHistory::where('listingId', $listing->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertNotNull($rawListingHistory);
        $this->assertEquals($listing->id, $rawListingHistory->listingId);

        $changes = json_decode($rawListingHistory->changes, true);
        $expectedChanges = [
            'description' => ['before' => 'Rumah Luas dan sangat bagus', 'after' => 'Rumah Modern dengan desain minimalis'],
            'price' => ['before' => 1000000000, 'after' => 1200000000]
        ];
        $this->assertEquals($expectedChanges, $changes);
    }
}
