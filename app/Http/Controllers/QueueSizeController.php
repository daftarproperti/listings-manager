<?php

namespace App\Http\Controllers;

use App\Helpers\Queue;
use Illuminate\Contracts\Queue\Factory as QueueManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Shows the current queue size.
 */
class QueueSizeController extends Controller
{
    public function index(Request $request, QueueManager $qm): Response
    {
        $prefix = type($request->query('prefix') ?? 'generic')->asString();
        $queueName = Queue::getQueueName($prefix);
        $connection = $qm->connection(type(config('queue.default'))->asString());
        $queueSize = $connection->size($queueName);
        return response((string)$queueSize);
    }
}
