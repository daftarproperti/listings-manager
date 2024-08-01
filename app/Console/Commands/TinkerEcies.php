<?php

namespace App\Console\Commands;

use App\Helpers\Ecies;
use Illuminate\Console\Command;

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

        $this->line(Ecies::encrypt($recipientPubHex, $message));
    }
}
