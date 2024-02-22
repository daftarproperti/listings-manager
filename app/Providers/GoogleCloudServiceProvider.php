<?php

namespace App\Providers;

use Google\Cloud\Tasks\V2\CloudTasksClient;
use Illuminate\Support\ServiceProvider;

class GoogleCloudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CloudTasksClient::class, function ($app) {
            $keyFilePath = storage_path('gpc-auth.json');
            if (file_exists($keyFilePath)) {
                // Initialize with credentials if key file exists
                $client = new CloudTasksClient([
                    'credentials' => $keyFilePath,
                ]);
            } else {
                // Initialize without credentials if key file does not exist.
                // This will retrieve the application default credentials (ADC).
                $client = new CloudTasksClient();
            }
            return $client;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
