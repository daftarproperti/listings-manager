<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class GetProperty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-property {property-id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utility to get property by id.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $propertyId = $this->argument('property-id');

        $property = Property::find($propertyId);
        if (!$property) {
            $this->info("No property found with id " . $propertyId);
            return;
        }

        $this->line("property = " . print_r($property, TRUE));
    }
}
