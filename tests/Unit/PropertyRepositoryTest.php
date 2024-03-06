<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Repositories\PropertyRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Tests\TestCase;


class PropertyRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Property::truncate();
    }

    public function test_property_list_without_filter(): void
    {
        $repository = new PropertyRepository();
        $properties = $repository->list();
        $this->assertInstanceOf(Paginator::class, $properties);
    }

    public function test_property_list_filter_price(): void
    {
        Property::factory(5)->create([
            'price' => 1000000,
        ]);

        Property::factory(2)->create([
            'price' => 500000,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'price' => ['min' => 500000, 'max' => 1000000]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(7, $properties->items());
    }

    public function test_property_list_filter_bedroom_count(): void
    {
        Property::factory(5)->create([
            'bedroomCount' => 2,
        ]);

        Property::factory(2)->create([
            'bedroomCount' => 3,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'bedroomCount' => 2
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(5, $properties->items());
    }

    public function test_property_list_filter_bedroom_count_min(): void
    {
        Property::factory(5)->create([
            'bedroomCount' => 2,
        ]);

        Property::factory(2)->create([
            'bedroomCount' => 3,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'bedroomCount' => ['min' => 3]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(2, $properties->items());
    }

    public function test_property_list_filter_bedroom_count_max(): void
    {
        Property::factory(5)->create([
            'bedroomCount' => 2,
        ]);

        Property::factory(2)->create([
            'bedroomCount' => 3,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'bedroomCount' => ['max' => 3]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(7, $properties->items());
    }

    public function test_property_list_filter_bathroom_count(): void
    {
        Property::factory(5)->create([
            'bathroomCount' => 2,
        ]);

        Property::factory(2)->create([
            'bathroomCount' => 3,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'bathroomCount' => 2
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(5, $properties->items());
    }

    public function test_property_list_filter_bathroom_count_min(): void
    {
        Property::factory(5)->create([
            'bathroomCount' => 2,
        ]);

        Property::factory(2)->create([
            'bathroomCount' => 3,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'bathroomCount' => ['min' => 3]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(2, $properties->items());
    }

    public function test_property_list_filter_bathroom_count_max(): void
    {
        Property::factory(5)->create([
            'bathroomCount' => 2,
        ]);

        Property::factory(2)->create([
            'bathroomCount' => 3,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'bathroomCount' => ['max' => 2]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(5, $properties->items());
    }

    public function test_property_list_filter_lot_size(): void
    {
        Property::factory(5)->create([
            'lotSize' => 200,
        ]);

        Property::factory(2)->create([
            'lotSize' => 150,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'lotSize' => ['min' => 170, 'max' => 200]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(5, $properties->items());
    }

    public function test_property_list_filter_building_size(): void
    {
        Property::factory(5)->create([
            'buildingSize' => 100,
        ]);

        Property::factory(2)->create([
            'buildingSize' => 36,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'buildingSize' => ['min' => 10, 'max' => 90]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(2, $properties->items());
    }

    public function test_property_list_filter_ownership(): void
    {
        Property::factory(5)->create([
            'ownership' => 'SHM',
        ]);

        Property::factory(2)->create([
            'ownership' => 'HGB',
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'ownership' => 'SHM'
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(5, $properties->items());
    }

    public function test_property_list_filter_car_count(): void
    {
        Property::factory(5)->create([
            'carCount' => 2,
        ]);

        Property::factory(2)->create([
            'carCount' => 1,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'carCount' => 1
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(2, $properties->items());
    }

    public function test_property_list_filter_car_count_min(): void
    {
        Property::factory(5)->create([
            'carCount' => 2,
        ]);

        Property::factory(2)->create([
            'carCount' => 1,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'carCount' => ['min' => 1]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(7, $properties->items());
    }

    public function test_property_list_filter_car_count_max(): void
    {
        Property::factory(5)->create([
            'carCount' => 2,
        ]);

        Property::factory(2)->create([
            'carCount' => 1,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'carCount' => ['max' => 1]
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(2, $properties->items());
    }

    public function test_property_list_filter_electric_power(): void
    {
        Property::factory(5)->create([
            'electricPower' => 5500,
        ]);

        Property::factory(2)->create([
            'electricPower' => 2200,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'electricPower' => 5500
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(5, $properties->items());
    }

    public function test_property_list_sort_desc(): void
    {
        Property::factory(2)->create([
            'buildingSize' => 150,
        ]);

        Property::factory(2)->create([
            'buildingSize' => 90,
        ]);

        Property::factory(2)->create([
            'buildingSize' => 36,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'sort' => 'buildingSize',
            'order' => 'desc'
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertEquals(150, $properties->items()[0]->buildingSize);
    }

    public function test_property_list_sort_asc(): void
    {
        Property::factory(2)->create([
            'buildingSize' => 150,
        ]);

        Property::factory(2)->create([
            'buildingSize' => 90,
        ]);

        Property::factory(2)->create([
            'buildingSize' => 36,
        ]);

        $repository = new PropertyRepository();
        $filters = [
            'sort' => 'buildingSize',
            'order' => 'asc'
        ];

        $properties = $repository->list($filters);
        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertEquals(36, $properties->items()[0]->buildingSize);
    }

    public function test_property_get_by_keyword(): void
    {
        Property::factory()->create([
            'title' => 'Rumah Apik',
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        Property::factory()->create([
            'title' => 'Rumah Bagus',
            'description' => 'Rumah yang sangat apik dan bagus'
        ]);

        $filters = [
            'q' => 'apik'
        ];

        $repository = new PropertyRepository();
        $properties = $repository->list($filters);

        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(2, $properties->items());
    }

    public function test_property_get_by_keyword_not_found(): void
    {
        Property::factory()->create([
            'title' => 'Rumah Apik',
            'description' => 'Rumah Luas dan sangat bagus'
        ]);

        Property::factory()->create([
            'title' => 'Rumah Bagus',
            'description' => 'Rumah yang sangat apik dan bagus'
        ]);

        $filters = [
            'q' => 'dekat'
        ];

        $repository = new PropertyRepository();
        $properties = $repository->list($filters);

        $this->assertInstanceOf(Paginator::class, $properties);
        $this->assertCount(0, $properties->items());
    }
}