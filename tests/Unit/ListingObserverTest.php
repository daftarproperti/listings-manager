<?php

namespace Tests\Unit;

use App\Models\Listing;
use App\Models\Property;
use Tests\TestCase;


class ListingObserverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Listing::truncate();
    }

    public function test_empty_listing_creation(): void
    {
        $listing = new Listing();
        $listing->title = '';
        $listing->price = '';
        $listing->description = '';

        $listing->save();

        $this->assertNull($listing->id);
    }

    public function test_filled_listing_creation(): void
    {
        $listing = Listing::factory()->create([
            'title' => 'Rumah Apik',
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $this->assertNotNull($listing->id);
    }

    public function test_listing_creation_should_create_property(): void
    {
        $listing = Listing::factory()->create([
            'title' => 'Rumah Apik',
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $property = Property::where('listings', $listing->id)->first();

        $this->assertNotNull($property->id);
    }

    public function test_listing_update_should_update_property(): void
    {
        $listing = Listing::factory()->create([
            'title' => 'Rumah Apik',
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        $listing->title = 'Rumah Bagus';
        $listing->save();

        $property = Property::where('listings', $listing->id)->first();

        $this->assertEquals($property->title, 'Rumah Bagus');
    }
}
