<?php

namespace Tests\Unit;

use Carbon\Carbon;
use App\Models\Listing;
use App\Models\Enums\VerifyStatus;
use App\Models\ListingHistory;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class ListingObserverTest extends TestCase
{
    private User $user;
    private const ADMIN_REVIEW_MESSAGE = "Listing baru akan melalui proses tinjauan oleh admin.\n" .
                                         "Jika ada informasi yang harus diubah, maka akan ditambahkan di catatan ini.\n" .
                                         "Silahkan pantau catatan ini.\n";

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

        Auth::setUser($this->user);
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
        $this->assertEquals($this->user->phoneNumber, $rawListingHistory->actor);
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

        /** @var ListingHistory|null $rawListingHistory */
        $rawListingHistory = ListingHistory::where('listingId', $listing->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertNotNull($rawListingHistory);
        $this->assertEquals($listing->id, $rawListingHistory->listingId);
        $this->assertEquals($this->user->phoneNumber, $rawListingHistory->actor);

        $changes = json_decode($rawListingHistory->changes, true);
        $listing->refresh(); // To get the updated at.
        $expectedChanges = [
            'description' => ['before' => 'Rumah Luas dan sangat bagus', 'after' => 'Rumah Modern dengan desain minimalis'],
            'price' => ['before' => 1000000000, 'after' => 1200000000],
            'adminNote' => [
                'before' => null,
                'after' => [
                    'message' => self::ADMIN_REVIEW_MESSAGE,
                    'email' => 'system@daftarproperti.org',
                    'date' => [
                        '$date' => [
                            '$numberLong' => (string)$listing->adminNote->date->getPreciseTimestamp(3),
                        ]
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedChanges, $changes);
    }

    public function test_impersonator_listing_update_with_history(): void
    {
        $listing = Listing::factory()->make([
            'description' => 'Rumah Luas dan sangat bagus',
            'price' => 1000000000
        ]);

        $listing->save();

        $impersonatorPhoneNumber = '+6281212341234';
        $this->user->setImpersonatedBy($impersonatorPhoneNumber);

        $listing->description = 'Rumah Modern dengan desain minimalis';
        $listing->price = 1200000000;
        $listing->save();

        /** @var ListingHistory|null $rawListingHistory */
        $rawListingHistory = ListingHistory::where('listingId', $listing->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertNotNull($rawListingHistory);
        $this->assertEquals($listing->id, $rawListingHistory->listingId);
        $this->assertEquals($this->user->phoneNumber, $rawListingHistory->actor);
        $this->assertEquals($impersonatorPhoneNumber, $rawListingHistory->impersonator);

        $changes = json_decode($rawListingHistory->changes, true);
        $listing->refresh();
        $expectedChanges = [
            'description' => ['before' => 'Rumah Luas dan sangat bagus', 'after' => 'Rumah Modern dengan desain minimalis'],
            'price' => ['before' => 1000000000, 'after' => 1200000000],
            'adminNote' => [
                'before' => null,
                'after' => [
                    'message' => self::ADMIN_REVIEW_MESSAGE,
                    'email' => 'system@daftarproperti.org',
                    'date' => [
                        '$date' => [
                            '$numberLong' => (string)$listing->adminNote->date->getPreciseTimestamp(3),
                        ]
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedChanges, $changes);
    }
}
