<?php
namespace App\Logging;

use Google\Cloud\Logging\LoggingClient;
use Monolog\Logger;
use Monolog\Handler\PsrHandler;

class CreateGcpLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array<string>  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $loggingClient = new LoggingClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
        ]);

        $logger = $loggingClient->psrLogger('app', [
            'batchEnabled' => true,
        ]);

        return new Logger('gcp', [new PsrHandler($logger)]);
    }
}
