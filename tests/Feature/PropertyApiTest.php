<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PropertyApiTest extends TestCase
{
    private string $fakeBotToken = 'fake-bot-token';
    private int $fakeUserId = 12345;

    private User $user;
    private string $token;

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

        Config::set('services.google.bucket_name', 'some-bucket');

        // Ensure each test case starts with empty database.
        Property::truncate();
        User::truncate();

        $this->user = User::factory()->create([
            'name' => 'John Smith',
            'phoneNumber' => '081239129321',
            'password' => null
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
        $response = $this->get('/api/app/properties');

        $response->assertStatus(403);
    }

    public function test_can_list_properties(): void
    {
        $this->addProperty("Dijual Rumah", $this->fakeUserId, [
            'propertyType' => 'house',
            'listingType' => 'rent',
        ]);
        // Workaround to prevent timestamp being too close and not sorted correctly.
        // TODO: Find a better solution to prevent flakiness.
        sleep(1);
        $this->addProperty("Dijual Gedung", $this->fakeUserId, [
            'propertyType' => 'warehouse',
            'listingType' => 'sale',
        ]);

        $this->testWithBothAuth(function (self $makesHttpRequests) {
            $response = $makesHttpRequests->get('/api/app/properties');

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
        });
    }

    public function test_can_show_property(): void
    {
        $property = $this->addProperty("Dijual Rumah", $this->fakeUserId);

        $this->testWithBothAuth(function (self $makesHttpRequests) use ($property) {
            $response = $makesHttpRequests->get("/api/app/properties/{$property->id}");

            $response->assertStatus(200);

            // TODO: Test more fields other than title.
            $response->assertJson([
                'id' => $property->id,
                'title' => $property->title,
            ]);
        });
    }

    public function test_get_profile_with_access_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/app/users/profile');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'city',
            'cityId',
            'description',
            'company',
            'picture',
            'phoneNumber',
            'isPublicProfile'
        ]);
    }

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
            'description' => null,
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
