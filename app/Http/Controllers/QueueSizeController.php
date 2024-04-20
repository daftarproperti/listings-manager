<?php

namespace App\Http\Controllers;

use App\Helpers\Assert;
use App\Helpers\Queue;
use App\Http\Controllers\Controller;
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
        $prefix = Assert::string($request->query('prefix') ?? 'generic');
        $queueName = Queue::getQueueName($prefix);
        $connection = $qm->connection(Assert::string(config('queue.default')));
        $queueSize = $connection->size($queueName);
        return response((string)$queueSize);
    }
}
