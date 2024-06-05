<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CityIdFiller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:city-id-fill {collection-name} {city-field=city}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill cityId in collection from its city name.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Retrieve command arguments
        $collectionName = $this->argument('collection-name');
        $cityField = $this->argument('city-field');

        // Check if the collection exists
        if (!Schema::hasTable($collectionName)) {
            $this->error("Collection $collectionName doesn't exist.");
            return;
        }

        // Build model class name
        $modelClassName = ucfirst(Str::studly(Str::singular($collectionName)));
        $modelClass = "App\\Models\\$modelClassName";

        // Check if the model class exists
        if (!class_exists($modelClass)) {
            $this->error("Model class $modelClassName doesn't exist.");
            return;
        }

        // Load city data
        $cityDataPath = storage_path('cities.json');
        if (!File::exists($cityDataPath)) {
            $this->error('City data file not found.');
            return;
        }


        /** @var array<string, \App\Models\City> $cities */
        $cities = json_decode(File::get($cityDataPath));
        $cityCollection = collect($cities);

        // Retrieve total count of records
        $totalCount = $modelClass::count();
        $this->info("Total records in $collectionName: $totalCount");

        // Initialize progress bar
        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        // Array to store "City not found" messages
        $notFoundMessages = [];

        // Process records in chunks
        $modelClass::query()->chunkById(500, function ($collection) use ($cityCollection, $cityField, $progressBar, &$notFoundMessages) {
            foreach ($collection as $item) {
                $cityName = $item->$cityField;

                $cities = $cityCollection->filter(function ($city) use ($cityName) {
                    return stripos($city->name, $cityName) !== false;
                });

                if ($cities->isNotEmpty()) {
                    $cityData = $cities->first();
                    $item->cityId = $cityData?->osmId;
                    $item->save();
                } else {
                    $notFoundMessages[$cityName] = "City not found: $cityName";
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->info("\nCityId filling completed.");

        // Display "City not found" messages
        foreach ($notFoundMessages as $message) {
            $this->output->writeln("<comment>$message</comment>");
        }
    }
}
