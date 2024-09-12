<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Helpers\TelegramPhoto;
use App\Jobs\SyncListingToGCS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Web3\Contract;
use Web3\Web3;
use kornrunner\Ethereum\Transaction;

class Web3Listing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TelegramPhoto $telegramPhoto;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Listing $listing,
        public string $operationType)
    {
        //
    }

    private function hasListing(Contract $contract, int $id): bool
    {
        $exists = false;
        $contract->call('getListing', $id, function ($err, $ret) use (&$exists) {
            if ($err !== null) return;
            $exists = true;
        });
        return $exists;
    }

    private function executeListingContract(int $id, int $cityId, string $offChainLink, string $hash, string $operationType): void
    {
        $web3 = new Web3(type(env('ETH_NODE'))->asString());

        $abi = type(file_get_contents(storage_path('blockchain/Listings.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);
        $exists = $this->hasListing($contract, $id);

        $gasPrice = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->gasPrice(function ($err, $ret) use (&$gasPrice) {
            if ($err !== null) throw $err;
            $gasPrice = $ret->toHex();
        });

        $nonce = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->getTransactionCount(env('ETH_ACCOUNT'), function ($err, $ret) use (&$nonce) {
            if ($err !== null) throw $err;
            $nonce = $ret->toHex();
        });

        $operation = '';
        switch ($operationType) {
            case "ADD":
                $operation = 'addListing';
                if ($exists) {
                    logger()->info("Listing $id already exist. skipping addition");
                    return;
                }
                break;
            default:
                logger()->error("Unhandled operation type: $operationType");
        }

        /** @var string $contractData */
        $contractData = $contract->at($contractAddress)->getData($operation, $id, $cityId, $offChainLink, $hash); // @phpstan-ignore-line
        $tx = new Transaction(
            $nonce, // nonce
            $gasPrice, // gas price
            '0x50000', // gas limit
            $contractAddress, // to
            '0x0', // value
            $contractData,
        );

        $privateKey = type(env('ETH_PRIVATE_KEY'))->asString();
        $raw = $tx->getRaw($privateKey, (int)type(env('ETH_CHAIN_ID'))->asString());

        $txHash = null;
        /** @phpstan-ignore-next-line */
        $web3->getEth()->sendRawTransaction('0x' . $raw, function ($err, $hash) use (&$txHash) {
            if ($err !== null) throw $err;
            logger()->info("Sent Listing transaction, txHash = $hash");
            $txHash = $hash;
        });

        $receipt = null;
        $maxRetries = 100;
        do {
            /** @phpstan-ignore-next-line */
            $web3->getEth()->getTransactionReceipt($txHash, function ($err, $rec) use (&$receipt) {
                if ($err !== null) throw $err;
                $receipt = $rec;
            });
            $maxRetries--;
            sleep(1);
        } while ($receipt === null && $maxRetries > 0);

        if ($receipt && hexdec($receipt->status) == 1) {
            logger()->info("Transaction successful: $txHash");
        } else {
            logger()->error("Transaction failed: $txHash, receipt = " . print_r($receipt, true));
        }
    }

    protected function getHash(string $offChainLink): string|null {
        usleep(200000); // In case just uploaded.
        $content = file_get_contents($offChainLink);
        if (!$content) return null;
        return hash('sha256', $content);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!env('ETH_NODE')) {
            logger()->warning('No ETH Node configured, not sending Listing transaction.');
            return;
        }

        $listing = $this->listing;
        $updatedAt = $listing->updated_at->toIso8601ZuluString();
        $fileName = "listings/{$listing->listingId}/{$listing->listingId}-$updatedAt.json";

        $offChainLink = TelegramPhoto::getGcsUrlFromFileName($fileName);
        try {
            file_get_contents($offChainLink);
        } catch (\Exception) {
            // Upload to GCS first if not yet uploaded synchronously.
            (new SyncListingToGCS($listing->listingId))->handle();
        }

        $hash = $this->getHash($offChainLink);

        if (!$hash) {
            logger()->error("Cannot get hash from $offChainLink, aborting.");
            return;
        }

        // Lock for 120 seconds, migrate and seed should take no more than 1 minute.
        $lock = Cache::lock('execute-contract', 60);

        try {
            $lock->block(120);
            $this->executeListingContract($listing->listingId, $listing->cityId, $offChainLink, $hash, $this->operationType);
        } catch (LockTimeoutException) {
            logger()->error("Unable to acquire lock execute-contract");
        } finally {
            $lock->release();
        }
    }
}