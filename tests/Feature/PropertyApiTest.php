<?php

namespace Tests\Feature;

use App\Helpers\TelegramInitDataValidator;
use App\Models\Property;
use App\Models\PropertyUser;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PropertyApiTest extends TestCase
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

    private function addProperty(string $title, int $userId, array $fields = []): Property
    {
        return Property::factory()->create([
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
        Config::set('services.google.bucket_name', 'some-bucket');

        // Ensure each test case starts with empty database.
        Property::truncate();
        TelegramUser::truncate();
    }

    public function test_without_authentication(): void
    {
        $response = $this->get('/api/tele-app/properties');

        $response->assertStatus(403);
    }

    public function test_can_list_properties(): void
    {
        $this->addProperty("Dijual Rumah", $this->fakeUserId, [
            'propertyType' => 'house',
            'listingType' => 'rent',
        ]);
        $this->addProperty("Dijual Gedung", $this->fakeUserId, [
            'propertyType' => 'warehouse',
            'listingType' => 'sale',
        ]);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get('/api/tele-app/properties');

        $response->assertStatus(200);

        // TODO: Test more fields other than title.
        $response->assertJson([
            "properties" => [
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
    }

    public function test_can_show_property(): void
    {
        $property = $this->addProperty("Dijual Rumah", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get("/api/tele-app/properties/{$property->id}");

        $response->assertStatus(200);

        // TODO: Test more fields other than title.
        $response->assertJson([
            'id' => $property->id,
            'title' => $property->title,
        ]);
    }

    public function test_get_profile(): void
    {
        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get('/api/tele-app/users/profile');

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'city',
            'description',
            'company',
            'picture',
            'phoneNumber',
            'isPublicProfile'
        ]);
    }

    public function test_set_profile(): void
    {
        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->post('/api/tele-app/users/profile', [
            'name' => 'John No',
            'city' => 'Jakarta',
            'picture' => 'some_picture.jpg',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'id' => $this->fakeUserId,
            'name' => 'John No',
            'city' => 'Jakarta',
            'description' => null,
            'company' => null,
            'picture' => 'https://storage.googleapis.com/some-bucket/some_picture.jpg',
            'phoneNumber' => '',
            'isPublicProfile' => '',
        ]);

        $this->assertEquals('John No', $response->json('name'));
        $this->assertEquals('Jakarta', $response->json('city'));

        $this->assertDatabaseHas('telegram_users', [
            'username' => 'johnsmith',
            'profile' => [
                'name' => 'John No',
                'phoneNumber' => null,
                'city' => 'Jakarta',
                'description' => null,
                'company' => null,
                'picture' => 'some_picture.jpg',
                'isPublicProfile' => false,
            ],
        ]);
    }
}
