<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AccountType",
    type: "string",
    description: "Account type",
    enum: ["individual", "professional"],
    example: "professional"
)]
enum AccountType: string
{
    case Individual = 'individual';
    case Professional = 'professional';
}
