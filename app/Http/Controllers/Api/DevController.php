<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Queue;
use App\Http\Controllers\Controller;
use App\Jobs\DevQueueJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Useful for testing in development environment.
 * Should not be used in production.
 */
class DevController extends Controller
{
    // Tests how queue jobs are handled.
    public function queue(Request $request): JsonResponse
    {
        $start = hrtime(TRUE);
        DevQueueJob::dispatch(type($request->input('name', 'Default'))->asString())
            ->onQueue(Queue::getQueueName('generic'));
        $end = hrtime(TRUE);
        $durationMs = ($end - $start) / 1000000;

        // Return the duration taken to dispatch the job.
        return response()->json(['success' => true, 'duration_ms' => $durationMs]);
    }
}
