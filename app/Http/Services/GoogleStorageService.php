<?php

namespace App\Http\Services;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;

class GoogleStorageService
{
    protected Bucket $bucket;

    public function __construct()
    {
        /*
         * on local machine please login in terminal with `gcloud auth application-default login`
         * if get error "Anonymous caller does not have storage.objects.get access.." when accessing this service,
         * please set your GOOGLE_APPLICATION_CREDENTIALS with $HOME/.config/gcloud/application_default_credentials.json
         * in `.env` file replace $HOME with your home path
         */

        $storageClient = new StorageClient([
            'projectId' => config('services.google.project_id'),
        ]);

        $this->bucket = $storageClient->bucket(type(config('services.google.bucket_name'))->asString());
    }

    public function uploadFile(string $fileContent, string $fileName): ?StorageObject
    {
        $object = $this->bucket->upload($fileContent, [
            'name' => $fileName,
        ]);

        return $object;
    }

    public function getFile(string $fileName): StorageObject
    {
        $file = $this->bucket->object($fileName);
        return $file;
    }
}
