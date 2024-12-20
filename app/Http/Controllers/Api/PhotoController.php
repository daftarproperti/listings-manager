<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Services\GoogleStorageService;
use OpenApi\Attributes as OA;

class PhotoController extends Controller
{
    #[OA\Post(
        path: '/api/app/upload/image',
        tags: ['Image'],
        summary: 'Upload Image',
        operationId: 'image.upload',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    ref: '#/components/schemas/ImageUploadRequest',
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'fileId',
                            type: 'integer',
                            example: 123,
                        ),
                        new OA\Property(
                            property: 'fileName',
                            type: 'string',
                            example: 'image.jpg',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function uploadImage(
        ImageUploadRequest $request,
        GoogleStorageService $googleStorageService,
    ): \Illuminate\Http\JsonResponse {
        $validated = $request->validated();

        /** @var \Illuminate\Http\UploadedFile $image */
        $image = $validated['image'];

        $fileName = sprintf('%s.%s', md5($image->getClientOriginalName()), $image->getClientOriginalExtension());
        $fileId = time();

        $googleStorageService->uploadFile(
            type(file_get_contents($image->getRealPath()))->asString(),
            sprintf('%s_%s', $fileId, $fileName),
        );

        return response()->json([
            'fileId' => $fileId,
            'fileName' => $fileName,
        ]);
    }
}
