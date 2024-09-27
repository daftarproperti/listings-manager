<?php

namespace Tests\Feature;

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

    private function addListing(string $title, int $userId, array $fields = []): Listing
    {
        return Listing::factory()->create([
            'user' => [
                'userId' => $userId,
                'source' => 'app',
            ],
            'title' => $title,
        ] + $fields);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.google.bucket_name', 'some-bucket');

        // Ensure each test case starts with empty database.
        Listing::truncate();
        User::truncate();

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

    private function testWithBothAuth($testFunction)
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
        $this->addListing("Dijual Rumah", $this->fakeUserId, [
            'propertyType' => 'house',
            'listingType' => 'rent',
        ]);
        $this->addListing("Dijual Gedung", $this->fakeUserId, [
            'propertyType' => 'warehouse',
            'listingType' => 'sale',
        ]);

        $this->testWithBothAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/listings');

            $response->assertStatus(200);

            // TODO: Test more fields other than title.
            $response->assertJson([
                "listings" => [
                    [
                        "title" => "Dijual Gedung",
                        "propertyType" => "warehouse",
                        "listingType" => "sale",
                    ],
                    [
                        "title" => "Dijual Rumah",
                        "propertyType" => "house",
                        "listingType" => "rent",
                    ],
                ],
            ]);
        });
    }

    public function test_can_list_listings_with_filter_q(): void
    {
        $this->addListing("Dijual Rumah", $this->fakeUserId);
        $this->addListing("Dijual Gedung", $this->fakeUserId);

        $this->testWithBothAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/listings?q=rumah');

            $response->assertStatus(200);

            $response->assertJson([
                "listings" => [
                    [
                        "title" => "Dijual Rumah",
                    ],
                ],
            ]);
        });
    }

    public function test_can_list_listings_with_filter_price(): void
    {
        $this->addListing("Dijual Rumah 1M", $this->fakeUserId, ['price' => 1000000000]);
        $this->addListing("Dijual Gedung 2M", $this->fakeUserId, ['price' => 2000000000]);

        $this->testWithBothAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/listings?price[min]=1500000000');

            $response->assertStatus(200);

            $response->assertJson([
                "listings" => [
                    [
                        "title" => "Dijual Gedung 2M",
                    ],
                ],
            ]);
        });
    }

    public function test_can_show_Listing(): void
    {
        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId);

        $this->testWithBothAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->get("/api/app/listings/{$listing->id}");

            $response->assertStatus(200);

            // TODO: Test more fields other than title.
            $response->assertJson([
                'id' => $listing->id,
                'title' => $listing->title,
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
        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId);

        $this->testWithBothAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->get("/api/app/listings/{$listing->id}");

            $response->assertStatus(200);

            // TODO: Test more fields other than title.
            $response->assertJson([
                'id' => $listing->id,
                'title' => $listing->title,
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

    public function test_can_update_Listing(): void
    {
        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId);

        $this->testWithBothAuth(function (self $makesHttpRequests) use ($listing) {
            $response = $makesHttpRequests->post("/api/app/listings/{$listing->id}", [
                'title' => 'Lagi Dijual',
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

            $updatedListing = Listing::find($listing->id);
            // Assert that the listing properties have been updated
            $this->assertEquals('Lagi Dijual', $updatedListing->title);
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
        });
    }

    public function test_can_create_Listing(): void
    {
        $this->testWithBothAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->post("/api/app/listings", [
                'title' => 'Lagi Dijual',
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
                'title' => 'Lagi Dijual',
                'address' => 'Jl. itu',
                'verifyStatus' => 'on_review',
            ]);
        });
    }

    public function test_create_listing_fail_params(): void
    {
        $this->testWithBothAuth(function (self $makesHttpRequests) {
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
}
