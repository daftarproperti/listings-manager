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

        $expectedListing = new Listing();
        $expectedListing->title = "Rumah Terawat di DARMO PERMAI";
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
}
