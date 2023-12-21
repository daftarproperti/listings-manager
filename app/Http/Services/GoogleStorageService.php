<?php

namespace App\Http\Services;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;

class GoogleStorageService
{
    protected $bucket;

    public function __construct()
    {
        $storageClient = new StorageClient([
            'projectId' => config('services.google.project_id'),
            'keyFilePath' => storage_path('gpc-auth.json'),
        ]);

        $this->bucket = $storageClient->bucket(config('services.google.bukcet_name'));
    }

    public function uploadFile($fileContent, $fileName): ?StorageObject
    {
        $object = $this->bucket->upload($fileContent, [
            'name' => $fileName
        ]);

        return $object;
    }

    public function getFile($fileName): StorageObject
    {
        $file = $this->bucket->object($fileName);
        return $file;
    }
}
