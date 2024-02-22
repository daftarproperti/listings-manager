<?php

namespace App\Http\Services;

use App\Helpers\Assert;
use App\Models\PropertyUser;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;

class QueueService
{
    private CloudTasksClient $cloudTasksClient;

    public function __construct(CloudTasksClient $cloudTasksClient) {
        $this->cloudTasksClient = $cloudTasksClient;
    }

    public function queueGptProcess(string $message, PropertyUser $user, string $chatId = null): string
    {
        $queueName = Assert::string(config('services.google.queue_name'));
        $projectId = Assert::string(config('services.google.project_id'));
        $location = Assert::string(config('services.google.queue_location'));

        $parent = $this->cloudTasksClient->queueName($projectId, $location, $queueName);

        $task = new Task();
        $task->setHttpRequest(new HttpRequest([
            'http_method' => HttpMethod::POST,
            'url' => config('services.google.webhook_url'),
            'headers' => [
                'Content-Type' => 'application/json',
                'access-token' => config('services.google.webhook_access_secret'),
            ],
            'body' => json_encode(['message' => $message, 'user' => $user, 'chat_id' => $chatId]),
        ]));

        $response = $this->cloudTasksClient->createTask($parent, $task);

        return $response->getName();
    }

}
