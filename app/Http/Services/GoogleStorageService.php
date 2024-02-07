<?php

namespace App\Http\Services;

use App\Helpers\Assert;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;

class GoogleStorageService
{
    protected Bucket $bucket;

    public function __construct()
    {
        $storageClient = new StorageClient([
            'projectId' => config('services.google.project_id'),
            'keyFilePath' => storage_path('gpc-auth.json'),
        ]);

        $this->bucket = $storageClient->bucket(Assert::string(config('services.google.bucket_name')));
    }

    public function uploadFile(string $fileContent, string $fileName): ?StorageObject
    {
        $object = $this->bucket->upload($fileContent, [
            'name' => $fileName
        ]);

        return $object;
    }

    public function getFile(string $fileName): StorageObject
    {
        $file = $this->bucket->object($fileName);
        return $file;
    }
}
