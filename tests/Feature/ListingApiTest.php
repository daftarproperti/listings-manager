<?php

namespace Tests\Feature;

use App\Helpers\TelegramInitDataValidator;
use App\Models\Listing;
use App\Models\ListingUser;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ListingApiTest extends TestCase
{
    private string $fakeBotToken = 'fake-bot-token';
    private int $fakeUserId = 12345;

    /**
     * Generates fake init data and appends valid hash according to Telegram spec:
     * https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     *
     * @return array<string, string>
     */
    private function generate_telegram_init_data(): array
    {
        return TelegramInitDataValidator::generateInitData(
            $this->fakeBotToken,
            0,
            [
                'id' => $this->fakeUserId,
                'first_name' => "John",
                'last_name' => "Smith",
                'username' => "johnsmith",
            ]
        );
    }

    private function addListing(string $title, int $userId): Listing
    {
        $listingUser = new ListingUser();
        $listingUser->userId = $userId;
        $listing = new Listing();
        $listing->title = $title;
        $listing->user = $listingUser;
        $listing->save();
        return $listing;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.telegram.bot_token', $this->fakeBotToken);

        // Ensure each test case starts with empty database.
        Listing::truncate();
        TelegramUser::truncate();
    }

    public function test_without_authentication(): void
    {
        $response = $this->get('/api/tele-app/listings');

        $response->assertStatus(403);
    }

    public function test_can_list_listings(): void
    {
        $this->addListing("Dijual Rumah", $this->fakeUserId);
        $this->addListing("Dijual Gedung", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get('/api/tele-app/listings');

        $response->assertStatus(200);

        // TODO: Test more fields other than title.
        $response->assertJson([
            "listings" => [
                [
                    "title" => "Dijual Rumah",
                ],
                [
                    "title" => "Dijual Gedung",
                ],
            ],
        ]);
    }

    public function test_can_show_Listing(): void
    {
        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get("/api/tele-app/listings/{$listing->id}");

        $response->assertStatus(200);

        // TODO: Test more fields other than title.
        $response->assertJson([
            'id' => $listing->id,
            'title' => $listing->title,
        ]);
    }

    public function test_can_update_Listing(): void
    {
        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->post("/api/tele-app/listings/{$listing->id}", [
            'title' => 'Lagi Dijual',
            'address' => 'Jl. itu',
            'description' => 'Dijual rumah bagus',
            'price' => '1000000000',
            'lotSize' => '230',
            'buildingSize' => '200',
            'city' => 'Jakarta',
            'bedroomCount' => '3',
            'bathroomCount' => '2',
            'isPrivate' => false,
        ]);

        $response->assertStatus(200);
    }

    public function test_can_create_Listing(): void
    {
        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->post("/api/tele-app/listings", [
            'title' => 'Lagi Dijual',
            'address' => 'Jl. itu',
            'description' => 'Dijual rumah bagus',
            'price' => '1000000000',
            'lotSize' => '230',
            'buildingSize' => '200',
            'city' => 'Jakarta',
            'bedroomCount' => '3',
            'bathroomCount' => '2',
            'isPrivate' => false,
        ]);

        $response->assertStatus(201);
    }

    public function test_create_listing_fail_params(): void
    {
        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->post("/api/tele-app/listings", [
            'address' => 'Jl. itu',
            'description' => 'Dijual rumah bagus',
            'price' => '1000000000',
            'lotSize' => '230',
            'buildingSize' => '200',
            'city' => 'Jakarta',
            'bedroomCount' => '3',
            'bathroomCount' => '2',
            'isPrivate' => false,
        ]);

        $response->assertStatus(422);
    }

}
