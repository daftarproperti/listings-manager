<?php

namespace App\Console\Commands;

use App\Helpers\EthWrapper;
use Illuminate\Console\Command;
use Web3\Contract;
use Web3\Web3;
use kornrunner\Ethereum\Transaction;

class TinkerWeb3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tinker-web3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test web3 functionality';

    private function getListing(Contract $contract, int $id): void
    {
        $wrapper = new EthWrapper($contract);
        $ret = $wrapper->call('getListing', $id);
        $this->line('ret = ' . print_r($ret, true));
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $web3 = new Web3(type(env('ETH_NODE'))->asString());
        $ethWrapper = new EthWrapper($web3->getEth());

        $abi = type(file_get_contents(storage_path('blockchain/Listings.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);

        $this->getListing($contract, 19);

        $gasPrice = $ethWrapper->gasPrice();
        $nonce = $ethWrapper->getTransactionCount(env('ETH_ACCOUNT'))->toHex();

        /** @var string $contractData */
        $contractData = $contract->at($contractAddress)->getData( // @phpstan-ignore-line
            'addListing',
            19,
            'Salatiga',
            'http://xxx',
            'zzz'
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

        $txHash = $ethWrapper->sendRawTransaction('0x' . $raw);
        $this->info("txHash = $txHash");
    }
}
