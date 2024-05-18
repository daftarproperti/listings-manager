<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupTelegramBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-telegram-bot {base-url} {ui-url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up a telegram bot for use with Daftar Properti';

    /**
     * @param array<mixed> $params
     */
    private function callTelegramApi(string $apiMethod, array $params): int
    {
        $this->line("calling $apiMethod with params = " . print_r($params, TRUE));
        $response = Http::asForm()->post(
            sprintf(
                'https://api.telegram.org/bot%s/%s',
                type(config('services.telegram.bot_token'))->asString(),
                $apiMethod
            ),
            $params,
        );

        if ($response->status() != 200) {
            $this->error("Error calling $apiMethod");
            $this->line($response->body());
            return 1;
        }

        $this->info("Successfully called $apiMethod");
        $this->line("response = " . $response->body());
        $this->line("");
        return 0;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // API reference: https://core.telegram.org/bots/api

        $webhookUrl = sprintf('%s%s', rtrim($this->argument('base-url'), '/'), route('telegram-webhook', [
            'secret_token' => config('services.telegram.webhook_access_secret'),
        ], false));
        if ($errorCode = $this->callTelegramApi(
            'setWebhook',
            ['url' => $webhookUrl],
        )) {
            return $errorCode;
        }

        if ($errorCode = $this->callTelegramApi(
            'setChatMenuButton',
            [
                'menu_button' => json_encode([
                    'type' => 'web_app',
                    'text' => 'Kelola Listing',
                    'web_app' => [
                        'url' => $this->argument('ui-url'),
                    ],
                ])
            ],
        )) {
            return $errorCode;
        }

        if ($errorCode = $this->callTelegramApi(
            'setMyDescription',
            [
                'description' => 'Daftar Properti membantu anda mengelola dan share listing anda.',
            ],
        )) {
            return $errorCode;
        }

        return 0;
    }
}
