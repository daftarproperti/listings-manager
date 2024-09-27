<?php

namespace App\Console\Commands;

use App\Helpers\Queue;
use App\Jobs\Web3Listing;
use App\Models\Enums\VerifyStatus;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class EthSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:eth-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data to Blockchain';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Collection<int, Listing> $listings */
        $listings = Listing::where('verifyStatus', VerifyStatus::APPROVED)->get();
        foreach ($listings as $listing) {
            $listingId = $listing->listingId;
            $this->line("Processing Listing ID $listingId");

            Web3Listing::dispatch(
                $listing,
                'ADD',
            )->onQueue(Queue::getQueueName('generic'));
        }
    }
}
