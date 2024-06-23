<?php

namespace App\Console\Commands;

use App\Helpers\Queue;
use App\Helpers\TelegramPhoto;
use App\Jobs\SyncListingToGCS;
use App\Jobs\Web3AddListing;
use App\Models\Listing;
use Illuminate\Console\Command;
use Web3\Contract;
use Web3\Web3;

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

    private function hasListing(Contract $contract, int $id): bool
    {
        $exists = false;
        $contract->call('getListing', $id, function ($err, $ret) use (&$exists) {
            if ($err !== null) return;
            $exists = true;
        });
        return $exists;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $abi = type(file_get_contents(storage_path('blockchain/Listings.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $web3 = new Web3(type(env('ETH_NODE'))->asString());
        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);

        $listings = Listing::all();
        $count = 7;
        foreach ($listings as $listing) {
            if ($count <= 0) break;
            $count--;

            $listingId = $listing->listingId;
            $this->line("Processing Listing ID $listingId");

            $exists = $this->hasListing($contract, $listingId);
            if ($exists) {
                $this->line("Already in blockchain, skipping");
                continue;
            }

            $updatedAt = $listing->updated_at->toIso8601ZuluString();
            $fileName = "listings/{$listingId}/{$listingId}-$updatedAt.json";

            $offChainLink = TelegramPhoto::getGcsUrlFromFileName($fileName);
            try {
                file_get_contents($offChainLink);
            } catch (\Exception) {
                // Upload to GCS first if not yet uploaded.
                SyncListingToGCS::dispatch($listingId)->onQueue(Queue::getQueueName('generic'));
            }

            Web3AddListing::dispatch(
                $listingId,
                $listing->cityName ?? ($listing->city ?? 'No City'),
                TelegramPhoto::getGcsUrlFromFileName($fileName),
            )->onQueue(Queue::getQueueName('generic'));
        }
    }
}
