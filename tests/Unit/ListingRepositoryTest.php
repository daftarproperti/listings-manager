<?php

namespace Tests\Unit;

use App\Models\Listing;
use App\Repositories\ListingRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Tests\TestCase;


class ListingRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Listing::truncate();
    }

    public function test_listing_list_without_filter(): void
    {
        $repository = new ListingRepository();
        $listings = $repository->list();
        $this->assertInstanceOf(Paginator::class, $listings);
    }

    public function test_listing_list_filter_collection(): void
    {
        $totalItemsByUser = 10;
        Listing::factory($totalItemsByUser)->create([
            'user' => [
                'userId' => 1
            ]
        ]);

        Listing::factory()->create([
            'user' => [
                'userId' => 5
            ]
        ]);

        $repository = new ListingRepository();
        $filters = [
            'collection' => true,
            'userId' => 1
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount($totalItemsByUser, $listings->items());
    }

    public function test_listing_list_filter_price(): void
    {
        Listing::factory(5)->create([
            'price' => 1000000,
        ]);

        Listing::factory(2)->create([
            'price' => 500000,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'price' => ['min' => 500000, 'max' => 1000000]
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(7, $listings->items());
    }

    public function test_listing_list_filter_bedroom_count(): void
    {
        Listing::factory(5)->create([
            'bedroomCount' => 2,
        ]);

        Listing::factory(2)->create([
            'bedroomCount' => 3,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'bedroomCount' => 2
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(5, $listings->items());
    }

    public function test_listing_list_filter_bathroom_count(): void
    {
        Listing::factory(5)->create([
            'bathroomCount' => 2,
        ]);

        Listing::factory(2)->create([
            'bathroomCount' => 3,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'bathroomCount' => 2
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(5, $listings->items());
    }

    public function test_listing_list_filter_lot_size(): void
    {
        Listing::factory(5)->create([
            'lotSize' => 200,
        ]);

        Listing::factory(2)->create([
            'lotSize' => 150,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'lotSize' => ['min' => 170, 'max' => 200]
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(5, $listings->items());
    }

    public function test_listing_list_filter_building_size(): void
    {
        Listing::factory(5)->create([
            'buildingSize' => 100,
        ]);

        Listing::factory(2)->create([
            'buildingSize' => 36,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'buildingSize' => ['min' => 10, 'max' => 90]
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(2, $listings->items());
    }

    public function test_listing_list_filter_ownership(): void
    {
        Listing::factory(5)->create([
            'ownership' => 'SHM',
        ]);

        Listing::factory(2)->create([
            'ownership' => 'HGB',
        ]);

        $repository = new ListingRepository();
        $filters = [
            'ownership' => 'SHM'
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(5, $listings->items());
    }

    public function test_listing_list_filter_car_count(): void
    {
        Listing::factory(5)->create([
            'carCount' => 2,
        ]);

        Listing::factory(2)->create([
            'carCount' => 1,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'carCount' => 1
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(2, $listings->items());
    }

    public function test_listing_list_filter_electric_power(): void
    {
        Listing::factory(5)->create([
            'electricPower' => 5500,
        ]);

        Listing::factory(2)->create([
            'electricPower' => 2200,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'electricPower' => 5500
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertCount(5, $listings->items());
    }

    public function test_listing_list_sort_desc(): void
    {
        Listing::factory(2)->create([
            'buildingSize' => 150,
        ]);

        Listing::factory(2)->create([
            'buildingSize' => 90,
        ]);

        Listing::factory(2)->create([
            'buildingSize' => 36,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'sort' => 'buildingSize',
            'order' => 'desc'
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertEquals(150, $listings->items()[0]->buildingSize);
    }

    public function test_listing_list_sort_asc(): void
    {
        Listing::factory(2)->create([
            'buildingSize' => 150,
        ]);

        Listing::factory(2)->create([
            'buildingSize' => 90,
        ]);

        Listing::factory(2)->create([
            'buildingSize' => 36,
        ]);

        $repository = new ListingRepository();
        $filters = [
            'sort' => 'buildingSize',
            'order' => 'asc'
        ];

        $listings = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $listings);
        $this->assertEquals(36, $listings->items()[0]->buildingSize);
    }
}
