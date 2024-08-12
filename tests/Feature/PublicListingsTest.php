<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use App\Helpers\Ecies;
use DateTime;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PublicListingsTest extends TestCase
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

        putenv('USER_ID_KEY=some-user-id-key');
        putenv('ETH_PRIVATE_KEY=0000000000000000000000000000000000000000000000000000000000000001');

        // Ensure each test case starts with empty database.
        Listing::truncate();
        User::truncate();

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
    }

    public function test_can_show_Listing_json(): void
    {
        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId, [
            'propertyType' => 'house',
            'listingType' => 'rent',
        ]);

        $response = $this->get("/public/listings/{$listing->listingId}", [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'listingId' => $listing->listingId,
            'title' => 'Dijual Rumah',
        ], true);

        $this->assertEquals('John Smith', $response->json('registrant.name'));
        $this->assertEquals(
            '8a835e56c51b7137ee852b6bca1ec300e9567a10851c3eda38b0311ac830d261',
            $response->json('registrant.phoneNumberHash'));
        $phoneEncrypted = $response->json('registrant.phoneNumberEncrypted');
        $privateKey = Ecies::privateKeyFromHex(getenv('ETH_PRIVATE_KEY'));
        $this->assertEquals('081239129321', Ecies::decryptToString($privateKey, $phoneEncrypted));
    }
}
