<?php

namespace App\Console\Commands;

use App\Models\FacingDirection;
use App\Models\PropertyOwnership;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Data;

class StrapiListing extends Data
{
    public ?int $id = null;
    public ?string $title = null;
    public ?string $address = null;
    public ?string $description = null;
    public ?float $price = null;
    public ?float $lot_size = null;
    public ?float $building_size = null;
    public ?string $facing = null;
    public ?int $floor_count = null;
    public ?int $bedroom_count = null;
    public ?int $bathroom_count = null;
    public ?int $car_count = null;
    public ?string $ownership = null;
    public ?string $condition = null;
    public ?string $seller_name = null;
    public ?string $seller_phone = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?string $place_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $published_at = null;
    public ?string $created_by_id = null;
    public ?string $updated_by_id = null;
    public ?string $picture_url = null;
    public ?int $osm_id = null;
}

class ImportListingsFromStrapi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-listings-from-strapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utility to import listings from strapi database';

    private function createListingFromStrapi(StrapiListing $strapiListing): void
    {
        /** @var Listing|null $listing */
        $listing = Listing::where('strapiId', $strapiListing->id)->first();
        if (is_null($listing)) $listing = new Listing();

        $listing->strapiId = $strapiListing->id;
        $listing->title = $strapiListing->title ?? '';
        $listing->address = $strapiListing->address ?? '';
        $listing->description = $strapiListing->description ?? '';
        $listing->price = $strapiListing->price ? (int)($strapiListing->price * 1000000) : 0;
        $listing->lotSize = (int)$strapiListing->lot_size;
        $listing->buildingSize = (int)$strapiListing->building_size;
        $listing->facing = FacingDirection::tryFrom($strapiListing->facing ?? '') ?? FacingDirection::Unknown;
        $listing->floorCount = (int)$strapiListing->floor_count;
        $listing->bedroomCount = (int)$strapiListing->bedroom_count;
        $listing->bathroomCount = (int)$strapiListing->bathroom_count;
        $listing->carCount = (int)$strapiListing->car_count;
        $listing->ownership = PropertyOwnership::tryFrom($strapiListing->ownership ?? '') ?? PropertyOwnership::Unknown;
        if ($strapiListing->picture_url) $listing->pictureUrls = [$strapiListing->picture_url];
        $listing->cityId = $strapiListing->osm_id ?? 0;

        // TODO: Import these fields
        // $listing->condition = $strapiListing->condition;
        // $listing->seller_name = $strapiListing->seller_name;
        // $listing->seller_phone = $strapiListing->seller_phone;
        // $listing->latitude = $strapiListing->latitude;
        // $listing->longitude = $strapiListing->longitude;
        // $listing->place_id = $strapiListing->place_id;

        $listing->save();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->line("Hello");

        $connection = [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'strapi',
            'username' => 'strapi',
            'password' => env('PG_PASSWORD'),
            'charset' => 'utf8',
        ];

        config(['database.connections.strapi_pgsql' => $connection]);

        $sql = <<<EOD
SELECT DISTINCT ON (p.id) p.*, f.url as picture_url, c.osm_id as osm_id
FROM properties p
JOIN files_related_morphs frm ON p.id = frm.related_id
JOIN files f ON frm.file_id = f.id
JOIN properties_city_links pcl ON p.id = pcl.property_id
JOIN cities c ON pcl.city_id = c.id
WHERE frm.related_type = 'api::property.property' AND frm.field = 'pictures' AND p.address IS NOT NULL
ORDER BY p.id
EOD;
        $listings = DB::connection('strapi_pgsql')->select($sql);
        foreach ($listings as $listingObj) {
            $strapiListing = StrapiListing::from($listingObj);
            $this->line("Importing listing = " . $strapiListing->title);
            $this->createListingFromStrapi($strapiListing);
        }
    }
}
