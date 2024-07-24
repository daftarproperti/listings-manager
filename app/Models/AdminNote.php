<?php

namespace App\Models;

use MongoDB\BSON\UTCDateTime;
use Spatie\LaravelData\Data;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: "object",
    properties: [
        new OA\Property(property: "email", type: "string", example: "john@doe.web"),
        new OA\Property(property: "message", type: "string", example: "Hello World"),
        new OA\Property(property: "date", type: "string", format: "date-time"),
    ]
)]
class AdminNote extends Data
{
    public string $email;
    public string $message;
    public UTCDateTime $date;
}
