<?php

namespace Tests\Feature;

use Mockery;
use App\Helpers\Extractor;
use App\Jobs\GenerateListingFromText;
use App\Models\GeneratedListing;
use App\Models\Resources\ListingResource;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Tests\TestCase;

class GenerateListingFromTextTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        GeneratedListing::truncate();
    }

    public function testGenerateListing(): void
    {
        $text = 'JUAL BARU Modern Rumah Asri Taman 1 Menit ke Supermarket Oasis 5 Menit ke Rumah Sakit Sejahtera Dimensi: 6 x 22LT: 132m²LB: 180m²2 Lantai KT: 4+1 (bisa jadi 5+1) KM: 4Full MarmerListrik 2500 watt Row jalan 3 mobil leluasa SHM, bisa KPR Harga: 3,2M (nego) Hubungi: Developer Property 081234567890 (WhatsApp)';

        $extractedListing = (object) [
            'id' => 'temp-id',
            'listingId' => 1234,
            'title' => 'Rumah Asri Taman 1',
            'propertyType' => 'house',
            'listingType' => 'sale',
            'address' => '',
            'price' => 3200000000,
            'description' => $text,
            'sourceText' => $text,
            'coordinate' => (object) ['latitude' => 0, 'longitude' => 0],
            'contact' => (object) [
                'name' => 'Developer Property',
                'company' => null
            ],
            'updated_at' => now(),
            'created_at' => now(),
            'user_profile' => null,
            'adminNote' => null,
            'cancellationNote' => null,
            'closings' => null,
            'rentPrice' => null,
            'lotSize' => 132,
            'buildingSize' => 180,
            'carCount' => 3,
            'bedroomCount' => 4,
            'additionalBedroomCount' => 1,
            'bathroomCount' => 4,
            'additionalBathroomCount' => null,
            'floorCount' => 2,
            'electricPower' => 2500,
            'facing' => 'unknown',
            'ownership' => 'shm'
        ];

        $extractorMock = Mockery::mock(Extractor::class);
        $extractorMock->shouldReceive('extractSingleListingFromMessage')
            ->once()
            ->with($text)
            ->andReturn($extractedListing);

        $this->app->instance(Extractor::class, $extractorMock);

        Log::shouldReceive('info')
            ->once();

        $job = new GenerateListingFromText('job-id-123', $text);
        $job->handle(app(Extractor::class));

        $jobResult = GeneratedListing::where('job_id', 'job-id-123')->first();

        $this->assertDatabaseHas('generated_listings', [
            'job_id' => 'job-id-123',
            'generated_listing' => json_encode($jobResult->generated_listing),
        ]);
    }
}
