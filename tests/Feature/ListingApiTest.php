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

    private function addListing(string $title, int $userId, array $fields = []): Listing
    {
        return Listing::factory()->create([
            'user' => [
                'userId' => $userId,
                'source' => 'telegram',
            ],
            'title' => $title,
        ] + $fields);
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
                    "title" => "Dijual Gedung",
                ],
                [
                    "title" => "Dijual Rumah",
                ],
            ],
        ]);
    }

    public function test_can_list_listings_with_filter_q(): void
    {
        $this->addListing("Dijual Rumah", $this->fakeUserId);
        $this->addListing("Dijual Gedung", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get('/api/tele-app/listings?q=rumah');

        $response->assertStatus(200);

        $response->assertJson([
            "listings" => [
                [
                    "title" => "Dijual Rumah",
                ],
            ],
        ]);
    }

    public function test_can_list_listings_with_filter_price(): void
    {
        $this->addListing("Dijual Rumah 1M", $this->fakeUserId, ['price' => 1000000000]);
        $this->addListing("Dijual Gedung 2M", $this->fakeUserId, ['price' => 2000000000]);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get('/api/tele-app/listings?price[min]=1500000000');

        $response->assertStatus(200);

        $response->assertJson([
            "listings" => [
                [
                    "title" => "Dijual Gedung 2M",
                ],
            ],
        ]);
    }

    public function test_can_show_Listing(): void
    {
        TelegramUser::factory()->create([
            'user_id' => $this->fakeUserId,
            'profile' => [
                'name' => 'The User',
                'phoneNumber' => '0123',
                'city' => 'Some City',
                'description' => 'About the user.',
                'company' => 'The Company',
            ],
        ]);

        $listing = $this->addListing("Dijual Rumah", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get("/api/tele-app/listings/{$listing->id}");

        $response->assertStatus(200);

        // TODO: Test more fields other than title.
        $response->assertJson([
            'id' => $listing->id,
            'title' => $listing->title,
            'user' => [
                'name' => 'The User',
                'phoneNumber' => '0123',
                'city' => 'Some City',
                'description' => 'About the user.',
                'company' => 'The Company',
            ],
        ]);
    }

    public function test_can_show_Listing_user_does_not_exist(): void
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
            'user' => [
                'name' => null,
                'phoneNumber' => null,
                'city' => null,
                'description' => null,
                'company' => null,
            ],
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
