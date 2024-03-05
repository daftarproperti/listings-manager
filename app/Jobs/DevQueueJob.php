<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * A queue job for testing queue set up behavior.
 * Should not be used in production.
 */
class DevQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $name;

    /**
     * Create a new job instance.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("begin DevQueueJob $this->name");

        if ($this->name === 'error') {
            // Intentionally trigger error to see how job errors are handled.
            nonExistentFunction(); // @phpstan-ignore-line
        }

        Log::debug("end DevQueueJob $this->name");
    }
}
