<?php

namespace Tests\Feature;

use App\Models\Enums\ClosingType;
use App\Models\Listing;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use MongoDB\BSON\UTCDateTime;

class ClosingListingTest extends TestCase
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

        Config::set('services.telegram.bot_token', $this->fakeBotToken);
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
        $listing = $this->addListing('Dijual Rumah', $this->fakeUserId);
        $response = $this->post(sprintf('/api/tele-app/listings/%s/closings', $listing->id));
        $response->assertStatus(403);
    }

    public function test_can_add_closings(): void
    {
        $listing = $this->addListing('Dijual Rumah', $this->fakeUserId, [
            'propertyType' => 'house',
            'listingType' => 'rent',
        ]);

        $testDate = Carbon::parse('2024-01-01 00:01:00');

        $closingData = [
            'closingType' => (ClosingType::SOLD)->value,
            'clientName' => 'John Smith',
            'clientPhoneNumber' => '081239129321',
            'transactionValue' => 1000000,
            'date' => $testDate->toIso8601ZuluString(),
        ];

        $this->testWithBothAuth(function (self $makesHttpRequests) use($listing, $closingData) {
            $response = $makesHttpRequests->post(
                sprintf('/api/tele-app/listings/%s/closings', $listing->id),
                $closingData
            );
            $response->assertStatus(200);

            $responseData = $response->json();

            $this->assertArrayHasKey('closings', $responseData);
            //phone number canonicalized when saving, so on return we need to compare canonicalized numbers
            $closingData['clientPhoneNumber'] = '+6281239129321';
            $response->assertJsonFragment($closingData);
        });

        $this->assertDatabaseHas('closings', [
            'closingType' => (ClosingType::SOLD)->value,
            'clientName' => 'John Smith',
            'clientPhoneNumber' => '+6281239129321',
            'transactionValue' => 1000000,
            'date' => new UTCDateTime($testDate->getTimestampMs()),
            'listing_id' => $listing->id,
        ]);
    }
}
