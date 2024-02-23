<?php

namespace App\Console\Commands;

use App\Helpers\Assert;
use App\Helpers\TelegramInitDataValidator;
use Illuminate\Console\Command;

class GenInitData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gen-init-data {user-id} {first-name} {last-name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Telegram init data for testing and experimentation.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $initData = TelegramInitDataValidator::generateInitData(
            Assert::string(config('services.telegram.bot_token')),
            time(),
            [
                'id' => (int)$this->argument('user-id'),
                'first_name' => $this->argument('first-name'),
                'last_name' => $this->argument('last-name'),
            ]
        );
        $this->info("Init data with checksum:");
        $this->line(print_r($initData, TRUE));
        $this->info("As a query string:");
        $this->line(http_build_query($initData));
    }
}
