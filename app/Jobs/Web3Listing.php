<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\Resources\PublicListingResource;
use App\Helpers\EthWrapper;
use App\Helpers\Photo;
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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Listing $listing,
        public string $operationType,
    ) {
        //
    }

    private function hasListingV0(Contract $contract, int $id): bool
    {
        $wrapper = new EthWrapper($contract);

        try {
            $wrapper->call('getListing', $id);
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    /**
     * @return array<int, mixed> The key value pair depends on the ABI version.
     */
    private function getExistingListingV1(Contract $contract, Listing $listing): array|null
    {
        $wrapper = new EthWrapper($contract);

        try {
            $ret = $wrapper->call('getListing', $listing->listingId);
            if (!is_array($ret)) {
                return null;
            }
            return $ret;
        } catch (\Exception) {
            return null;
        }
    }

    private function executeListingContractV0(
        Listing $listing,
        string $offChainLink,
        string $hash,
        string $operationType,
    ): void {
        $id = $listing->listingId;
        $cityId = $listing->cityId;
        $web3 = new Web3(type(env('ETH_NODE'))->asString(), /*timeout=*/ 5);

        $abi = type(file_get_contents(storage_path('blockchain/ListingsV0.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);
        $exists = $this->hasListingV0($contract, $id);

        $gasPrice = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->gasPrice(function ($err, $ret) use (&$gasPrice) {
            if ($err !== null) {
                throw $err;
            }
            $gasPrice = $ret->toHex();
        });

        $nonce = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->getTransactionCount(env('ETH_ACCOUNT'), function ($err, $ret) use (&$nonce) {
            if ($err !== null) {
                throw $err;
            }
            $nonce = $ret->toHex();
        });

        $operation = '';
        switch ($operationType) {
            case 'ADD':
                $operation = 'addListing';
                if ($exists) {
                    logger()->info("Listing $id already exist. skipping addition");
                    return;
                }
                break;
            default:
                logger()->error("Unhandled operation type: $operationType");
                return;
        }

        /** @var string $contractData */
        $contractData = $contract->at($contractAddress)->getData( // @phpstan-ignore-line
            $operation,
            $id,
            $cityId,
            $offChainLink,
            $hash,
        );
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
            if ($err !== null) {
                throw $err;
            }
            logger()->info("Sent Listing transaction, txHash = $hash");
            $txHash = $hash;
        });

        $receipt = null;
        $maxRetries = 100;
        do {
            /** @phpstan-ignore-next-line */
            $web3->getEth()->getTransactionReceipt($txHash, function ($err, $rec) use (&$receipt) {
                if ($err !== null) {
                    throw $err;
                }
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

    private function executeListingContractV1(
        Listing $listing,
        string $offChainLink,
        string $hash,
        string $operationType,
    ): void {
        $id = $listing->listingId;
        $cityId = $listing->cityId;
        $web3 = new Web3(type(env('ETH_NODE'))->asString(), /*timeout=*/ 5);

        $abi = type(file_get_contents(storage_path('blockchain/ListingsV1.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);
        $existingV1 = $this->getExistingListingV1($contract, $listing);

        $gasPrice = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->gasPrice(function ($err, $ret) use (&$gasPrice) {
            if ($err !== null) {
                throw $err;
            }
            $gasPrice = $ret->toHex();
        });

        $nonce = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->getTransactionCount(env('ETH_ACCOUNT'), function ($err, $ret) use (&$nonce) {
            if ($err !== null) {
                throw $err;
            }
            $nonce = $ret->toHex();
        });

        $operation = '';
        switch ($operationType) {
            case 'ADD':
                $operation = 'addListing';
                if ($existingV1) {
                    $existingOffChainLink = isset($existingV1[2]) ? $existingV1[2] : null;
                    // Skip only if the listing already exists in blockchain with the same exact offChainLink, otherwise
                    // consider that we need to update to make sure listing is sync to blockchain.
                    if ($existingOffChainLink == $offChainLink) {
                        logger()->info("Listing $id already exist. skipping addition");
                        return;
                    }
                    logger()->info("Listing $id already exist but different schema version, forcing update.");
                    $operation = 'updateListing';
                }
                break;
            case 'UPDATE':
                $operation = 'updateListing';
                if (!$existingV1) {
                    logger()->info("Listing $id does not exist. adding it as new listing");
                    $operation = 'addListing';
                }
                break;
            case 'DELETE':
                $operation = 'deleteListing';
                if (!$existingV1) {
                    logger()->info("Listing $id does not exist. skipping deletion");
                    return;
                }
                break;
            default:
                logger()->error("Unhandled operation type: $operationType");
                return;
        }

        /** @var string $contractData */
        $contractData = $contract->at($contractAddress)->getData( // @phpstan-ignore-line
            $operation,
            $id,
            $cityId,
            $offChainLink,
            $hash,
        );
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
            if ($err !== null) {
                throw $err;
            }
            logger()->info("Sent Listing transaction, txHash = $hash");
            $txHash = $hash;
        });

        $receipt = null;
        $maxRetries = 100;
        do {
            /** @phpstan-ignore-next-line */
            $web3->getEth()->getTransactionReceipt($txHash, function ($err, $rec) use (&$receipt) {
                if ($err !== null) {
                    throw $err;
                }
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

    protected function getHash(string $offChainLink): string|null
    {
        usleep(200000); // In case just uploaded.
        $content = file_get_contents($offChainLink);
        if (!$content) {
            return null;
        }
        return hash('sha256', $content);
    }

    public static function getOffChainFileName(Listing $listing): string
    {
        $updatedAt = $listing->updated_at->toIso8601ZuluString();
        $versionSuffix = '-v' . PublicListingResource::VERSION;
        return "listings/{$listing->listingId}/{$listing->listingId}-$updatedAt$versionSuffix.json";
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
        $fileName = self::getOffChainFileName($listing);

        $offChainLink = Photo::getGcsUrlFromFileName($fileName);
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

        $contractVersion = env('ETH_LISTINGS_CONTRACT_VERSION', '0');
        $contractVersionMap = [
            '0' => [$this, 'executeListingContractV0'],
            '1' => [$this, 'executeListingContractV1'],
        ];
        if (!isset($contractVersionMap[$contractVersion])) {
            logger()->error('Unhandled contract version');
            return;
        }

        // Lock for 120 seconds, migrate and seed should take no more than 1 minute.
        $lock = Cache::lock('execute-contract', 60);

        try {
            $lock->block(120);
            $contractVersionMap[$contractVersion](
                $listing,
                $offChainLink,
                $hash,
                $this->operationType,
            );
        } catch (LockTimeoutException) {
            logger()->error('Unable to acquire lock execute-contract');
        } finally {
            $lock->release();
        }
    }
}
