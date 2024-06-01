<?php

namespace App\Console\Commands;

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
        $contract->call('getListing', $id, function ($err, $ret) {
            if ($err !== null) {
                $this->line("err = " . $err);
                return;
            }
            $this->line("ret = " . print_r($ret, true));
        });
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $web3 = new Web3(type(env('ETH_NODE'))->asString());

        $abi = type(file_get_contents(storage_path('blockchain/Listings.abi.json')))->asString();
        $contractAddress = type(env('ETH_LISTINGS_CONTRACT_ADDRESS'))->asString();

        $contract = (new Contract($web3->getProvider(), $abi))->at($contractAddress);

        $this->getListing($contract, 1);

        $gasPrice = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->gasPrice(function ($err, $ret) use (&$gasPrice) {
            if ($err !== null) {
                $this->line("err = " . $err);
                return;
            }
            $this->line('gas price = 0x' . $ret->toHex());
            $gasPrice = $ret->toHex();
        });

        $nonce = '0x0';
        /** @phpstan-ignore-next-line */
        $web3->getEth()->getTransactionCount(env('ETH_ACCOUNT'), function ($err, $ret) use (&$nonce) {
            if ($err !== null) {
                $this->line("err = " . $err);
                return;
            }
            $this->line('nonce = 0x' . $ret->toHex());
            $nonce = $ret->toHex();
        });

        /** @var string $contractData */
        $contractData = $contract->at($contractAddress)->getData('addListing', 19, 'Salatiga', 'http://xxx', 'zzz'); // @phpstan-ignore-line
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

        /** @phpstan-ignore-next-line */
        $web3->getEth()->sendRawTransaction('0x' . $raw, function ($err, $txHash) {
            if ($err !== null) {
                $this->line("err = " . $err);
                return;
            }
            $this->info("txHash = $txHash");
        });
    }
}
