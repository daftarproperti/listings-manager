<?php

namespace App\Console\Commands;

use App\Helpers\Assert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupTelegramBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-telegram-bot {base-url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up a telegram bot for use with Daftar Properti';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $webhookUrl = sprintf('%s%s', rtrim($this->argument('base-url'), '/'), route('telegram-webhook', [
            'secret_token' => config('services.telegram.webhook_access_secret'),
        ], false));

        $this->line('Setting webhook URL to ' . $webhookUrl);
        $response = Http::asForm()->post(
            sprintf('https://api.telegram.org/bot%s/setWebhook', Assert::string(config('services.telegram.bot_token'))),
            ['url' => $webhookUrl],
        );

        if ($response->status() != 200) {
            $this->error("Error setting webhook url");
            $this->line($response->body());
            return 1;
        }

        $this->info("Successfully set webhook URL");
        $this->line("response = " . $response->body());

        // TODO: Also set up the UI button.
        return 0;
    }
}
