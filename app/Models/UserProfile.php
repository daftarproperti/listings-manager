<?php

namespace App\Models;

use OpenApi\Attributes as OA;
use Spatie\LaravelData\Data;

#[OA\Schema(
    type: 'object',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'John Doe', nullable: true),
        new OA\Property(property: 'phoneNumber', type: 'string', example: '+6281234567890', nullable: true),
        new OA\Property(property: 'city', type: 'string', example: 'Jakarta', nullable: true),
        new OA\Property(property: 'cityId', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'cityName', type: 'string', example: 'Jakarta', nullable: true),
        new OA\Property(property: 'description', type: 'string', example: 'Lorem ipsum', nullable: true),
        new OA\Property(property: 'company', type: 'string', example: 'Company Name', nullable: true),
        new OA\Property(property: 'picture', type: 'string', example: 'https://example.com/image.jpg', nullable: true),
        new OA\Property(property: 'isPublicProfile', type: 'boolean', example: true, nullable: true),
    ]
)]
class UserProfile extends Data
{
    public ?string $name = null;
    public ?string $phoneNumber = null;
    public ?string $city = null;
    public ?int $cityId = null;
    public ?string $cityName = null;
    public ?string $description = null;
    public ?string $company = null;
    public ?string $picture = null;
    public ?bool $isPublicProfile = null;
}
