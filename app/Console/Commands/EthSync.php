<?php

namespace App\Console\Commands;

use App\Helpers\Queue;
use App\Jobs\Web3Listing;
use App\Models\Enums\VerifyStatus;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
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
        $abi = type(file_get_contents(storage_path('blockchain/ListingsV0.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $web3 = new Web3(type(env('ETH_NODE'))->asString());
        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);

        /** @var Collection<int, Listing> $listings */
        $listings = Listing::where('verifyStatus', VerifyStatus::APPROVED)->get();
        foreach ($listings as $listing) {
            $listingId = $listing->listingId;
            $this->line("Processing Listing ID $listingId");

            $exists = $this->hasListing($contract, $listingId);
            if ($exists) {
                $this->line("Already in blockchain, skipping");
                continue;
            }

            Web3Listing::dispatch(
                $listing,
                'ADD',
            )->onQueue(Queue::getQueueName('generic'));
        }
    }
}
