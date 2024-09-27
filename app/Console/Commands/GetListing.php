<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Listing;

class GetListing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-listing {listing-id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utility to get listing by id.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $listingId = $this->argument('listing-id');

        /** @var Listing|null $listing */
        $listing = Listing::find($listingId);
        if (!$listing) {
            $this->info("No listing found with id " . $listingId);
            return;
        }

        $this->line("listing = " . print_r($listing, true));
        $this->line("user = " . print_r($listing->user, true));
    }
}
