<?php

namespace App\Services;

use Google\Cloud\Storage\Bucket;
use Kreait\Firebase\Factory;
use Illuminate\Http\UploadedFile;

class FirebaseStorageService
{
    protected Bucket $bucket;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials'))
            ->withDefaultStorageBucket(config('firebase.projects.app.storage.default_bucket'));

        $this->bucket = $factory->createStorage()->getBucket();
    }

    /**
     * Upload a single file to Firebase Storage
     *
     * @param array $files The file to upload
     * @param string $folder The folder path in Firebase Storage
     * @return array Information about the uploaded file
     */
    public function uploadMultiple(array $files, string $folder): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            if (!($file instanceof UploadedFile)) {
                continue;
            }

            $fileName = $folder . '/' . uniqid() . '_' . $file->getClientOriginalName();

            $object = $this->bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $fileName]
            );

            $uploaded[] = [
                'filename' => $file->getClientOriginalName(),
                'path' => $fileName,
                'url' => $object->signedUrl(new \DateTime('+1 year'))
            ];
        }

        return $uploaded;
    }

    /**
     * Delete a file from Firebase Storage
     *
     * @param string $path The path of the file to delete
     * @return void
     */
    public function delete(string $path): void
    {
        $object = $this->bucket->object($path);
        if ($object->exists()) {
            $object->delete();
        }
    }
}

