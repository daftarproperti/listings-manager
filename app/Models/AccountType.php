<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'professional'
)]
/**
 * Account type
 */
enum AccountType: string
{
    case Individual = 'individual';
    case Professional = 'professional';
}
