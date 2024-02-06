<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyUser;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    private string $fakeBotToken = 'fake-bot-token';
    private string $fakeUserId = '12345';

    /**
     * Generates fake init data and appends valid hash according to Telegram spec:
     * https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     */
    private function generate_telegram_init_data(): array
    {
        $initData = [
            'user' => json_encode([
                'id' => $this->fakeUserId,
                'first_name' => "John",
                'last_name' => "Smith",
                'username' => "johnsmith",
            ]),
            'foo' => 'bar',
        ];

        $secretKey = hash_hmac('sha256', $this->fakeBotToken, 'WebAppData', true);
        $dataCheckString = collect($initData)
            ->sort()
            ->map(fn ($value, $key) => "$key=$value")
            ->join("\n");

        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $initData['hash'] = $hash;
        return $initData;
    }

    private function addProperty($title, $userId): void
    {
        $propertyUser = new PropertyUser();
        $propertyUser->userId = $userId;
        $property = new Property();
        $property->title = $title;
        $property->user = $propertyUser;
        $property->save();
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
                    "title" => "Dijual Rumah",
                ],
                [
                    "title" => "Dijual Gedung",
                ],
            ],
        ]);
    }
}
