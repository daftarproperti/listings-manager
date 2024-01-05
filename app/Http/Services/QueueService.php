<?php

namespace App\Http\Services;

use App\Models\UserProperty;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Auth\Credentials\ServiceAccountCredentials;

class QueueService
{
    public function queueGptProcess(string $message, UserProperty $user)
    {
        $queueName = config('services.google.queue_name');
        $projectId = config('services.google.project_id');
        $location = config('services.google.queue_location');

        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/cloud-tasks', storage_path('gpc-auth.json'));

        $client = new CloudTasksClient(['credentials' => $credentials]);
        $parent = $client->queueName($projectId, $location, $queueName);

        $task = new Task();
        $task->setHttpRequest(new HttpRequest([
            'http_method' => HttpMethod::POST,
            'url' => config('services.google.webhook_url'),
            'headers' => [
                'Content-Type' => 'application/json',
                'access-token' => config('services.google.webhook_access_secret'),
            ],
            'body' => json_encode(['message' => $message, 'user' => $user]),
        ]));

        $response = $client->createTask($parent, $task);

        return $response->getName();
    }

}
