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

    private function addProperty(string $title, int $userId): Property
    {
        $propertyUser = new PropertyUser();
        $propertyUser->userId = $userId;
        $property = new Property();
        $property->title = $title;
        $property->user = $propertyUser;
        $property->save();
        return $property;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.telegram.bot_token', $this->fakeBotToken);

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
        $this->addProperty("Dijual Rumah", $this->fakeUserId);
        $this->addProperty("Dijual Gedung", $this->fakeUserId);

        $response = $this->withHeaders([
            'x-init-data' => http_build_query($this->generate_telegram_init_data()),
        ])->get('/api/tele-app/properties');

        $response->assertStatus(200);

        // TODO: Test more fields other than title.
        $response->assertJson([
            "properties" => [
                [
                    "title" => "Dijual Gedung",
                ],
                [
                    "title" => "Dijual Rumah",
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
        ]);

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

        $this->assertEquals('John No', $response->json('name'));
        $this->assertEquals('Jakarta', $response->json('city'));
    }
}
