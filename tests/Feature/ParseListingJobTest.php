<?php

namespace Tests\Feature;

use App\Http\Services\ChatGptService;
use App\Jobs\ParseListingJob;
use App\Models\Listing;
use App\Models\ListingUser;
use Mockery\MockInterface;
use Tests\TestCase;

class ParseListingJobTest extends TestCase
{
    private ListingUser $fakeUser;

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
    }

    // LLM gives pictureUrls wrongly as string
    public function test_picture_urls_wrong_type(): void
    {
        $job = new ParseListingJob('some message', $this->fakeUser);
        /** @var ChatGptService $chatGptService*/
        $chatGptService = $this->mock(ChatGptService::class, function (MockInterface $mock) {
            $mock->shouldReceive('seekAnswer')->with('some message')->andReturn(<<<'EOT'
[
  {
    "title": "Rumah Terawat di DARMO PERMAI",
    "address": "",
    "description": "Rumah sangat bagus.",
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
]
EOT);
        });

        $job->handle($chatGptService);

        $this->assertDatabaseCount('listings', 1);
        $this->assertDatabaseHas('listings', [
            'title' => 'Rumah Terawat di DARMO PERMAI',
            'description' => 'Rumah sangat bagus.',
            'price' => 1250000000,
            'lotSize' => 117,
            'buildingSize' => 90,
            'bathroomCount' => 1,
            'bedroomCount' => 2,
            'carCount' => 1,
            'floorCount' => 1,
            'electricPower' => 2200,
        ]);
    }

    // Multiple listings in a message
    public function test_multiple_listings(): void
    {
        $job = new ParseListingJob('some message', $this->fakeUser);
        /** @var ChatGptService $chatGptService*/
        $chatGptService = $this->mock(ChatGptService::class, function (MockInterface $mock) {
            $mock->shouldReceive('seekAnswer')->with('some message')->andReturn(<<<'EOT'
[
    {
        "title": "Dijual Rumah Hitung Tanah Mulyosari Utara",
        "address": "Mulyosari Utara",
        "description": "LT 11×15\nLB 1.5 Lantai\nRow jln 2mbl",
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
    },
    {
        "title": "Rumah Siap Huni, Baru Greesss",
        "address": "Regency One Babatan",
        "description": "Rumah 2lt uk 5x10\nKt 3 km 2\nBaru gress",
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
    },
    {
        "title": "HANYA 1 MENIT KE RAYA KENJERAN",
        "address": "Lebak Jaya",
        "description": "LT 73 (6x12) / LB 103\nKT 4 / KM 3\nPLN 3500 W\nTANDON BAWAH + POMPA AIR\nCarport 2 mobil\nROW jalan 2,5 mobil",
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
    },
    {
        "title": "MOJOARUM",
        "address": "MOJOARUM",
        "description": "LT 240 (12×20)/LB 180 (1,5 Lantai)\nPondasi rumah ready utk 2 lantai\nKT 3 + 1/KM 2 + 1\nListrik 3500 Watt\n🔥Bonus Tandon Atas dan Bawah\n🔥Bonus Pemanas Air Solahart",
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
    },
    {
        "title": "RUMAH MODERN BARU GRESS",
        "address": "MOJOKLANGGRU PUSAT KOTA",
        "description": "LT 115 m² (5,25x22) / LB 200 m²\nKT 3+1/KM 3+1 (semua KM dlm)\nROW jalan 3 mobil\nCarport 2 mobil",
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
    },
    {
        "title": "MURAH NEMEN INI",
        "address": "Babatan Pantai",
        "description": "LT 240 (12x20) // LB ±300\nKT 4+1 / KM 3+1\nSemi Furnished Kitchen Set & Interior\nGarasi 1 mobil, carport 1 mobil",
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
            'address' => 'Babatan Pantai',
            'bathroomCount' => 3,
            'bedroomCount' => 4,
        ]);

        $this->assertDatabaseHas('listings', [
            'title' => 'RUMAH MODERN BARU GRESS',
            'address' => 'MOJOKLANGGRU PUSAT KOTA',
            'bathroomCount' => 3,
            'bedroomCount' => 3,
        ]);
    }
}
