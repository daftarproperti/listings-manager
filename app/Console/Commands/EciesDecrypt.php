<?php

namespace App\Console\Commands;

use App\Helpers\Ecies;
use Illuminate\Console\Command;

class EciesDecrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ecies-decrypt {privateKeyHex} {encryptedBlock}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decryption utility';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $privateKeyHex = $this->argument('privateKeyHex');
        $encryptedBlock = $this->argument('encryptedBlock');

        $decrypted = Ecies::decryptToString(Ecies::privateKeyFromHex($privateKeyHex), $encryptedBlock);
        if ($decrypted === false) {
            $this->error('failed decrypting');
            return;
        }

        if (!mb_check_encoding($decrypted, 'ASCII')) {
            $this->error('decrypted block is not ASCII');
            return;
        }

        $this->info("Decrypted: $decrypted");
    }
}
