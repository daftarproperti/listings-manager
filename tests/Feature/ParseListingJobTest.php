<?php

namespace Tests\Feature;

use App\Http\Services\ChatGptService;
use App\Jobs\ParseListingJob;
use App\Models\Property;
use App\Models\Listing;
use App\Models\ListingUser;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class ParseListingJobTest extends TestCase
{
    private ListingUser $fakeUser;
    private int $fakeChatId = 1;

    public function setUp(): void
    {
        parent::setUp();

        $this->fakeUser = ListingUser::from([
            'name' => 'John Smith',
            'userName' => 'johnsmith',
            'userId' => 123,
            'source' => 'telegram',
        ]);

        Listing::truncate();
        Property::truncate();

        Config::set('services.telegram.bot_token', 'FakeToken');

        // Fake API responses from telegram.
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200)
        ]);
    }

    public function test_with_picture_urls(): void
    {
        $job = new ParseListingJob('The source text.', ['http://picture1.jpg', 'http://picture2.jpg'], $this->fakeUser);
        /** @var ChatGptService $chatGptService*/
        $chatGptService = $this->mock(ChatGptService::class, function (MockInterface $mock) {
            $mock->shouldReceive('seekAnswer')->withArgs(function ($msg) {
                return str_contains($msg, 'I need your help to transform this text into separate listing texts for each listing');
            })->andReturn('SINGLE_LISTING');
            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'I will need to extract the unstructured information into structured fields');
            })->andReturn(<<<'EOT'
[
  {
    "title": "Rumah Terawat di DARMO PERMAI",
    "propertyType": "House",
    "listingType": "Sale",
    "address": "",
    "price": "1250000000",
    "lotSize": "117",
    "buildingSize": "90",
    "carCount": "1",
    "bedroomCount": "2",
    "bathroomCount": "1",
    "floorCount": "1.5",
    "electricPower": "2200",
    "facing": "East",
    "ownership": "SHM",
    "city": "",
    "pictureUrls": "",
    "contact": {
      "name": "Brigitta Wong",
      "phoneNumber": "08113093772",
      "profilePictureURL": "",
      "sourceURL": "",
      "provider": ""
    },
    "coordinate": {
      "latitude": "",
      "longitude": ""
    }
  }
]
EOT);
        });

        $job->handle($chatGptService);

        $this->assertDatabaseCount('listings', 1);
        $this->assertDatabaseHas('listings', [
            'title' => 'Rumah Terawat di DARMO PERMAI',
            'propertyType' => 'house', // enum should be sanitized to lower case
            'listingType' => 'sale', // enum sanitized to lower case
            'description' => 'The source text.',
            'price' => 1250000000,
            'lotSize' => 117,
            'buildingSize' => 90,
            'bathroomCount' => 1,
            'bedroomCount' => 2,
            'carCount' => 1,
            'floorCount' => 1,
            'electricPower' => 2200,
            'ownership' => 'shm', // enum should be sanitized to lower case
            'pictureUrls' => ['picture1.jpg', 'picture2.jpg'],
        ]);

        $this->assertDatabaseCount('properties', 1);
        $this->assertDatabaseHas('properties', [
            'title' => 'Rumah Terawat di DARMO PERMAI',
            'propertyType' => 'house',
            'listingType' => 'sale',
            'description' => 'The source text.',
            'price' => 1250000000,
            'lotSize' => 117,
            'buildingSize' => 90,
            'bathroomCount' => 1,
            'bedroomCount' => 2,
            'carCount' => 1,
            'floorCount' => 1,
            'electricPower' => 2200,
            'ownership' => 'shm',
            'pictureUrls' => ['picture1.jpg', 'picture2.jpg'],
        ]);
    }

    // Multiple listings in a message
    public function test_multiple_listings(): void
    {
        $job = new ParseListingJob('The source text.', [], $this->fakeUser);
        /** @var ChatGptService $chatGptService*/
        $chatGptService = $this->mock(ChatGptService::class, function (MockInterface $mock) {
            $mock->shouldReceive('seekAnswer')->withArgs(function ($msg) {
                return str_contains($msg, 'I need your help to transform this text into separate listing texts for each listing');
            })->andReturn(<<<'EOT'
{
    "header": "The header",
    "footer": "The footer",
    "listings": [
        "Listing 1 content.",
        "Listing 2 content.",
        "Listing 3 content.",
        "Listing 4 content.",
        "Listing 5 content.",
        "Listing 6 content."
    ]
}
EOT);
            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'Listing 1 content');
            })->andReturn(<<<'EOT'
[
    {
        "title": "Dijual Rumah Hitung Tanah Mulyosari Utara",
        "propertyType": "HOUSE",
        "address": "Mulyosari Utara",
        "price": "1400000000",
        "lotSize": "11",
        "buildingSize": "1.5",
        "carCount": null,
        "bedroomCount": null,
        "bathroomCount": null,
        "floorCount": 1,
        "electricPower": 2200,
        "facing": "utara",
        "ownership": "SHM",
        "city": "Surabaya",
        "pictureUrls": "",
        "contact": {
            "name": "Peter Axell One",
            "phoneNumber": "6287853246108",
            "profilePictureURL": null,
            "sourceURL": null,
            "provider": null
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        }
    }
]
EOT);

            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'Listing 2 content');
            })->andReturn(<<<'EOT'
[
    {
        "title": "Rumah Siap Huni, Baru Greesss",
        "propertyType": "HOUSE",
        "address": "Regency One Babatan",
        "price": "1150000000",
        "lotSize": 50,
        "buildingSize": null,
        "carCount": null,
        "bedroomCount": 3,
        "bathroomCount": 2,
        "floorCount": 2,
        "electricPower": null,
        "facing": "utara",
        "ownership": null,
        "city": "Surabaya",
        "pictureUrls": "",
        "contact": {
            "name": "Daniel Axell One",
            "phoneNumber": "6281931594777",
            "profilePictureURL": null,
            "sourceURL": null,
            "provider": null
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        }
    }
]
EOT);

            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'Listing 3 content');
            })->andReturn(<<<'EOT'
[
    {
        "title": "HANYA 1 MENIT KE RAYA KENJERAN",
        "propertyType": "HOUSE",
        "address": "Lebak Jaya",
        "price": "1570000000",
        "lotSize": 73,
        "buildingSize": 103,
        "carCount": 2,
        "bedroomCount": 4,
        "bathroomCount": 3,
        "floorCount": null,
        "electricPower": 3500,
        "facing": "Timur",
        "ownership": "SHM",
        "city": "Surabaya",
        "pictureUrls": "",
        "contact": {
            "name": "Frans Axell",
            "phoneNumber": "08175112181",
            "profilePictureURL": null,
            "sourceURL": null,
            "provider": null
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        }
    }
]
EOT);

            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'Listing 4 content');
            })->andReturn(<<<'EOT'
[
    {
        "title": "MOJOARUM",
        "propertyType": "HOUSE",
        "address": "MOJOARUM",
        "price": "2600000000",
        "lotSize": 240,
        "buildingSize": 180,
        "carCount": null,
        "bedroomCount": 3,
        "bathroomCount": 2,
        "floorCount": 1.5,
        "electricPower": 3500,
        "facing": null,
        "ownership": "SHM",
        "city": "Surabaya",
        "pictureUrls": "",
        "contact": {
            "name": "Frans Axell",
            "phoneNumber": "08175112181",
            "profilePictureURL": null,
            "sourceURL": null,
            "provider": null
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        }
    }
]
EOT);

            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'Listing 5 content');
            })->andReturn(<<<'EOT'
[
    {
        "title": "RUMAH MODERN BARU GRESS",
        "propertyType": "HOUSE",
        "address": "MOJOKLANGGRU PUSAT KOTA",
        "price": "2750000000",
        "lotSize": 115,
        "buildingSize": 200,
        "carCount": 2,
        "bedroomCount": 3,
        "bathroomCount": 3,
        "floorCount": null,
        "electricPower": null,
        "facing": "Timur",
        "ownership": "SHM",
        "city": "Surabaya",
        "pictureUrls": "",
        "contact": {
            "name": "Frans Axell",
            "phoneNumber": "08175112181",
            "profilePictureURL": null,
            "sourceURL": null,
            "provider": null
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        }
    }
]
EOT);

            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'Listing 6 content');
            })->andReturn(<<<'EOT'
[
    {
        "title": "MURAH NEMEN INI",
        "propertyType": "HOUSE",
        "address": "Babatan Pantai",
        "price": "2700000000",
        "lotSize": 240,
        "buildingSize": 300,
        "carCount": null,
        "bedroomCount": 4,
        "bathroomCount": 3,
        "floorCount": null,
        "electricPower": null,
        "facing": "Utara",
        "ownership": "SHM",
        "city": "Surabaya",
        "pictureUrls": "",
        "contact": {
            "name": "Frans Axell",
            "phoneNumber": "08175112181",
            "profilePictureURL": null,
            "sourceURL": null,
            "provider": null
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        }
    }
]
EOT);
        });

        $job->handle($chatGptService);

        $this->assertDatabaseCount('listings', 6);

        $this->assertDatabaseHas('listings', [
            'title' => 'MURAH NEMEN INI',
            'propertyType' => 'house',
            'address' => 'Babatan Pantai',
            // The description should be from the split message
            'description' => "The header\n\nListing 6 content.\n\nThe footer",
            'bathroomCount' => 3,
            'bedroomCount' => 4,
            'facing' => 'north', // Convert to our canonical FacingDirection enum from the LLM-given 'Utara'
        ]);

        $this->assertDatabaseHas('listings', [
            'title' => 'RUMAH MODERN BARU GRESS',
            'propertyType' => 'house',
            'address' => 'MOJOKLANGGRU PUSAT KOTA',
            // The description should be from the split message
            'description' => "The header\n\nListing 5 content.\n\nThe footer",
            'bathroomCount' => 3,
            'bedroomCount' => 3,
            'facing' => 'east', // Convert to our canonical FacingDirection enum from the LLM-given 'Timur'
        ]);
    }

    // LLM gives a single object instead of array
    public function test_single_object_needs_wrap(): void
    {
        $job = new ParseListingJob('The source text.', [], $this->fakeUser, $this->fakeChatId);
        /** @var ChatGptService $chatGptService*/
        $chatGptService = $this->mock(ChatGptService::class, function (MockInterface $mock) {
            $mock->shouldReceive('seekAnswer')->withArgs(function ($msg) {
                return str_contains($msg, 'I need your help to transform this text into separate listing texts for each listing');
            })->andReturn('SINGLE_LISTING');
            $mock->shouldReceive('seekAnswerWithRetry')->withArgs(function ($msg) {
                return str_contains($msg, 'I will need to extract the unstructured information into structured fields');
            })->andReturn(<<<'EOT'
{
"title": "Rumah Terawat di DARMO PERMAI",
"address": "",
"price": "1250000000",
"lotSize": "117",
"buildingSize": "90",
"carCount": "1",
"bedroomCount": "2",
"bathroomCount": "1",
"floorCount": "1.5",
"electricPower": "2200",
"facing": "Timur",
"ownership": "SHM",
"city": "",
"pictureUrls": "",
"contact": {
  "name": "Brigitta Wong",
  "phoneNumber": "08113093772",
  "profilePictureURL": "",
  "sourceURL": "",
  "provider": ""
},
"coordinate": {
  "latitude": "",
  "longitude": ""
}
}
EOT);
        });

        $job->handle($chatGptService);

        $this->assertDatabaseCount('listings', 1);
        $this->assertDatabaseHas('listings', [
            'title' => 'Rumah Terawat di DARMO PERMAI',
            'description' => 'The source text.',
            'price' => 1250000000,
            'lotSize' => 117,
            'buildingSize' => 90,
            'bathroomCount' => 1,
            'bedroomCount' => 2,
            'carCount' => 1,
            'floorCount' => 1,
            'electricPower' => 2200,
        ]);

        $recordedRequests = Http::recorded(function (Request $request) {
            return str_starts_with($request->url(), 'https://api.telegram.org');
        });

        $this->assertCount(1, $recordedRequests);
        $request = $recordedRequests[0][0];
        $this->assertEquals('/botFakeToken/sendMessage', parse_url($request->url(), PHP_URL_PATH));
        parse_str(parse_url($request->url(), PHP_URL_QUERY), $params);
        $this->assertEquals($this->fakeChatId, $params['chat_id']);
        $this->assertEquals('html', $params['parse_mode']);
        $this->assertEquals(<<<EOD
Listing telah terproses dan masuk ke database, sehingga dapat ditemukan di jaringan Daftar Properti:
* <b>Rumah Terawat di DARMO PERMAI</b>

Klik tombol "Kelola Listing" di bawah untuk meng-edit atau menambahkan foto sehingga lebih menarik bagi pencari.
EOD, $params['text']);
    }
}
