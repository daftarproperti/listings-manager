<?php

namespace App\Console\Commands;

use App\Helpers\Ecies;
use Illuminate\Console\Command;
use Mdanter\Ecc\EccFactory;

class TinkerEcies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tinker-ecies {recipientPubHex} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ECIES encrypt/decrypt utility';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $recipientPubHex = $this->argument('recipientPubHex');
        $message = $this->argument('message');

        $generator = EccFactory::getSecgCurves()->generator256k1();

        $recipientPublicKey = $generator->getPublicKeyFrom(
            gmp_init(substr($recipientPubHex, 0, 64), 16),
            gmp_init(substr($recipientPubHex, 64, 64), 16),
        );

        $encrypted = Ecies::encryptString($recipientPublicKey, $message);
        if ($encrypted === false) {
            $this->error('error encrypting');
            return;
        }

        $this->info("encrypted: $encrypted");
    }
}
