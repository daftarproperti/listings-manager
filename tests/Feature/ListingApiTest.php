<?php

namespace Tests\Feature;

use App\Models\AdminAttention;
use App\Models\Listing;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ListingApiTest extends TestCase
{
    private string $fakeBotToken = 'fake-bot-token';
    private int $fakeUserId = 12345;

    private User $user;
    private string $token;

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
        User::truncate();
        AdminAttention::truncate();

        // Create fake user that will use token authentication
        $this->user = User::factory()->create([
            'user_id' => $this->fakeUserId,
            'name' => 'John Smith',
            'phoneNumber' => '081239129321',
            'password' => null,
            'city' => 'Some City',
            'company' => 'The Company',
            'description' => 'About the user.',
            'picture' => 'some_picture.jpg',
        ]);

        $expiryDate = new DateTime();
        $expiryDate->modify('+1 month');

        $this->token = $this->user->createToken('Test Token', ['*'], $expiryDate)->plainTextToken;
    }

    private function testWithAuth($testFunction)
    {
        // Test using access token
        $testFunction($this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ]));
    }

    public function test_without_authentication(): void
    {
        $response = $this->get('/api/app/listings');

        $response->assertStatus(403);
    }

    public function test_can_list_listings(): void
    {
        $this->addListing("Jl. Rumah Baru", $this->fakeUserId, [
            'propertyType' => 'house',
            'listingType' => 'rent',
        ]);
        $this->addListing("Jl.Gedung Baru", $this->fakeUserId, [
            'propertyType' => 'warehouse',
            'listingType' => 'sale',
        ]);

        $this->testWithAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/listings');

            $response->assertStatus(200);

            // TODO: Test more fields other than address.
            $response->assertJson([
                "listings" => [
                    [
                        "address" => "Jl.Gedung Baru",
                        "propertyType" => "warehouse",
                        "listingType" => "sale",
                    ],
                    [
                        "address" => "Jl. Rumah Baru",
                        "propertyType" => "house",
                        "listingType" => "rent",
                    ],
                ],
            ]);
        });
    }

    public function test_can_list_listings_with_filter_q(): void
    {
        $this->addListing("Jl. Rumah Baru", $this->fakeUserId);
        $this->addListing("Jl. Gedung Baru", $this->fakeUserId);

        $this->testWithAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/listings?q=rumah');

            $response->assertStatus(200);

            $response->assertJson([
                "listings" => [
                    [
                        "address" => "Jl. Rumah Baru",
                    ],
                ],
            ]);
        });
    }

    public function test_can_list_listings_with_filter_price(): void
    {
        $this->addListing("Jl. Rumah Baru 1M", $this->fakeUserId, ['price' => 1000000000]);
        $this->addListing("Jl. Gedung Baru 2M", $this->fakeUserId, ['price' => 2000000000]);

        $this->testWithAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/listings?price[min]=1500000000');

            $response->assertStatus(200);

            $response->assertJson([
                "listings" => [
                    [
                        "address" => "Jl. Gedung Baru 2M",
                    ],
                ],
            ]);
        });
    }

    public function test_can_show_Listing(): void
    {
        $listing = $this->addListing("Jl. Rumah Baru", $this->fakeUserId);

        $this->testWithAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->get("/api/app/listings/{$listing->id}");

            $response->assertStatus(200);

            // TODO: Test more fields other than address.
            $response->assertJson([
                'id' => $listing->id,
                'address' => $listing->address,
                'user' => [
                    'name' => 'John Smith',
                    'city' => 'Some City',
                    'description' => 'About the user.',
                    'company' => 'The Company',
                    'profilePictureURL' => 'https://storage.googleapis.com/some-bucket/some_picture.jpg',
                ],
            ]);
        });
    }

    public function test_can_show_Listing_user_does_not_exist(): void
    {
        $listing = $this->addListing("Jl. Rumah Baru", $this->fakeUserId);

        $this->testWithAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->get("/api/app/listings/{$listing->id}");

            $response->assertStatus(200);

            // TODO: Test more fields other than address.
            $response->assertJson([
                'id' => $listing->id,
                'address' => $listing->address,
                'user' => [
                    'name' => 'John Smith',
                    'city' => 'Some City',
                    'description' => 'About the user.',
                    'company' => 'The Company',
                    'profilePictureURL' => 'https://storage.googleapis.com/some-bucket/some_picture.jpg',
                ],
            ]);
        });
    }

    public function test_can_update_Listing_with_no_revision(): void
    {
        $listing = $this->addListing("Jl. itu", $this->fakeUserId);

        $this->testWithAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->post("/api/app/listings/{$listing->id}", [
                'address' => 'Jl. itu',
                'description' => 'Dijual rumah bagus',
                'price' => '1000000000',
                'rentPrice' => '40000000',
                'lotSize' => '230',
                'buildingSize' => '200',
                'city' => 'Jakarta',
                'cityId' => 1,
                'bedroomCount' => '3',
                'bathroomCount' => '2',
                'listingForSale' => true,
                'listingForRent' => true,
                'isPrivate' => false,
                'withRewardAgreement' => true,
                'isMultipleUnits' => true,
            ]);

            $response->assertStatus(200);

            /** @var Listing $updatedListing */
            $updatedListing = Listing::find($listing->id);
            // Assert that the listing properties have been updated
            $this->assertEquals('Jl. itu', $updatedListing->address);
            $this->assertEquals('Dijual rumah bagus', $updatedListing->description);
            $this->assertEquals('1000000000', $updatedListing->price);
            $this->assertEquals('230', $updatedListing->lotSize);
            $this->assertEquals('200', $updatedListing->buildingSize);
            $this->assertEquals('Jakarta', $updatedListing->city);
            $this->assertEquals('3', $updatedListing->bedroomCount);
            $this->assertEquals('2', $updatedListing->bathroomCount);
            $this->assertEquals(false, $updatedListing->isPrivate);
            $this->assertEquals(true, $updatedListing->withRewardAgreement);
            $this->assertEquals(true, $updatedListing->isMultipleUnits);

            // Assert that admin attention exist when a listing is updated
            $this->assertEquals($updatedListing->adminAttentions->isNotEmpty(), true);
            $adminAttention = $updatedListing->adminAttentions->first();

            // Delay 10ms to make sure we test the update at later time.
            usleep(10000);
            $response = $makesHttpRequests->post("/api/app/listings/{$listing->id}", [
                'address' => 'Jl. itu',
                'description' => 'Dijual rumah bagus',
                'price' => '1000000000',
                'rentPrice' => '40000000',
                'lotSize' => '230',
                'buildingSize' => '200',
                'city' => 'Jakarta',
                'cityId' => 1,
                'bedroomCount' => '3',
                'bathroomCount' => '2',
                'listingForSale' => true,
                'listingForRent' => true,
                'isPrivate' => false,
                'withRewardAgreement' => true,
                'isMultipleUnits' => true,
            ]);
            $response->assertStatus(200);

            /** @var Listing $updatedListing */
            $updatedListing = Listing::find($listing->id);
            // admin attention should not be updated with the new time,
            // it should still point to the first unattended attention.
            $this->assertEquals($adminAttention, $updatedListing->adminAttentions->first());
        });
    }

    public function test_can_update_Listing_with_revision(): void
    {
        $listing = $this->addListing("Jl. ini 1", $this->fakeUserId);

        $this->testWithAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->post("/api/app/listings/{$listing->id}", [
                'address' => 'Jl. ini 1',
                'description' => 'Dijual rumah bagus',
                'price' => '1000000000',
                'rentPrice' => '40000000',
                'lotSize' => '230',
                'buildingSize' => '200',
                'city' => 'Jakarta',
                'cityId' => 1,
                'bedroomCount' => '3',
                'bathroomCount' => '2',
                'listingForSale' => true,
                'listingForRent' => true,
                'isPrivate' => false,
                'withRewardAgreement' => true,
                'isMultipleUnits' => true,
                'revision' => 0,
            ]);

            $response->assertStatus(200);

            $updatedListing = Listing::find($listing->id);
            // Assert that the listing properties have been updated
            $this->assertEquals('Jl. ini 1', $updatedListing->address);
            $this->assertEquals('Dijual rumah bagus', $updatedListing->description);
            $this->assertEquals('1000000000', $updatedListing->price);
            $this->assertEquals('230', $updatedListing->lotSize);
            $this->assertEquals('200', $updatedListing->buildingSize);
            $this->assertEquals('Jakarta', $updatedListing->city);
            $this->assertEquals('3', $updatedListing->bedroomCount);
            $this->assertEquals('2', $updatedListing->bathroomCount);
            $this->assertEquals(false, $updatedListing->isPrivate);
            $this->assertEquals(true, $updatedListing->withRewardAgreement);
            $this->assertEquals(true, $updatedListing->isMultipleUnits);
        });
    }

    public function test_can_create_Listing(): void
    {
        $this->testWithAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->post("/api/app/listings", [
                'address' => 'Jl. itu',
                'description' => 'Dijual rumah bagus',
                'price' => '1000000000',
                'rentPrice' => '40000000',
                'lotSize' => '230',
                'buildingSize' => '200',
                'city' => 'Jakarta',
                'cityId' => 1,
                'bedroomCount' => '3',
                'bathroomCount' => '2',
                'listingForSale' => true,
                'listingForRent' => true,
                'propertyType' => 'house',
                'isPrivate' => false,
                'withRewardAgreement' => true,
                'isMultipleUnits' => true,
            ]);

            $response->assertStatus(201);

            $this->assertDatabaseHas('listings', [
                'address' => 'Jl. itu',
                'verifyStatus' => 'on_review',
            ]);

            $listingId = $response->json('id');

            // Assert that admin attention exist when a listing is created
            $this->assertDatabaseHas('admin_attentions', [
                'listingId' => $listingId,
            ]);
        });
    }

    public function test_create_listing_fail_params(): void
    {
        $this->testWithAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->post("/api/app/listings", [
                'address' => 'Jl. itu',
                'description' => 'Dijual rumah bagus',
                'price' => '1000000000',
                'rentPrice' => '40000000',
                'lotSize' => '230',
                'buildingSize' => '200',
                'city' => 'Jakarta',
                'bedroomCount' => '3',
                'bathroomCount' => '2',
                'listingForSale' => true,
                'listingForRent' => true,
                'isPrivate' => false,
            ]);

            $response->assertStatus(422);
        });
    }

    // TODO: Find another home for this test.
    public function test_set_profile_with_access_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/app/users/profile', [
            'name' => 'John No',
            'city' => 'Jakarta',
            'cityId' => 123,
            'picture' => 'some_picture.jpg',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'John No',
            'city' => 'Jakarta',
            'cityId' => 123,
            'description' => 'About the user.',
            'picture' => 'https://storage.googleapis.com/some-bucket/some_picture.jpg',
            'phoneNumber' => '081239129321',
            'isPublicProfile' => false,
        ]);

        $this->assertEquals('John No', $response->json('name'));
        $this->assertEquals('Jakarta', $response->json('city'));

        $this->assertDatabaseHas('users', [
            'name' => 'John No',
            'phoneNumber' => '081239129321',
            'city' => 'Jakarta',
            'picture' => 'some_picture.jpg',
            'isPublicProfile' => false,
        ]);
    }
}
